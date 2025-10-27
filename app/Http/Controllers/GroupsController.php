<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GroupsController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $groups = Group::query()
            ->when($query !== '', function ($builder) use ($query) {
                $needle = mb_strtolower($query);
                return $builder->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]);
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('groups.index', [
            'groups' => $groups,
            'query'  => $query,
        ]);
    }

    public function create(): View
    {
        $users = User::orderBy('name')->get();

        return view('groups.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:groups,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'users'       => ['array'],
            'users.*'     => ['integer', 'exists:users,id'],
        ]);

        $group = Group::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $group->users()->sync($data['users'] ?? []);

        return redirect()
            ->route('groups.index')
            ->with('success', __('groups.flash.created'));
    }

    public function edit(Group $group): View
    {
        $users = User::orderBy('name')->get();

        return view('groups.edit', compact('group', 'users'));
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:groups,name,' . $group->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'users'       => ['array'],
            'users.*'     => ['integer', 'exists:users,id'],
        ]);

        $group->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $group->users()->sync($data['users'] ?? []);

        return redirect()
            ->route('groups.index')
            ->with('success', __('groups.flash.updated'));
    }

    public function destroy(Group $group): RedirectResponse
    {
        $group->delete();

        return redirect()
            ->route('groups.index')
            ->with('success', __('groups.flash.deleted'));
    }
}
