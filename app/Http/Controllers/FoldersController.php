<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class FoldersController extends Controller
{
    public function index(): View
    {
        return view('folders.index');
    }
}
