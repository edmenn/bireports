<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        return view('dashboards.index'); // “Informes” ya lo tenés
    }
}
