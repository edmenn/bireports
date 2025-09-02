<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Group;
use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FoldersController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $folders = Folder::query()
            ->when($q !== '', fn($qr)=> $qr->where('name','ilike',"%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        return view('folders.index', compact('folders','q'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        $reports = Report::orderBy('name')->get();
        return view('folders.create', compact('users','groups','reports'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255','unique:folders,name'],
            'description' => ['nullable','string','max:2000'],
            'users'   => ['array'], 'users.*'   => ['integer','exists:users,id'],
            'groups'  => ['array'], 'groups.*'  => ['integer','exists:groups,id'],
            'reports' => ['array'], 'reports.*' => ['string','exists:reports,id'],
        ]);
        $f = Folder::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $f->users()->sync($data['users'] ?? []);
        $f->groups()->sync($data['groups'] ?? []);
        $f->reports()->sync($data['reports'] ?? []);
        return redirect()->route('folders.index')->with('success','Carpeta creada.');
    }

    public function edit(Folder $carpeta): View
    {
        $users = User::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        $reports = Report::orderBy('name')->get();
        return view('folders.edit', ['folder'=>$carpeta, 'users'=>$users, 'groups'=>$groups, 'reports'=>$reports]);
    }

    public function update(Request $request, Folder $carpeta): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255','unique:folders,name,'.$carpeta->id],
            'description' => ['nullable','string','max:2000'],
            'users'   => ['array'], 'users.*'   => ['integer','exists:users,id'],
            'groups'  => ['array'], 'groups.*'  => ['integer','exists:groups,id'],
            'reports' => ['array'], 'reports.*' => ['string','exists:reports,id'],
        ]);
        $carpeta->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $carpeta->users()->sync($data['users'] ?? []);
        $carpeta->groups()->sync($data['groups'] ?? []);
        $carpeta->reports()->sync($data['reports'] ?? []);
        return redirect()->route('folders.index')->with('success','Carpeta actualizada.');
    }

    public function destroy(Folder $carpeta): RedirectResponse
    {
        $carpeta->delete();
        return redirect()->route('folders.index')->with('success','Carpeta eliminada.');
    }
}
