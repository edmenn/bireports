<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class CapacitiesController extends Controller
{
    public function index(): View
    {
        return view('capacities.index');
    }
}
