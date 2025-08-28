<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AppsController extends Controller
{
    public function index(): View
    {
        return view('apps.index');
    }
}
