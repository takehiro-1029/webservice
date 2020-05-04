<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>仮想通貨のトレンドが一目でわかる||CryptoTrend</title>
        <meta name="description" content="仮想通貨トレンド情報を独自方法で解析し無料で提供しています。仮想通貨に関する最新ニュースも閲覧可能、自動フォロー機能で仮想通貨に特化したTwitter運用もお任せください。" />
        <meta name="keywords" content="仮想通貨,トレンド,最新情報">
        
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

                    <div class="p-header__menu-trigger js-toggle-sp-menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <nav class="p-header__nav js-toggle-sp-menu-target">
                        <ul class="p-header__nav-menu">
                            <li class="p-header__nav-menu-item">{{ Auth::user()->name }}</li>
                            <li class="p-header__nav-menu-item"><a href="{{route('cryptotrend.news')}}">関連ニュース</a></li>
                            <li class="p-header__nav-menu-item"><a href="{{route('cryptotrend.rank')}}">トレンド表示</a></li>
                            <li class="p-header__nav-menu-item"><a href="{{route('cryptotrend.usershow')}}">アカウント一覧</a></li>
                            <li class="p-header__nav-menu-item">
                                <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                            </li>
                        </ul>
                    </nav>
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
