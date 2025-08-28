<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Http\Requests\UpdateConfiguracionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

class ConfiguracionesController extends Controller
{
    public function index(): View
    {
        // Garantizamos que exista UNA fila (id=1)
        $config = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'nombre'                        => 'Default',
                'tipo_visualizacion'            => 'pastas',
                'email_locale'                  => 'es',
                'powerbi_scope'                 => 'https://analysis.windows.net/powerbi/api/.default',
                'powerbi_token_refresh_minutes' => 0,
            ]
        );

        return view('configuraciones.index', compact('config'));
    }

    public function update(UpdateConfiguracionRequest $request, Configuracion $configuracion)
    {
        // Solo actualizamos columnas que EXISTEN realmente en la tabla
        $input   = $request->validated();
        $columns = Schema::getColumnListing($configuracion->getTable());
        $data    = array_intersect_key($input, array_flip($columns));

        $configuracion->update($data);

        return redirect()
            ->route('configuraciones.index')
            ->with('success', 'Configuración actualizada.');
    }
}
