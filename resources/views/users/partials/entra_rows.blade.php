@php
    /**
     * @var array<int,array<string,mixed>> $items
     * @var array<string,bool> $existingEmails  // set lowercase de emails existentes en BD
     */
    $existingEmails = $existingEmails ?? [];
@endphp

@forelse($items as $u)
@php
    $name  = trim((string)($u['displayName'] ?? '')) ?: ((string)($u['userPrincipalName'] ?? ''));
    $mail  = trim((string)($u['mail'] ?? ''));
    $upn   = trim((string)($u['userPrincipalName'] ?? ''));
    $active = isset($u['accountEnabled']) ? (bool)$u['accountEnabled'] : null;

    // Candidatos de email
    $candidates = [];
    if ($mail !== '') $candidates[] = strtolower($mail);
    if ($upn  !== '') $candidates[] = strtolower($upn);

    foreach ((array)($u['otherMails'] ?? []) as $alt) {
        $alt = trim((string)$alt);
        if ($alt !== '') $candidates[] = strtolower($alt);
    }
    foreach ((array)($u['proxyAddresses'] ?? []) as $p) {
        $p = trim((string)$p);
        if ($p === '') continue;
        $parts = explode(':', $p, 2);
        $addr = isset($parts[1]) ? $parts[1] : $parts[0];
        if ($addr !== '') $candidates[] = strtolower($addr);
    }

    $candidates = array_values(array_unique($candidates));

    // Elegimos qué email mostrar en la fila (preferimos UPN; si no, mail; si no, otro)
    $emailShow = $upn !== '' ? $upn : ($mail !== '' ? $mail : ( $candidates[0] ?? '' ));

    // ¿Existe ya en BD? (si cualquiera de los alias ya está)
    $exists = false;
    foreach ($candidates as $c) {
        if (isset($existingEmails[$c])) { $exists = true; break; }
    }
@endphp
<tr>
    <td style="width: 40px;">
        @if($emailShow)
            <input type="checkbox"
                   class="chk-user"
                   value="{{ $emailShow }}"
                   data-name="{{ e($name) }}"
                   @if($exists) disabled title="Este usuario (o uno de sus alias) ya existe en el sistema" @endif
            >
        @endif
    </td>
    <td>
        <div class="font-weight-bold">
            {{ $name }}
            @if($exists)
                <span class="badge badge-secondary ml-2">Ya existe</span>
            @endif
        </div>
        <div class="text-muted small">{{ $emailShow }}</div>
    </td>
    <td class="text-nowrap">
        @if($active === true)
            <span class="badge badge-success">Activo</span>
        @elseif($active === false)
            <span class="badge badge-secondary">Inactivo</span>
        @else
            <span class="badge badge-light">N/D</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="3" class="text-center text-muted p-3">Sin resultados.</td>
</tr>
@endforelse
