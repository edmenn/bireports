<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class EmpresasController extends Controller
{
    public function index(): View
    {
        $empresas = Empresa::orderBy('name')->paginate(10);
        return view('empresas.index', compact('empresas'));
    }

    public function create(): View
    {
        return view('empresas.create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Empresa::create($request->validated());
        return redirect()->route('empresas.index')->with('success', 'Empresa creada correctamente.');
    }

    public function edit(Empresa $empresa): View
    {
        return view('empresas.edit', ['empresa' => $empresa]);
    }

    public function update(UpdateCompanyRequest $request, Empresa $empresa): RedirectResponse
    {
        $empresa->update($request->validated());
        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada.');
    }

    public function destroy(Empresa $empresa): RedirectResponse
    {
        $empresa->delete();
        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada.');
    }
}
