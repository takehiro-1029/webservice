<p>
    {{ __('Hello,This is the customer support center of the cryptotrend site.') }}
</p>
<br>
<br>
<p>
    {{ __('Click link below and reset password.') }}<br>
    {{ __('If you did not request a password reset, no further action is required.') }}
</p>
<br>
<p>
    {{ $actionText }}: <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
</p>
<p>
    ※{{ __('This password reset link will expire in 60 minutes.') }}
</p>
<br>
<p>
  <a href="{{ config('app.url') }}">{{ config('app.name') }}</a>
</p>
