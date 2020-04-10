<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
    <body>
      <!-- フラッシュメッセージ -->
             @if (session('flash_message'))
                 <div class="c-flash-message js-flash-message">
                     {{ session('flash_message') }}
                 </div>
             @endif

        <header id="l-header" class="l-header">
            <div class="p-header">
                <h1 class="p-header__logo">
                    <a href="{{ route('top') }}">CryptoTrend</a>
                </h1>

                @guest
                    <div class="p-header__btn">
                        <div class="p-header__btn-login">
                            <a class="p-header__btn-login-btn" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </div>
                        @if (Route::has('register'))
                            <div class="p-header__btn-register">
                                <a class="p-header__btn-register-btn" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-header__actions">
                        <div class="p-header__name">{{ Auth::user()->name }} </div>
                        <div class="p-header__logout">
                          <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                          </a>
                          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf
                          </form>
                        </div>
                    </div>
                @endguest

            </div>
        </header>

        @yield('menubar')

        <main class="l-main">
            @yield('content')
        </main>

        <footer id="footer" class="l-footer">
            <div class="l-footer__copyright">
                Copyright © 2020 <a href="{{ route('top') }}">CryptoTrend</a>. All Rights Reserved.
            </div>
        </footer>

    </body>
</html>
