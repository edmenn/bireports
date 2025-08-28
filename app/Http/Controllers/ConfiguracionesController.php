<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ConfiguracionesController extends Controller
{
    public function index(): View
    {
        return view('configuraciones.index');
    }
}
