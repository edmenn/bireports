@once
    @push('css')
        <style>
            .portal-card {
                border: none;
                border-top: 3px solid var(--app-hi-bg);
                border-radius: .75rem;
                box-shadow: 0 .65rem 1.6rem rgba(15, 34, 58, .08);
                background-color: var(--app-card-bg);
                margin-bottom: 1.5rem;
                overflow: hidden;
            }

            .portal-card .card-header {
                display: flex;
                align-items: center;
                gap: .75rem;
                padding: 1rem 1.25rem;
                background: var(--app-card-alt-bg);
                border-bottom: 1px solid rgba(15, 34, 58, .08);
            }

            .portal-card .card-header .portal-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: .65rem;
                background-color: var(--app-icon-bg);
                color: var(--app-icon-text);
                font-size: 1.1rem;
            }

            .portal-card .card-header h5 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
                color: var(--app-card-text);
            }

            .portal-card .card-header small {
                display: block;
                color: var(--app-card-text);
                opacity: .72;
            }

            .portal-card .card-body {
                padding: 1.25rem;
                background-color: var(--app-card-bg);
            }

            .portal-empty-state {
                text-align: center;
                color: #6c757d;
                padding: 4rem 1rem;
            }

            .portal-empty-state i {
                display: block;
                font-size: 2rem;
                opacity: .6;
                margin-bottom: .5rem;
            }
        </style>
    @endpush
@endonce
