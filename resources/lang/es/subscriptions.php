<?php

return [
    'create' => [
        'title' => 'Crear suscripción',
    ],

    'actions' => [
        'create' => 'Crear suscripción',
        'close' => 'Cerrar',
        'cancel' => 'Cancelar',
        'save' => 'Guardar suscripción',
        'view' => 'Ver',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'send_now' => 'Enviar ahora',
        'insert_snippet' => 'Insertar fragmento',
        'delete_confirm' => '¿Eliminar esta suscripción?',
        'back' => 'Volver',
    ],

    'tabs' => [
        'configuration' => 'Configuración',
        'report' => 'Reporte',
        'groups' => 'Grupos',
        'users' => 'Usuarios',
        'email' => 'Correo',
        'notifications' => 'Notificaciones',
    ],

    'fields' => [
        'name' => 'Nombre de la suscripción',
        'is_enabled' => 'Habilitada',
        'enabled_label' => 'Ejecutar automáticamente',
        'format' => 'Formato del archivo',
        'recurrence' => 'Recurrencia',
        'timezone' => 'Zona horaria',
        'start_date' => 'Fecha de inicio',
        'end_date' => 'Fecha de fin',
        'hour' => 'Hora',
        'minute' => 'Minuto',
        'cron_expression' => 'Expresión cron',
        'max_attachment_size_mb' => 'Tamaño máximo del adjunto (MB)',
        'report_id' => 'Reporte',
        'groups' => 'Grupos',
        'users' => 'Usuarios',
        'mail_subject' => 'Asunto',
        'mail_body' => 'Contenido del correo',
        'notify_workspace_admins' => 'Notificar administradores del espacio de trabajo',
        'notification_users' => 'Notificar usuarios',
        'notification_groups' => 'Notificar grupos',
        'schedule_window' => 'Ventana de programación',
        'created_by' => 'Creada por',
        'updated_by' => 'Actualizada por',
    ],

    'recurrence' => [
        'daily' => 'Diaria',
        'weekly' => 'Semanal',
        'monthly' => 'Mensual',
        'cron' => 'Personalizada (Cron)',
    ],

    'recurrence_labels' => [
        'daily' => 'Diaria',
        'weekly' => 'Semanal',
        'monthly' => 'Mensual',
        'cron' => 'Expresión cron',
    ],

    'hints' => [
        'start_date' => 'La suscripción comenzará a ejecutarse en esta fecha.',
        'end_date' => 'Déjalo en blanco para ejecutar indefinidamente.',
        'cron_expression' => 'Sintaxis cron estándar con cinco o seis campos.',
        'max_attachment_size_mb' => 'Los adjuntos mayores a este límite se comprimen o se omiten.',
        'report_selection' => 'Selecciona un reporte para cargar páginas y destinatarios.',
        'pages' => 'Elige al menos una página para incluir en la exportación.',
        'groups' => 'Todos los grupos están disponibles; sus miembros recibirán el informe por correo.',
        'users' => 'Todos los usuarios están disponibles; solo necesitan recibir el correo.',
        'mail_subject' => 'Se admiten variables como {{report_name}} y {{date}}.',
        'mail_body' => 'Se admite HTML. Se admiten variables como {{report_name}} y {{datetime}}.',
        'notify_workspace_admins' => 'Incluye administradores del workspace en las notificaciones de error.',
        'notification_users' => 'Usuarios adicionales a notificar ante fallas.',
        'notification_groups' => 'Miembros de estos grupos recibirán notificación de fallas.',
    ],

    'placeholders' => [
        'report' => 'Selecciona un reporte',
        'subject' => 'Reporte programado: {{report_name}} ({{date}})',
        'body' => '<p>Hola,</p><p>Adjuntamos el reporte programado.</p><p>Saludos.</p>',
        'groups_empty' => 'No hay grupos disponibles',
        'users_empty' => 'No hay usuarios disponibles',
        'notification_users_empty' => 'No hay usuarios configurados',
        'notification_groups_empty' => 'No hay grupos configurados',
    ],

    'sections' => [
        'overview' => 'Resumen de la suscripción',
        'recipients' => 'Destinatarios',
        'email' => 'Entrega por correo',
        'notifications' => 'Notificaciones de error',
        'pages' => 'Páginas del reporte',
        'runs' => 'Historial de ejecuciones',
    ],

    'labels' => [
        'deleted_report' => 'Reporte eliminado',
        'no_end_date' => 'Sin fecha de fin',
        'time_at' => 'Hora: :time',
        'no_limit' => 'Sin límite',
        'notify_workspace_admins_yes' => 'Se notificará a los administradores del workspace',
        'notify_workspace_admins_no' => 'No se notificará a los administradores del workspace',
        'no_groups_selected' => 'No se seleccionaron grupos',
        'no_users_selected' => 'No se seleccionaron usuarios',
        'subject_default' => 'Reporte programado',
        'body_default' => 'Se adjunta el reporte programado.',
        'no_notification_users' => 'No hay usuarios configurados',
        'no_notification_groups' => 'No hay grupos configurados',
        'no_pages' => 'No hay páginas seleccionadas',
        'page' => 'Página',
    ],

    'status' => [
        'enabled' => 'Habilitada',
        'disabled' => 'Deshabilitada',
    ],

    'statuses' => [
        'pending' => 'Pendiente',
        'running' => 'En ejecución',
        'success' => 'Correcta',
        'failed' => 'Fallida',
        'skipped' => 'Omitida',
    ],

    'runs' => [
        'scheduled_time' => 'Hora programada',
        'status' => 'Estado',
        'empty' => 'Aún no hay ejecuciones registradas.',
    ],

    'index' => [
        'title' => 'Suscripciones de reportes',
        'subtitle' => 'Administra los envíos programados de reportes',
        'empty' => [
            'title' => 'Aún no hay suscripciones',
            'hint' => 'Crea una suscripción para comenzar.',
        ],
    ],

    'table' => [
        'name' => 'Nombre',
        'report' => 'Reporte',
        'format' => 'Formato',
        'recurrence' => 'Recurrencia',
        'enabled' => 'Habilitada',
        'updated_at' => 'Actualizada',
        'actions' => 'Acciones',
    ],

    'show' => [
        'title' => 'Suscripción: :name',
        'subtitle' => 'Reporte: :report',
        'overview' => 'Resumen de la suscripción',
    ],

    'validation' => [
        'invalid_cron' => 'Ingresa una expresión cron válida (cinco o seis campos).',
        'hour_required' => 'Hora y minuto son obligatorios para esta recurrencia.',
        'recipient_required' => 'Selecciona al menos un destinatario (grupo o usuario).',
        'report_missing' => 'Selecciona un reporte para esta suscripción.',
        'report_forbidden' => 'No tienes acceso al reporte seleccionado.',
        'group_forbidden' => 'Algún grupo seleccionado no tiene acceso al reporte.',
        'user_forbidden' => 'Algún usuario seleccionado no tiene acceso al reporte.',
        'notification_user_forbidden' => 'Algún usuario de notificación no tiene acceso al reporte.',
        'notification_group_forbidden' => 'Algún grupo de notificación no tiene acceso al reporte.',
        'errors_title' => 'Revisa los siguientes errores:',
    ],

    'errors' => [
        'no_recipients' => 'La suscripción no puede ejecutarse porque no tiene destinatarios.',
        'attachment_too_large' => 'El archivo exportado supera el tamaño permitido.',
    ],

    'feedback' => [
        'metadata_error' => 'No pudimos cargar la metadata del reporte. Inténtalo más tarde.',
        'save_error' => 'No pudimos guardar la suscripción. Revisa el formulario y vuelve a intentar.',
        'save_success' => 'Suscripción guardada correctamente.',
        'loading' => 'Preparando metadata.',
    ],

    'mail' => [
        'subject' => [
            'default' => 'Reporte programado: {{report_name}} ({{date}})',
        ],
        'body_default' => 'Hola,<br><br>Adjuntamos el reporte programado.<br><br>Saludos.',
    ],

    'email_editor' => [
        'html' => 'Código HTML',
        'design' => 'Editor visual',
        'preview' => 'Vista previa',
        'preview_hint' => 'La vista previa muestra el correo tal como llegará al destinatario.',
        'preview_empty' => 'Aún no hay contenido. Agrega HTML en la pestaña de código para generar la vista previa.',
        'design_toolbar' => 'Herramientas de formato',
        'font_size' => 'Tamaño de fuente',
    ],

    'notifications' => [
        'failure' => [
            'subject' => 'Falló la suscripción: :name',
            'intro' => 'La suscripción ":name" falló en la última ejecución.',
            'action' => 'Ver suscripción',
            'footer' => 'Recibiste este correo porque estás configurado como destinatario de notificaciones.',
        ],
    ],

    'snippets' => [
        'report_name' => 'Nombre del reporte',
        'date' => 'Fecha programada',
        'datetime' => 'Fecha y hora programadas',
        'subscription_name' => 'Nombre de la suscripción',
        'report_link' => 'Enlace al reporte en el portal',
    ],

    'flash' => [
        'created' => 'Suscripción creada correctamente.',
        'updated' => 'Suscripción actualizada correctamente.',
        'deleted' => 'Suscripción eliminada correctamente.',
        'dispatched_now' => 'La suscripción se envió de inmediato.',
    ],
];
