<?php

return [
    'page' => [
        'title' => 'Configuraciones',
        'subtitle' => 'Credenciales de Microsoft Fabric / Power BI para App-Owns-Data',
    ],

    'actions' => [
        'view_audit' => 'Ver auditoría',
        'import_now' => 'Importar ahora',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
    ],

    'alerts' => [
        'success' => 'Éxito',
        'error' => 'Error',
        'validation' => 'Errores de validación',
    ],

    'service_principal' => [
        'card_title' => 'Credenciales de Conexión (Service Principal)',
        'intro' => 'Configura una aplicación de Entra ID (Service Principal) con permisos de Power BI para la autenticación desatendida.',
        'fields' => [
            'client_id' => [
                'label' => 'Client ID (Application ID)',
                'placeholder' => 'ID de la aplicación',
                'help' => 'Azure Entra ID → App registrations → Overview → Application (client) ID.',
                'tooltip' => 'Identificador de la app (Client ID) registrada en Entra ID.',
            ],
            'tenant_id' => [
                'label' => 'Tenant ID (Directory ID)',
                'placeholder' => 'ID del directorio',
                'help' => 'Azure Entra ID → Overview → Tenant ID.',
                'tooltip' => 'Debe coincidir con el tenant donde está registrada la app.',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'placeholder' => 'Secreto del cliente',
                'help' => 'Se guarda cifrado. Deja en blanco para no cambiar.',
                'tooltip' => 'Genera el secreto en Azure Entra ID → Certificates & secrets. Evita pegar valores enmascarados.',
            ],
            'refresh_minutes' => [
                'label' => 'Refrescar (min)',
                'placeholder' => '0 = auto',
                'help' => '0 = maneja el SDK automáticamente.',
                'tooltip' => 'Usa 0 para que la librería gestione la renovación; ajusta solo si es necesario.',
            ],
            'timezone' => [
                'label' => 'Zona horaria por defecto',
                'tooltip' => 'Afecta horarios mostrados y ejecución de tareas programadas.',
                'help' => 'Se aplicará al scheduler y como valor por defecto en programación de datasets.',
            ],
        ],
    ],

    'auth' => [
        'microsoft' => [
            'title' => 'Inicio de sesión con Microsoft',
            'description' => 'Permite a los usuarios autenticarse usando sus cuentas de Microsoft.',
        ],
        'local' => [
            'title' => 'Inicio de sesión local',
            'description' => 'Formulario tradicional de email y contraseña. En caso de emergencia, el entorno puede forzar habilitarlo con :env.',
        ],
    ],

    'prewarm' => [
        'title' => 'Pre-warm de capacidades',
        'description' => 'Si está activo, antes de una actualización programada reanudamos (encendemos) la capacidad asociada para evitar fallos por estar pausada. Desactívalo si preferís controlarlo externamente.',
    ],

    'login' => [
        'cards' => [
            'uploads' => [
                'title' => 'Archivos a Subir',
                'fields' => [
                    'logo' => [
                        'label' => 'Logo de login (PNG/JPG/SVG)',
                        'placeholder' => 'Elegir un archivo...',
                        'help' => 'Se recomienda fondo transparente y altura ≤ 90px.',
                        'tooltip' => 'Formatos admitidos: PNG, JPG o SVG. Se muestra sobre el formulario de login.',
                    ],
                    'background' => [
                        'label' => 'Imagen de fondo (pantalla completa)',
                        'placeholder' => 'Elegir un archivo...',
                        'help' => 'Se ajustará para cubrir toda la pantalla en la página de login.',
                        'tooltip' => 'Sugerencia: usar 1920×1080 o superior, con buena compresión.',
                    ],
                ],
            ],
            'preview' => [
                'title' => 'Vistas Previa Actuales',
                'logo_heading' => 'Logo de Login:',
                'logo_alt' => 'Vista previa del logo de login',
                'no_logo' => 'No hay un logo de login cargado.',
                'background_heading' => 'Imagen de Fondo:',
                'background_alt' => 'Vista previa de la imagen de fondo',
                'no_background' => 'No hay una imagen de fondo cargada.',
            ],
            'size' => [
                'title' => 'Tamaño del logo',
                'width_label' => 'Ancho (px)',
                'height_label' => 'Alto (px)',
                'hint' => 'Deja en blanco para autoajustar proporcionalmente.',
            ],
        ],
    ],

    'audit' => [
        'page' => [
            'title' => 'Auditoría de Configuraciones',
            'subtitle' => 'Cambios agrupados por evento',
        ],
        'actions' => [
            'back' => 'Volver a Configuraciones',
        ],
        'form' => [
            'search_label' => 'Buscar',
            'search_placeholder' => 'Campo/valor…',
            'user_label' => 'Usuario (ID)',
            'submit' => 'Filtrar',
        ],
        'table' => [
            'date' => 'Fecha',
            'user' => 'Usuario',
            'ip' => 'IP',
            'changes' => 'Cambios',
            'field' => 'Campo',
            'before' => 'Antes',
            'after' => 'Después',
            'summary' => 'Ver :count cambio(s)',
            'empty' => 'Sin registros',
        ],
    ],

    'guide' => [
        'title' => 'Guía y Buenas Prácticas',
        'permissions' => [
            'title' => 'Permisos requeridos',
            'items' => [
                'assign' => 'Asignar la app al <em>tenant</em> (Admin Portal → Developer settings).',
                'roles' => 'Roles típicos: <code>Tenant.Read.All</code> o <code>Tenant.ReadWrite.All</code>.',
                'access' => 'Conceder acceso a <em>workspaces</em> específicos.',
            ],
        ],
        'recommendations' => [
            'title' => 'Recomendaciones',
            'items' => [
                'secret' => 'Usa un secreto dedicado solo para este portal.',
                'rotate' => 'Rota los secretos periódicamente.',
                'restrict' => 'Restringe la app a los <em>workspaces</em> necesarios.',
            ],
        ],
    ],

    'viewer' => [
        'card_title' => 'Capacidad on-demand (Viewer)',
        'fields' => [
            'enable' => [
                'label' => 'Encendido on-demand',
                'toggle' => 'Activar intento automático',
                'help' => 'Si está desactivado, el viewer no intentará encender la capacidad.',
                'tooltip' => 'Si está activado, el visor intentará encender la capacidad automáticamente cuando un usuario abra un reporte y la capacidad esté pausada.',
                'tooltip_help' => 'Desactívalo si preferís mostrar un mensaje de capacidad pausada sin intentar encender automáticamente.',
            ],
            'message' => [
                'label' => 'Mensaje de encendido',
                'placeholder' => 'Encendiendo la capacidad, aguarde…',
                'default' => 'Encendiendo la capacidad, aguarde unos segundos…',
                'help' => 'Texto que verá el usuario mientras se enciende.',
                'tooltip' => 'Se muestra en un banner de espera hasta que la capacidad esté activa o se alcance el tiempo máximo.',
            ],
            'start_timeout' => [
                'label' => 'Timeout de encendido (seg)',
                'help' => 'Tiempo máximo de espera antes de mostrar error (recomendado: 120s).',
                'tooltip' => 'Si se supera este tiempo sin que la capacidad quede activa, se muestra un error y no se embebe el reporte.',
            ],
            'poll_interval' => [
                'label' => 'Intervalo de consulta (seg)',
                'help' => 'Frecuencia con la que el viewer consulta si la capacidad ya está activa.',
                'tooltip' => 'Intervalos más cortos reaccionan más rápido pero generan más llamadas; 5s suele ser un buen equilibrio.',
            ],
            'report_timeout' => [
                'label' => 'Timeout de carga del reporte (seg)',
                'help' => 'Se inicia después de comenzar el embed; si vence se muestra error de carga.',
                'tooltip' => "Ajusta el umbral de espera para que Power BI reporte el evento 'loaded'.",
            ],
        ],
    ],
];
