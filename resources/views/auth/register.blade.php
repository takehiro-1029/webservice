@extends('layouts.app')

@section('content')
<div class="l-main__inner">
    <div class="p-login-box">
        <div class="p-login-box__title">{{ __('Register') }}</div>

        <div class="p-login-box__body">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="p-login-box__body__form c-form">
                    <label for="name" class="c-form__label">{{ __('Name') }}</label>

                    <div class="c-form__input">
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                        @error('name')
                            <span class="c-form__input-error" role="alert">
                                <strong class="c-error__message">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="p-login-box__body__form c-form">
                    <label for="email" class="c-form_label">{{ __('E-Mail Address') }}</label>

                    <div class="c-form__input">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

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
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                        @error('password')
                            <span class="c-form__input-error" role="alert">
                                <strong class="c-error__message">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="p-login-box__body__form c-form">
                    <label for="password-confirm" class="c-form__label">{{ __('Confirm Password') }}</label>

                    <div class="c-form__input">
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                    </div>
                </div>

                <div class="c-form">
                    <div class="c-form__submit">
                        <button type="submit" class="c-form__submit__btn">
                            {{ __('Register') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
