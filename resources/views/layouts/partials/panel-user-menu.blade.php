@php
    $user = auth()->user();
@endphp
<div class="lw-panel-user-menu" data-panel-user-menu>
    <button type="button"
            class="lw-panel-user-menu-trigger"
            data-panel-user-menu-trigger
            aria-expanded="false"
            aria-controls="lw-panel-user-menu-panel"
            aria-haspopup="true"
            aria-label="Menu akun {{ $user->name }}">
        <img src="{{ $user->avatarUrl() }}" alt="" width="32" height="32" class="lw-panel-user-menu-avatar">
        <span class="lw-panel-user-menu-trigger-text">
            <span class="lw-panel-user-menu-trigger-name">{{ $user->name }}</span>
            <span class="lw-panel-user-menu-trigger-role">{{ $user->role->label() }}</span>
        </span>
        <span class="lw-panel-user-menu-chevron" aria-hidden="true"></span>
    </button>
    <div id="lw-panel-user-menu-panel"
         class="lw-panel-user-menu-panel"
         data-panel-user-menu-panel
         role="menu"
         hidden>
        <div class="lw-panel-user-menu-head">
            <img src="{{ $user->avatarUrl() }}" alt="" width="40" height="40" class="lw-panel-user-menu-head-avatar">
            <div class="lw-panel-user-menu-head-meta">
                <p class="lw-panel-user-menu-head-name">{{ $user->name }}</p>
                <p class="lw-panel-user-menu-head-role">{{ $user->role->label() }}</p>
                @if($user->email)
                    <p class="lw-panel-user-menu-head-email">{{ $user->email }}</p>
                @endif
            </div>
        </div>
        <div class="lw-panel-user-menu-actions" role="none">
            <form method="POST" action="{{ route('logout') }}" class="lw-panel-user-menu-form" role="none">
                @csrf
                <button type="submit" class="lw-panel-user-menu-item lw-panel-user-menu-item--button lw-panel-user-menu-item--danger" role="menuitem">Keluar</button>
            </form>
        </div>
    </div>
</div>
