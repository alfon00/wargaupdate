@extends('layouts.app')

@section('title', 'Akses Pengurus')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="lw-auth-page-wrapper">
    <div class="lw-container lw-auth-board">
        <section class="lw-auth-split lw-auth-page" aria-labelledby="auth-login-title">
            <div class="lw-track-hero-grid lw-track-hero-grid--solo">
                <div class="lw-auth-forms">
                    <article class="lw-form-card lw-auth-form-card">
                        <header class="lw-track-split__head">
                            <h1 id="auth-login-title" class="lw-track-split__title">Masuk ke panel</h1>
                            <p class="lw-track-split__lead">{{ $formLead }}</p>
                        </header>

                        @if($errors->any())
                            <div class="lw-alert lw-alert--error lw-auth-split__alert" role="alert">
                                <ul class="list-none p-0 m-0">
                                    @foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}" class="lw-auth-split__form">
                            @csrf

                            <div class="lw-form-field">
                                <label for="email" class="lw-form-label">Email pengurus RT atau kelurahan <span class="lw-form-label-required">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                    autocomplete="username" class="lw-form-input" placeholder="nama@contoh.com">
                            </div>

                            <div class="lw-form-field">
                                <label for="password" class="lw-form-label">Kata sandi <span class="lw-form-label-required">*</span></label>
                                <div class="lw-auth-split__password-wrap">
                                    <input type="password" id="password" name="password" required
                                        autocomplete="current-password" class="lw-form-input lw-auth-split__password-input">
                                    <button type="button" class="lw-auth-split__password-toggle" id="auth-password-toggle"
                                        aria-label="Tampilkan kata sandi" aria-pressed="false" aria-controls="password">
                                        <svg class="lw-auth-split__eye lw-auth-split__eye--show" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        <svg class="lw-auth-split__eye lw-auth-split__eye--hide" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                            <line x1="1" y1="1" x2="23" y2="23"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <label class="lw-form-check lw-auth-split__remember">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                Ingat saya di perangkat ini
                            </label>

                            <button type="submit" class="lw-auth-split__submit">Masuk</button>
                        </form>

                        <p class="lw-auth-split__note">{{ $loginNote }}</p>
                    </article>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var toggle = document.getElementById('auth-password-toggle');
    var input = document.getElementById('password');
    if (!toggle || !input) return;

    var eyeShow = toggle.querySelector('.lw-auth-split__eye--show');
    var eyeHide = toggle.querySelector('.lw-auth-split__eye--hide');

    toggle.addEventListener('click', function () {
        var visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', visible ? 'false' : 'true');
        toggle.setAttribute('aria-label', visible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        if (eyeShow) eyeShow.hidden = !visible;
        if (eyeHide) eyeHide.hidden = visible;
    });
})();
</script>
@endpush
