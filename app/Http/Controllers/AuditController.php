<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class AuditController extends Controller
{
    public function index(): View
    {
        return view('audit.index');
    }
}
