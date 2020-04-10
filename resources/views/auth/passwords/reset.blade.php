@extends('layouts.app')

@section('content')
<div class="l-main__inner">
    <div class="p-login-box">
                <div class="p-login-box__title">{{ __('Reset Password') }}</div>

                <div class="p-login-box__body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="p-login-box__body__form c-form">
                            <label for="email" class="c-form__label">{{ __('E-Mail Address') }}</label>

                            <div class="c-form__input">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

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
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
    </div>
</div>
@endsection
