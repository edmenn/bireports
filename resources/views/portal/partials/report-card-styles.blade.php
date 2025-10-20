@once
    @push('css')
        <style>
            :root{
                --card-radius: 14px;
                --card-border: rgba(0,0,0,.08);
                --card-hover: rgba(25,118,210,.35);
                --cover-height: 170px;
                --cover-bg: #f5f6fa;
            }
            .report-grid{
                display:grid;
                grid-template-columns: repeat(1, minmax(0,1fr));
                gap:1rem;
            }
            @media (min-width:576px){ .report-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
            @media (min-width:992px){ .report-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
            @media (min-width:1400px){ .report-grid{ grid-template-columns: repeat(4, minmax(0,1fr)); } }

            .report-card{
                position:relative;
                border:1px solid var(--card-border);
                border-radius: var(--card-radius);
                background:#fff;
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
                height: var(--cover-height);
                background: var(--cover-bg);
                display:flex;
                align-items:center;
                justify-content:center;
                border-bottom:1px solid rgba(0,0,0,.05);
                padding:.5rem;
            }
            .report-cover img.cover-full,
            .report-cover img.cover-default{
                max-width:100%;
                max-height:100%;
                width:auto;
                height:auto;
                object-fit:contain;
                display:block;
                margin:auto;
                border-radius:.5rem;
            }
            .report-cover .cover-scale-target{
                transition: transform .2s ease-in-out;
                transform-origin:center center;
            }
            .bi-svg{ width:60%; max-height:80%; height:auto; filter: drop-shadow(0 8px 16px rgba(0,0,0,.12)); }

            .description-clamp{
                display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
                overflow:hidden; min-height:2.4em;
            }
        </style>
    @endpush
@endonce
