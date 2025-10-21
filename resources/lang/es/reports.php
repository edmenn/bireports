<?php

return [
    'index' => [
        'title' => 'Todos los reportes',
        'subtitle' => 'Listado importado desde Microsoft Fabric / Power BI',
        'import_now' => 'Importar ahora',
        'empty' => [
            'title' => 'No hay reportes importados',
            'hint' => 'Ejecutá una importación para ver tus tableros acá.',
            'action' => 'Importar ahora',
        ],
        'metrics' => [
            'total' => 'Reportes totales',
            'with_cover' => 'Con portada',
            'without_cover' => 'Sin portada',
        ],
        'filters' => [
            'search_label' => 'Buscar',
            'search_placeholder' => 'Título, report ID, workspace o dataset ID...',
            'workspace_label' => 'Workspace',
            'workspace_all' => 'Todos',
            'cover_label' => 'Portada',
            'cover_all' => 'Todas',
            'cover_yes' => 'Con portada',
            'cover_no' => 'Sin portada',
            'order_label' => 'Ordenar',
            'order_options' => [
                'name_asc' => 'Nombre (A-Z)',
                'name_desc' => 'Nombre (Z-A)',
                'workspace_asc' => 'Workspace (A-Z)',
                'workspace_desc' => 'Workspace (Z-A)',
            ],
            'hint' => 'Los filtros y el orden se aplican en el navegador, sin recargar la página.',
        ],
        'cards' => [
            'open_title' => 'Abrir reporte',
            'edit_title' => 'Editar portada y descripción',
            'cover_aria' => 'Portada del reporte',
            'cover_alt' => 'Portada de :name',
            'default_cover_aria' => 'Portada genérica',
        ],
        'actions' => [
            'open' => 'Abrir',
            'edit' => 'Editar',
            'view' => 'Ver reporte',
        ],
        'badges' => [
            'workspace' => 'Workspace',
            'dataset' => 'Dataset',
            'report' => 'Report',
        ],
        'copy' => [
            'dataset_title' => 'Copiar Dataset ID',
            'dataset_label' => 'Dataset',
            'report_title' => 'Copiar Report ID',
            'report_label' => 'Report',
            'success' => 'Copiado',
            'error' => 'No se pudo copiar al portapapeles.',
        ],
    ],

    'show' => [
        'title_fallback' => 'Reporte',
        'loading' => 'Cargando reporte...',
        'fullscreen_enter' => 'Ver en pantalla completa',
        'fullscreen_exit' => 'Salir de pantalla completa',
        'missing_embed' => [
            'title' => 'No pudimos preparar el embed del reporte.',
            'hint' => 'Revisá las credenciales y los permisos de Power BI en Configuraciones.',
            'link' => 'Configuraciones',
            'summary' => 'Payload devuelto por PowerBiService::generateReportEmbedToken()',
            'items' => [
                'embed_url' => 'URL de incrustación',
                'access_token' => 'Token de acceso',
                'report_id' => 'ID del reporte',
            ],
            'status' => [
                'ok' => 'Disponible',
                'missing' => 'Falta',
            ],
        ],
        'errors' => [
            'no_credentials' => 'Faltan credenciales de Power BI en Configuraciones.',
            'embed_failure' => 'No se pudo preparar el embed del reporte. Revisá credenciales, capacidad y permisos.',
            'missing_fields' => 'Faltan datos de embedUrl, accessToken o reportId.',
            'client_library' => 'No se pudo cargar la librería powerbi-client.',
            'capacity_paused' => 'La capacidad está pausada por fuera del horario operativo.',
            'capacity_timeout' => 'No fue posible activar la capacidad a tiempo (timeout).',
            'load_timeout' => 'El reporte tardó demasiado en cargar (timeout de 10 s). Revisá la red, el token o la capacidad.',
            'debug_template' => ":exception: :message\nCódigo: :code\nArchivo: :file (línea :line)\n\nTraza:\n:trace",
        ],
        'on_demand' => [
            'starting' => 'Encendiendo la capacidad, aguardá unos segundos...',
        ],
    ],

    'edit' => [
        'title' => 'Editar reporte',
        'card_title' => 'Portada y descripción',
        'view_report' => 'Ver reporte',
        'preview' => [
            'card_title' => 'Vista previa del reporte',
            'scale_label' => 'Tamaño en la lista',
            'scale_display' => 'Tamaño actual: :value%',
            'width_label' => 'Ancho',
            'height_label' => 'Alto',
            'width_display' => 'Ancho: :value %',
            'height_display' => 'Alto: :value %',
            'lock_label' => 'Bloquear proporción',
        ],
        'alerts' => [
            'errors_title' => 'Revisá los errores:',
            'close' => 'Cerrar',
        ],
        'form' => [
            'read_only' => [
                'title' => 'Título',
                'workspace_id' => 'Workspace ID',
                'report_id' => 'Report ID',
            ],
            'preview' => [
                'label' => 'Vista previa',
                'hint' => 'Si no subís archivo ni definís una URL válida, se usará la portada predeterminada (si existe) o el ícono de BI.',
            ],
            'upload' => [
                'label' => 'Subir imagen (opcional)',
                'hint' => 'Formatos permitidos: JPG, PNG, WEBP, GIF o SVG. Máximo 2 MB.',
                'button' => 'Seleccionar archivo',
                'status' => [
                    'empty' => 'Ningún archivo seleccionado',
                    'selected' => 'Archivo seleccionado: :name',
                ],
            ],
            'remove_cover' => [
                'label' => 'Eliminar imagen actual',
                'hint' => 'Vuelve a la portada predeterminada o al icono por defecto.',
            ],
            'url' => [
                'label' => 'URL de imagen (opcional)',
                'placeholder' => 'https://tu-cdn/portadas/reporte.png',
                'hint' => 'Si subís un archivo, la URL se ignora.',
            ],
            'description' => [
                'label' => 'Descripción (opcional)',
                'placeholder' => 'Breve descripción del contenido, público objetivo, filtros destacados, etc.',
            ],
            'allow_resume' => [
                'label' => 'Permitir encender la capacidad fuera del horario operativo para este reporte',
                'hint' => 'Si está activo, cualquier usuario puede encenderla al abrir el reporte; después se apagará por inactividad.',
            ],
        ],
        'buttons' => [
            'cancel' => 'Cancelar',
            'save' => 'Guardar cambios',
        ],
    ],

    'validation' => [
        'cover_url_url' => 'La imagen debe ser una URL válida.',
        'cover_file_max' => 'La imagen no debe superar los 2 MB.',
        'cover_file_mimetypes' => 'Formatos permitidos: JPG, PNG, WEBP, GIF o SVG.',
        'cover_scale_between' => 'El tamaño debe estar entre 50% y 150%.',
        'cover_scale_numeric' => 'El tamaño debe ser un valor numérico.',
    ],

    'flash' => [
        'updated_default' => 'Reporte actualizado y portada establecida como predeterminada.',
        'updated' => 'Reporte actualizado correctamente.',
    ],
];
