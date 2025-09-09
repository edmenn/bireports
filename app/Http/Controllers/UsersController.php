<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\MicrosoftGraphService;

class UsersController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $users = User::query()
            ->when($q !== '', function ($qr) use ($q) {
                $needle = mb_strtolower($q);
                $qr->where(function ($w) use ($needle) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(email) LIKE ?', ["%{$needle}%"]);
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users', 'q'));
    }

    public function create(): View
    {
        $roles = ['admin','editor','usuario'];
        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','max:255'],
            'role'     => ['required','in:admin,editor,usuario'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email'=> $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $usuario): View
    {
        $roles = ['admin','editor','usuario'];
        $user = $usuario; // alias para claridad en vistas
        return view('users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $user = $usuario;
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email,'.$user->id],
            'password' => ['nullable','string','min:8','max:255'],
            'role'     => ['required','in:admin,editor,usuario'],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        // No permitir eliminar la propia cuenta
        $selfId = (int) (Auth::id() ?? 0);
        if ($usuario->id === $selfId) {
            return redirect()->route('users.index')
                ->with('warning', 'No puedes eliminar tu propia cuenta.');
        }

        // Evitar eliminar al último administrador del sistema
        try {
            $isAdmin = method_exists($usuario, 'hasRole') && $usuario->hasRole('admin');
            if ($isAdmin) {
                $admins = User::role('admin')->count();
                if ($admins <= 1) {
                    return redirect()->route('users.index')
                        ->with('warning', 'No se puede eliminar este usuario: es el único administrador.');
                }
            }
        } catch (\Throwable $e) {
            // Si algo falla al chequear roles, preferimos no dejar al sistema sin admin
            return redirect()->route('users.index')
                ->with('error', 'No se pudo validar los roles. Operación cancelada para proteger el último administrador.');
        }

        $usuario->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }

    public function importForm(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $next = $request->query('next');
        $items = [];
        $nextLink = null;
        $error = null;
        try {
            $svc = new MicrosoftGraphService();
            $res = $svc->listUsers($q !== '' ? $q : null, $next ? (string) $next : null);
            $items = $res['items'];
            $nextLink = $res['next'];
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            Log::warning('Import users (Graph) error: ' . $error);
        }
        $roles = ['usuario','editor','admin'];
        return view('users.import', compact('items','q','nextLink','error','roles'));
    }

    public function importStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'selected' => ['required','array','min:1'],
            'selected.*' => ['required','string'], // formato: email|name
            'role' => ['required','in:usuario,editor,admin'],
        ]);

        $created = 0;
        foreach ($data['selected'] as $packed) {
            [$email, $name] = array_pad(explode('|', $packed, 2), 2, '');
            $email = trim($email);
            $name  = trim($name) ?: $email;
            if ($email === '') continue;

            $user = User::query()->where('email', $email)->first();
            if ($user) {
                if (!$user->name && $name) {
                    $user->name = $name;
                    $user->save();
                }
                continue;
            }
            $user = User::create([
                'name' => $name,
                'email'=> $email,
                'password' => Str::password(32),
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            try { $user->syncRoles([$data['role']]); } catch (\Throwable) {}
            $created++;
        }

        return redirect()->route('users.index')
            ->with('success', $created > 0 ? ("Se importaron {$created} usuario(s).") : 'No se crearon usuarios nuevos.');
    }

    /**
     * Eliminación masiva de usuarios (excepto el propio).
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required','array','min:1'],
            'ids.*' => ['integer','distinct','exists:users,id'],
        ]);

        $selfId = (int) (Auth::id() ?? 0);

        $ids = collect($data['ids'])
            ->map(static fn ($v) => (int) $v)
            ->unique()
            ->reject(static fn (int $id) => $id === $selfId) // evitar auto-borrado
            ->values();

        if ($ids->isEmpty()) {
            return redirect()->route('users.index')
                ->with('warning', 'No se eliminaron usuarios.');
        }

        $deleted = 0;
        $errors  = 0;
        $skippedLastAdmins = 0;

        $users = User::whereIn('id', $ids)->get();
        // Contador de administradores restantes para evitar dejar el sistema sin admin
        try {
            $adminsRemaining = User::role('admin')->count();
        } catch (\Throwable $e) {
            $adminsRemaining = null; // si falla, tratamos como sin control y no borramos admins
        }
        foreach ($users as $u) {
            try {
                // Si es admin, evitar borrar el último
                if ($adminsRemaining !== null && method_exists($u, 'hasRole') && $u->hasRole('admin')) {
                    if ($adminsRemaining <= 1) {
                        $skippedLastAdmins++;
                        continue;
                    }
                    // vamos a borrar un admin, decrementar el contador de remanentes
                    $adminsRemaining--;
                }
                $u->delete();
                $deleted++;
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        $resp = redirect()->route('users.index');
        if ($deleted > 0) { $resp->with('success', "Se eliminaron {$deleted} usuario(s)."); }
        if ($errors  > 0) { $resp->with('warning', "No se pudieron eliminar {$errors} usuario(s)."); }
        if ($skippedLastAdmins > 0) { $resp->with('warning', "Se evitó eliminar {$skippedLastAdmins} usuario(s) por ser el último administrador."); }

        if (in_array($selfId, $request->input('ids', []), true)) {
            $resp->with('warning', 'Tu propio usuario no puede eliminarse.');
        }

        return $resp;
    }
}
