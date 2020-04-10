@extends('layouts.app')

@section('content')
<div class="l-main__inner">
  <div class="p-login-box">
    <div class="p-login-box__title">{{ __('Login') }}</div>

    <div class="p-login-box__body">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="p-login-box__body__form c-form">
                <label for="email" class="c-form__label">{{ __('E-Mail Address') }}</label>

                <div class="c-form__input">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                        <span class="c-form__input-error" role="alert">
                            <strong class="c-error__message">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="p-login-box__body__form c-form">
                <label for="password" class="c-form__label">{{ __('Password') }}</label>

                <div class="c-form__input">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                    @error('password')
                        <span class="c-form__input-error" role="alert">
                            <strong class="c-error__message">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>

            <div class="p-login-box__body__form c-form">
                <div class="c-form__check">
                    <input class="c-form__check__input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="c-form__check__label" for="remember">
                        {{ __('Remember Me') }}
                    </label>
                </div>
            </div>

            <div class="c-form">
                <div class="c-form__submit">
                    <button type="submit" class="c-form__submit__btn">
                        {{ __('Login') }}
                    </button>
                    <div class="c-form__submit__request">
                    @if (Route::has('password.request'))
                        <a class="c-form__submit__request-link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                  </div>
                </div>
            </div>
        </form>
    </div>
  </div>
</div>
@endsection
