<?php
declare(strict_types=1);

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
            ->when($q !== '', function ($qr) use ($q) {
                $needle = mb_strtolower($q);
                return $qr->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]);
            })
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
        return redirect()->route('folders.index')->with('success', __('folders.flash.created'));
    }

    public function edit(Folder $folder): View
    {
        $users = User::orderBy('name')->get();
        $groups = Group::orderBy('name')->get();
        $reports = Report::orderBy('name')->get();
        return view('folders.edit', ['folder' => $folder, 'users' => $users, 'groups' => $groups, 'reports' => $reports]);
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255','unique:folders,name,'.$folder->id],
            'description' => ['nullable','string','max:2000'],
            'users'   => ['array'], 'users.*'   => ['integer','exists:users,id'],
            'groups'  => ['array'], 'groups.*'  => ['integer','exists:groups,id'],
            'reports' => ['array'], 'reports.*' => ['string','exists:reports,id'],
        ]);
        $folder->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $folder->users()->sync($data['users'] ?? []);
        $folder->groups()->sync($data['groups'] ?? []);
        $folder->reports()->sync($data['reports'] ?? []);
        return redirect()->route('folders.index')->with('success', __('folders.flash.updated'));
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        $folder->delete();
        return redirect()->route('folders.index')->with('success', __('folders.flash.deleted'));
    }
}
