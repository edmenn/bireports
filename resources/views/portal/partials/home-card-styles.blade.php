@once
    @push('css')
        <style>
            .portal-card {
                border: none;
                border-top: 3px solid #0d6efd;
                border-radius: .75rem;
                box-shadow: 0 .65rem 1.6rem rgba(15, 34, 58, .08);
                background-color: #fff;
                margin-bottom: 1.5rem;
                overflow: hidden;
            }

            .portal-card .card-header {
                display: flex;
                align-items: center;
                gap: .75rem;
                padding: 1rem 1.25rem;
                background: linear-gradient(180deg, #f8f9fb 0%, #eef2f7 100%);
                border-bottom: 1px solid rgba(15, 34, 58, .08);
            }

            .portal-card .card-header .portal-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: .65rem;
                background-color: rgba(13, 110, 253, .12);
                color: #0d6efd;
                font-size: 1.1rem;
            }

            .portal-card .card-header h5 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
                color: #1f2d3d;
            }

            .portal-card .card-header small {
                display: block;
                color: #6c757d;
            }

            .portal-card .card-body {
                padding: 1.25rem;
                background-color: #fff;
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
