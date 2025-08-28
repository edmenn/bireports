<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class EmpresasController extends Controller
{
    public function index(): View
    {
        $companies = Company::orderBy('name')->paginate(10);
        return view('empresas.index', compact('companies'));
    }

    public function create(): View
    {
        return view('empresas.create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($request->validated());
        return redirect()->route('empresas.index')->with('success', 'Empresa creada correctamente.');
    }

    public function edit(Company $empresa): View
    {
        return view('empresas.edit', ['company' => $empresa]);
    }

    public function update(UpdateCompanyRequest $request, Company $empresa): RedirectResponse
    {
        $empresa->update($request->validated());
        return redirect()->route('empresas.index')->with('success', 'Empresa actualizada.');
    }

    public function destroy(Company $empresa): RedirectResponse
    {
        $empresa->delete();
        return redirect()->route('empresas.index')->with('success', 'Empresa eliminada.');
    }
}
