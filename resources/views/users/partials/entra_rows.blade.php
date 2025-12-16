@php
    /** @var array<int,array<string,mixed>> $items */
@endphp
@forelse($items as $u)
    @php
        // Preferir givenName + surname; si no, caer a displayName
        $given   = trim((string)($u['givenName'] ?? ''));
        $surname = trim((string)($u['surname'] ?? ''));
        $display = trim($given . ' ' . $surname);
        if ($display === '') {
            $display = trim((string)($u['displayName'] ?? ''));
        }
        $mail    = trim((string)($u['mail'] ?? ''));
        $upn     = trim((string)($u['userPrincipalName'] ?? ''));
        $active  = (bool)($u['accountEnabled'] ?? false);
        $job     = trim((string)($u['jobTitle'] ?? ''));

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
    @php
        $sortKey = mb_strtolower($display ?: ($preferred ?: ''));
    @endphp
    <tr class="js-entra-row" data-sort-name="{{ e($sortKey) }}">
        <td style="width:36px" class="text-center">
            @php
                // Construir lista completa de candidatos (preferido + upn + mail + aliases)
                $cands = [];
                if ($preferred !== '') $cands[] = strtolower($preferred);
                if ($upn !== '')       $cands[] = strtolower($upn);
                if ($mail !== '')      $cands[] = strtolower($mail);
                foreach ($aliases as $a) { $cands[] = strtolower($a); }
                $cands = array_values(array_unique($cands));
                $aliasesAttr = e(implode(',', $cands));
                $exists = \App\Models\User::query()->whereIn('email', $cands)->exists();
            @endphp
            <input type="checkbox" class="js-entra-row-chk"
                   data-email="{{ e($preferred) }}"
                   data-name="{{ e($display ?: $preferred) }}"
                   data-aliases="{{ $aliasesAttr }}"
                   @disabled($preferred === '' || $exists)
                   aria-label="{{ __('users.index.table.select_user', ['name' => $display ?: $preferred]) }}">
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar-initial mr-2" aria-hidden="true">{{ $initial }}</div>
                <div>
                    <div class="fw-600">{{ $display ?: __('users.index.badges.no_name') }}</div>
                    @if(!$active)
                        <small class="badge bg-secondary">{{ __('users.index.badges.inactive') }}</small>
                    @endif
                </div>
            </div>
        </td>
        <td>
            @if($job !== '')
                <span class="text-truncate d-inline-block" style="max-width: 260px;" title="{{ $job }}">{{ $job }}</span>
            @else
                <span class="text-muted">{{ __('users.index.badges.no_position') }}</span>
            @endif
        </td>
        <td>
            @if($preferred !== '')
                <div class="cell-ellipsis">
                    <code>{{ $preferred }}</code>
                    @if($exists)
                        <small class="badge badge-success ml-1">{{ __('users.index.badges.exists') }}</small>
                    @endif
                </div>
            @else
                <span class="text-muted">{{ __('users.index.badges.no_email') }}</span>
            @endif
            {{-- En esta vista no mostramos alias adicionales para evitar ruido visual --}}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center text-muted py-3">{{ __('users.index.messages.entra_no_results') }}</td>
    </tr>
@endforelse
