@extends('layouts.app')

@section('content')
<div class="l-main__inner">
    <div class="p-login-box">
        <div class="p-login-box__title">{{ __('Reset Password') }}</div>

        <div class="p-login-box__body">
            @if (session('status'))
                <div class="c-form__input-error" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
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

                <div class="c-form">
                    <div class="c-form__submit">
                        <button type="submit" class="c-form__submit__btn">
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
