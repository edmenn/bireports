@once
    @push('css')
        <style>
            :root{
                --card-radius: 14px;
                --card-border: rgba(0,0,0,.08);
                --card-hover: rgba(0,123,255,.4);
                --cover-height: 190px;
                --cover-bg: #f8f9fa;
                --card-max-width: 420px;
            }
            .report-grid{
                display:grid;
                grid-template-columns: repeat(1, minmax(0,1fr));
                gap:1rem;
            }
            @media (min-width:576px){ .report-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
            @media (min-width:992px){ .report-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
            @media (min-width:1400px){ .report-grid{ grid-template-columns: repeat(4, minmax(0,1fr)); } }

            .report-card,
            .preview-card-tile{
                position:relative;
                width:100%;
                max-width:var(--card-max-width);
                margin:0 auto;
                border:1px solid var(--card-border);
                border-radius: var(--card-radius);
                background:#fff;
                box-shadow:0 .25rem .5rem rgba(0,0,0,.05);
                overflow:hidden;
                transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
            }
            .report-card:hover{
                transform: translateY(-2px);
                box-shadow:0 .6rem 1.2rem rgba(0,0,0,.08)!important;
                border-color: var(--card-hover);
            }
            .report-card .card-link::after{ z-index: 0; }

            .report-cover{
                width: 100%;
                aspect-ratio: 16 / 9;
                max-height: var(--cover-height);
                background: var(--cover-bg);
                display:flex;
                align-items:center;
                justify-content:center;
                border-bottom:1px solid rgba(0,0,0,.05);
                padding:0;
                overflow:hidden;
            }
            .report-cover .cover-link{
                display:flex;
                align-items:center;
                justify-content:center;
                width:100%;
                height:100%;
                background: var(--cover-bg);
                border-radius: var(--card-radius);
                overflow:hidden;
            }
            .report-cover .cover-scale-target{
                width:100%;
                height:100%;
                object-fit:contain;
                object-position:center;
                display:block;
                transform-origin:center center;
                transition: transform .2s ease-in-out;
            }
            .report-cover .cover-default{
                padding:.75rem;
            }
            .bi-svg{ width:60%; max-height:80%; height:auto; filter: drop-shadow(0 8px 16px rgba(0,0,0,.12)); }

            .description-clamp{
                display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
                overflow:hidden; min-height:2.4em;
            }

            @media (max-width: 768px){
                .report-card,
                .preview-card-tile{
                    max-width:100%;
                }
            }
        </style>
    @endpush
@endonce
