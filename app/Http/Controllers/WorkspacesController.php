<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class WorkspacesController extends Controller
{
    public function index(): View
    {
        return view('workspaces.index');
    }
}
