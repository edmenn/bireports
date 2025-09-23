@php
    $rawFavIds     = $favIds ?? [];
    $favSet        = is_array($rawFavIds)
        ? $rawFavIds
        : (($rawFavIds instanceof \Illuminate\Support\Collection) ? $rawFavIds->all() : []);
    $cover         = trim((string)($report->effective_cover_url ?? ''));
    $hasCover      = $cover !== '';
    $isDefault     = $hasCover && \Illuminate\Support\Str::contains($cover, 'report-covers/default');
    $description   = trim((string)($report->description ?? ''));
    $isFav         = in_array($report->id, $favSet, true);
    $workspaceId   = $report->workspace_id;
@endphp

<div class="report-card shadow-sm d-flex flex-column">
    @if($workspaceId)
        <a class="stretched-link text-decoration-none card-link"
           href="{{ route('reports.show', [$workspaceId, $report->id]) }}"
           title="Abrir {{ $report->name }}">
        </a>
    @endif

    <div class="report-cover" aria-label="Portada del reporte">
        @if($hasCover)
            <img src="{{ $cover }}"
                 alt="Portada de {{ $report->name }}"
                 loading="lazy"
                 class="{{ $isDefault ? 'cover-default' : 'cover-full' }}">
        @else
            <svg class="bi-svg" viewBox="0 0 128 128" role="img" aria-label="Portada generica">
                <defs>
                    <linearGradient id="biYellow" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#FFD93D"/>
                        <stop offset="100%" stop-color="#FFC107"/>
                    </linearGradient>
                </defs>
                <rect x="8" y="28" width="112" height="80" rx="12" fill="url(#biYellow)"/>
                <rect x="8" y="28" width="112" height="80" rx="12" fill="#000" opacity=".05"/>
                <rect x="8" y="18" width="56" height="20" rx="6" fill="url(#biYellow)"/>
                <rect x="8" y="18" width="56" height="20" rx="6" fill="#000" opacity=".05"/>
                <g transform="translate(28,50)" fill="#fff">
                    <rect x="0"   y="30" width="12" height="28" rx="2"/>
                    <rect x="20"  y="18" width="12" height="40" rx="2"/>
                    <rect x="40"  y="10" width="12" height="48" rx="2"/>
                    <rect x="60"  y="4"  width="12" height="54" rx="2"/>
                </g>
            </svg>
        @endif
    </div>

    <div class="px-3 pt-3 pb-2">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <h3 class="h6 fw-semibold text-truncate mb-0 pe-2" title="{{ $report->name }}" style="max-width: 70%;">
                {{ $report->name }}
            </h3>
            <form action="{{ route('portal.fav.toggle', $report) }}"
                  method="POST"
                  class="js-fav-form position-relative flex-shrink-0"
                  style="z-index:2;">
                @csrf
                <button type="submit"
                        class="btn btn-sm btn-fav js-fav-btn {{ $isFav ? 'is-on' : '' }}"
                        data-report-id="{{ $report->id }}"
                        title="{{ $isFav ? 'Quitar de favoritos' : 'Agregar a favoritos' }}"
                        aria-label="{{ $isFav ? 'Quitar de favoritos' : 'Agregar a favoritos' }}"
                        onclick="event.stopPropagation();">
                    <i class="{{ $isFav ? 'fas' : 'far' }} fa-star"></i>
                </button>
            </form>
        </div>
    </div>

    @if($description !== '')
        <div class="px-3 pb-3 small text-muted description-clamp">
            {{ $description }}
        </div>
    @endif

    <div class="px-3 pb-3 mt-auto position-relative" style="z-index:2;">
        @if($workspaceId)
            <a href="{{ route('reports.show', [$workspaceId, $report->id]) }}"
               class="btn btn-primary w-100"
               onclick="event.stopPropagation();"
               title="Abrir {{ $report->name }}">
                <i class="fas fa-chart-line me-1"></i> Abrir
            </a>
        @else
            <span class="text-muted small">Workspace no asignado</span>
        @endif
    </div>
</div>
