@props(['role'])

@php
    use App\Enums\UserRole;

    $userRole = $role instanceof UserRole ? $role : UserRole::tryFrom((string) $role);
    $label = $userRole?->label() ?? (string) $role;
    $class = match ($userRole) {
        UserRole::Kelurahan => 'lw-admin-role-badge lw-admin-role-badge--kelurahan',
        UserRole::KetuaRt => 'lw-admin-role-badge lw-admin-role-badge--rt',
        default => 'lw-admin-role-badge',
    };
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $label }}</span>
