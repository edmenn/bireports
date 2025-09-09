@php
    /** @var array<int,array<string,mixed>> $items */
@endphp
@forelse($items as $u)
    @php
        $display = trim((string)($u['displayName'] ?? ''));
        $mail    = trim((string)($u['mail'] ?? ''));
        $upn     = trim((string)($u['userPrincipalName'] ?? ''));
        $active  = (bool)($u['accountEnabled'] ?? false);

        $aliases = [];
        foreach ((array)($u['otherMails'] ?? []) as $alt) {
            $alt = trim((string)$alt);
            if ($alt !== '') { $aliases[] = strtolower($alt); }
        }
        foreach ((array)($u['proxyAddresses'] ?? []) as $p) {
            $p = trim((string)$p);
            if ($p === '') { continue; }
            $parts = explode(':', $p, 2);
            $addr  = isset($parts[1]) ? $parts[1] : $parts[0];
            if ($addr !== '') { $aliases[] = strtolower($addr); }
        }

        $preferred = $mail !== '' ? $mail : ($upn !== '' ? $upn : ($aliases[0] ?? ''));
        $initial   = $display !== '' ? mb_strtoupper(mb_substr($display, 0, 1)) : 'U';
    @endphp
    <tr>
        <td style="width:36px" class="text-center">
            <input type="checkbox" class="js-entra-row-chk"
                   data-email="{{ e($preferred) }}"
                   data-name="{{ e($display ?: $preferred) }}"
                   @disabled($preferred === '')
                   aria-label="Seleccionar {{ $display ?: $preferred }}">
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar-initial mr-2" aria-hidden="true">{{ $initial }}</div>
                <div>
                    <div class="fw-600">{{ $display ?: 'Sin nombre' }}</div>
                    @if(!$active)
                        <small class="badge bg-secondary">Inactivo</small>
                    @endif
                </div>
            </div>
        </td>
        <td>
            @if($preferred !== '')
                <div class="cell-ellipsis"><code>{{ $preferred }}</code></div>
            @else
                <span class="text-muted">Sin email</span>
            @endif
            @if(!empty($aliases))
                <div>
                    @foreach(array_slice($aliases, 0, 3) as $a)
                        @if(strtolower($a) !== strtolower($preferred))
                            <small class="badge badge-light text-muted">{{ $a }}</small>
                        @endif
                    @endforeach
                    @if(count($aliases) > 3)
                        <small class="text-muted">+{{ count($aliases) - 3 }} alias</small>
                    @endif
                </div>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center text-muted py-3">Sin resultados</td>
    </tr>
@endforelse

