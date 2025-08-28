<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::orderBy('name')->paginate(15);
        return view('admin.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('admin.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        Client::create($data);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $this->validated($request, $client->id);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $client->update($data);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Cliente actualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();
        return back()->with('success', 'Cliente eliminado.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'                       => ['required','string','max:255'],
            'slug'                       => ['nullable','string','max:255','unique:clients,slug,'.($ignoreId ?? 'NULL').',id'],
            'custom_domain'              => ['nullable','string','max:255'],
            'ga_tracking_code'           => ['nullable','string','max:50'],
            'default_view_type'          => ['required','in:folders,icons,list'],
            'default_email_lang'         => ['required','string','max:10'],
            'branding'                   => ['nullable','array'],
            'login'                      => ['nullable','array'],
            'access_schedule'            => ['nullable','array'],
            'powerbi_client_id'          => ['nullable','string','max:100'],
            'powerbi_tenant_id'          => ['nullable','string','max:100'],
            'powerbi_client_secret_hint' => ['nullable','string','max:100'],
        ]);
    }
}
