<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Contracts\View\View;

class WorkspacesController extends Controller
{
    public function index(): View
    {
        $workspaces = Workspace::orderBy('name')->get();

        return view('workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    }

    public function show(string $workspace): View
    {
        // $workspace llega como ID (uuid). Buscamos y cargamos sus reports.
        $workspace = Workspace::with('reports')
            ->where('id', $workspace)
            ->firstOrFail();

        return view('workspaces.show', [
            'workspace' => $workspace,        // <-- nombre EXACTO que usa la vista
            'reports'   => $workspace->reports,
        ]);
    }
}
