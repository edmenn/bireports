<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\MicrosoftGraphService;

class UsersController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $users = User::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('name', 'ilike', "%{$q}%")
                      ->orWhere('email', 'ilike', "%{$q}%");
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
                // opcional: actualizar nombre si está vacío
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
}
