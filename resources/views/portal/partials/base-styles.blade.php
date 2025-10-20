@once
    @push('css')
        <style>
            .header-actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: flex-end;
                gap: .5rem;
            }

            .header-actions a.btn,
            .header-actions button.btn,
            .header-actions form button.btn {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 38px !important;
                line-height: 1 !important;
                padding: 0 .9rem !important;
                font-size: .875rem !important;
            }

            .header-actions i {
                font-size: .9rem;
                margin-right: .4rem;
            }

            @media (max-width: 480px) {
                .header-actions {
                    justify-content: center;
                    text-align: center;
                }
                .header-actions a.btn,
                .header-actions button.btn {
                    flex: 1 1 auto;
                }
            }
        </style>
    @endpush
@endonce
