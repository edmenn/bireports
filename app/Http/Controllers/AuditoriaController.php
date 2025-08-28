<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AuditoriaController extends Controller
{
    public function index(): View
    {
        return view('auditoria.index');
    }
}
