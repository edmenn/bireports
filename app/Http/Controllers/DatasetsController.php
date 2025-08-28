<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DatasetsController extends Controller
{
    public function index(): View
    {
        return view('datasets.index');
    }
}
