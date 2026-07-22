@php
    $allowed = empty($item['can']) || auth()->user()?->can($item['can']);
    $children = collect($item['children'] ?? [])->filter(fn ($child) => empty($child['can']) || auth()->user()?->can($child['can']));
    $routeName = $item['route'] ?? null;
    $active = $routeName && request()->routeIs($routeName, $routeName.'.*');
    $childActive = $children->contains(fn ($child) => ! empty($child['route']) && request()->routeIs($child['route'], $child['route'].'.*'));
    $label = fn (array $entry) => __(str_starts_with($entry['text'], 'nav.') ? 'menu.'.$entry['text'] : $entry['text']);
@endphp

@if($allowed && ($routeName || $children->isNotEmpty()))
    <li class="nav-item {{ $childActive ? 'active' : '' }}">
        @if($children->isNotEmpty())
            <a class="nav-link {{ $childActive ? '' : 'collapsed' }}" href="#nav-{{ $loop->index }}" data-bs-toggle="collapse" aria-expanded="{{ $childActive ? 'true' : 'false' }}">
                <span class="nav-link-icon"><i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i></span>
                <span class="nav-link-title">{{ $label($item) }}</span>
                <span class="nav-link-toggle"></span>
            </a>
            <div class="collapse {{ $childActive ? 'show' : '' }}" id="nav-{{ $loop->index }}">
                <ul class="nav nav-pills">
                    @foreach($children as $child)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($child['route'], $child['route'].'.*') ? 'active' : '' }}" href="{{ route($child['route']) }}">
                                <span class="nav-link-icon"><i class="{{ $child['icon'] ?? 'fas fa-circle' }}"></i></span>
                                <span class="nav-link-title">{{ $label($child) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ route($routeName) }}">
                <span class="nav-link-icon"><i class="{{ $item['icon'] ?? 'fas fa-circle' }}"></i></span>
                <span class="nav-link-title">{{ $label($item) }}</span>
            </a>
        @endif
    </li>
@endif
