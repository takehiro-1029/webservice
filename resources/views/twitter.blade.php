@extends('layouts.app')

@section('content')

@include('menubar')

<div id="app">
    <twitteraccount-component  v-bind:user_nofollowing_account="{{ json_encode($user_nofollowing_account) }}"></twitteraccount-component>
</div>
@endsection
