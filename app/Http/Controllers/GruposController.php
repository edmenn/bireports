<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GruposController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $grupos = Group::query()
            ->when($q !== '', function ($qr) use ($q) {
                $needle = mb_strtolower($q);
                return $qr->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]);
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        return view('grupos.index', compact('grupos','q'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->get();
        return view('grupos.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255','unique:groups,name'],
            'description' => ['nullable','string','max:2000'],
            'users'       => ['array'],
            'users.*'     => ['integer','exists:users,id'],
        ]);
        $g = Group::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $g->users()->sync($data['users'] ?? []);
        return redirect()->route('grupos.index')->with('success','Grupo creado.');
    }

    public function edit(Group $grupo): View
    {
        $users = User::orderBy('name')->get();
        return view('grupos.edit', ['grupo'=>$grupo, 'users'=>$users]);
    }

    public function update(Request $request, Group $grupo): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255','unique:groups,name,'.$grupo->id],
            'description' => ['nullable','string','max:2000'],
            'users'       => ['array'],
            'users.*'     => ['integer','exists:users,id'],
        ]);
        $grupo->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $grupo->users()->sync($data['users'] ?? []);
        return redirect()->route('grupos.index')->with('success','Grupo actualizado.');
    }

    public function destroy(Group $grupo): RedirectResponse
    {
        $grupo->delete();
        return redirect()->route('grupos.index')->with('success','Grupo eliminado.');
    }
}
