@extends('layouts.app')

@section('title', 'Akses Pengurus')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="lw-auth-page-wrapper">
    @include('public.partials.auth.hero')

    <div class="lw-container lw-auth-board">
        <section class="lw-auth-split lw-auth-page" aria-labelledby="auth-login-title">
            <div class="lw-track-hero-grid">
                <aside class="lw-track-intro" aria-label="Informasi keamanan panel">
                    <h2 class="lw-track-intro__title">{{ $introTitle }}</h2>
                    <p class="lw-track-intro__lead">{{ $introLead }}</p>
                    <ul class="lw-track-benefits">
                        @foreach($securityBenefits as $benefit)
                            <li class="lw-track-benefit">
                                <span class="lw-track-benefit__icon" aria-hidden="true">
                                    @if($loop->index === 0)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                                        </svg>
                                    @elseif($loop->index === 1)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </span>
                                <span class="lw-track-benefit__text">
                                    <strong>{{ $benefit['title'] }}</strong>
                                    <span>{{ $benefit['desc'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </aside>

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
                                <label for="email" class="lw-form-label">Email pengurus <span class="lw-form-label-required">*</span></label>
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
