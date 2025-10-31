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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\MicrosoftGraphService;

class UsersController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = strtolower((string) $request->query('status', ''));
        $users = User::query()
            ->when($q !== '', function ($qr) use ($q) {
                $needle = mb_strtolower($q);
                $qr->where(function ($w) use ($needle) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(email) LIKE ?', ["%{$needle}%"]);
                });
            })
            ->when($status !== '', function ($qr) use ($status) {
                if (in_array($status, ['bloqueado','blocked','1','true'], true)) {
                    $qr->where('is_blocked', true);
                } elseif (in_array($status, ['activo','active','0','false'], true)) {
                    $qr->where('is_blocked', false);
                }
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users', 'q'));
    }

    public function create(): View
    {
        $roles = ['admin','viewer'];
        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','max:255'],
            'role'     => ['required','in:admin,viewer'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email'=> $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', __('users.flash.created'));
    }

    public function edit(User $user): View
    {
        $roles = ['admin','viewer'];
        return view('users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email,'.$user->id],
            'password' => ['nullable','string','min:8','max:255'],
            'role'     => ['required','in:admin,viewer'],
            'is_blocked' => ['nullable','boolean'],
        ]);

        // Always allow toggling blocked state (including Entra accounts)
        $requestedBlock = $request->boolean('is_blocked');
        // Prevent users from blocking themselves
        if ($requestedBlock && (int)($user->id) === (int)(Auth::id() ?? 0)) {
            return redirect()->route('users.edit', ['user' => $user->id])
                ->with('warning', __('users.flash.self_block'));
        }
        $user->is_blocked = $requestedBlock;

        // For Entra accounts only the role/state can be changed from this screen
        if (($user->auth_provider ?? null) !== 'entra') {
            $user->name  = $data['name'];
            $user->email = $data['email'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
        }
        $user->save();
        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', __('users.flash.updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting the current user
        $selfId = (int) (Auth::id() ?? 0);
        if ($user->id === $selfId) {
            return redirect()->route('users.index')
                ->with('warning', __('users.flash.self_delete'));
        }

        // Avoid deleting the last admin in the system
        try {
            $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('admin');
            if ($isAdmin) {
                $admins = User::role('admin')->count();
                if ($admins <= 1) {
                    return redirect()->route('users.index')
                        ->with('warning', __('users.flash.only_admin_warning'));
                }
            }
        } catch (\Throwable $e) {
            // Si algo falla al chequear roles, preferimos no dejar al sistema sin admin
            return redirect()->route('users.index')
                ->with('error', __('users.flash.roles_validation_error'));
        }

            $user->delete();
            return redirect()->route('users.index')->with('success', __('users.flash.deleted'));
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
        $roles = ['admin','viewer'];
        return view('users.import', compact('items','q','nextLink','error','roles'));
    }

    public function importStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'selected' => ['required','array','min:1'],
            'selected.*' => ['required','string'], // formato: email|name
            'role' => ['required','in:viewer,admin'],
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
            ->with('success', $created > 0 ? __('users.flash.import_some', ['count' => $created]) : __('users.flash.import_none'));
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
                ->with('warning', __('users.flash.none_to_delete'));
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
        if ($deleted > 0) { $resp->with('success', __('users.flash.bulk_deleted', ['count' => $deleted])); }
        if ($errors  > 0) { $resp->with('warning', __('users.flash.bulk_delete_errors', ['count' => $errors])); }
        if ($skippedLastAdmins > 0) { $resp->with('warning', __('users.flash.bulk_delete_skipped_admins', ['count' => $skippedLastAdmins])); }

        if (in_array($selfId, $request->input('ids', []), true)) {
            $resp->with('warning', __('users.flash.self_delete'));
        }

        return $resp;
    }

    /**
     * Bulk block users (skip current user and preserve the last admin).
     */
    public function bulkBlock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required','array','min:1'],
            'ids.*' => ['integer','distinct','exists:users,id'],
        ]);

        $selfId = (int) (Auth::id() ?? 0);
        $ids = collect($data['ids'])->map(fn($v)=>(int)$v)->unique()->reject(fn($id)=>$id === $selfId)->values();
        if ($ids->isEmpty()) {
            return redirect()->route('users.index')->with('warning', __('users.flash.none_to_block'));
        }

        $blocked=0; $skippedLastAdmins=0; $errors=0;
        $users = User::whereIn('id', $ids)->get();
        try { $adminsRemaining = User::role('admin')->whereNotIn('id', [$selfId])->count(); } catch (\Throwable) { $adminsRemaining = null; }
        foreach ($users as $u) {
            try {
                if ($adminsRemaining !== null && method_exists($u,'hasRole') && $u->hasRole('admin')) {
                    if ($adminsRemaining <= 1) { $skippedLastAdmins++; continue; }
                    $adminsRemaining--;
                }
                if (! (bool)($u->is_blocked ?? false)) { $u->forceFill(['is_blocked'=>true])->save(); $blocked++; }
            } catch (\Throwable) { $errors++; }
        }

        $resp = redirect()->route('users.index');
        if ($blocked>0) $resp->with('success', __('users.flash.bulk_blocked', ['count' => $blocked]));
        if ($skippedLastAdmins>0) $resp->with('warning', __('users.flash.bulk_block_skipped_admins', ['count' => $skippedLastAdmins]));
        if ($errors>0) $resp->with('warning', __('users.flash.bulk_block_errors', ['count' => $errors]));
        return $resp;
    }

    /**
     * Bulk unblock users (skip current user).
     */
    public function bulkUnblock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required','array','min:1'],
            'ids.*' => ['integer','distinct','exists:users,id'],
        ]);

        $selfId = (int) (Auth::id() ?? 0);
        $ids = collect($data['ids'])->map(fn($v)=>(int)$v)->unique()->reject(fn($id)=>$id === $selfId)->values();
        if ($ids->isEmpty()) {
            return redirect()->route('users.index')->with('warning', __('users.flash.none_to_unblock'));
        }

        $unblocked=0; $errors=0;
        $users = User::whereIn('id', $ids)->get();
        foreach ($users as $u) {
            try {
                if ((bool)($u->is_blocked ?? false)) { $u->forceFill(['is_blocked'=>false])->save(); $unblocked++; }
            } catch (\Throwable) { $errors++; }
        }

        $resp = redirect()->route('users.index');
        if ($unblocked>0) $resp->with('success', __('users.flash.bulk_unblocked', ['count' => $unblocked]));
        if ($errors>0) $resp->with('warning', __('users.flash.bulk_unblock_errors', ['count' => $errors]));
        return $resp;
    }

    /**
     * Toggle the blocked state for an individual user.
     */
    public function toggleBlock(Request $request, User $user): RedirectResponse
    {
        $selfId = (int) (Auth::id() ?? 0);
        if ($user->id === $selfId) {
            return back()->with('warning', __('users.flash.self_block'));
        }

        $willBlock = ! (bool) ($user->is_blocked ?? false);
        // Prevent blocking when this is the last admin
        if ($willBlock && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            try { $admins = User::role('admin')->count(); } catch (\Throwable) { $admins = null; }
            if ($admins !== null && $admins <= 1) {
                return back()->with('warning', __('users.flash.only_admin_warning'));
            }
        }

        $attributes = ['is_blocked' => $willBlock];
        if ($willBlock) {
            $attributes['remember_token'] = Str::random(60);
        }

        $user->forceFill($attributes)->save();

        if ($willBlock) {
            try {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            } catch (\Throwable $e) {
                Log::warning('Unable to purge sessions for the blocked user', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
        return back()->with('success', $willBlock ? __('users.flash.blocked') : __('users.flash.unblocked'));
    }
}
