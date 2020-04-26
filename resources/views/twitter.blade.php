@extends('layouts.app')

@section('content')

@include('menubar')

<div id="app">
    <twitteraccount-component
    v-bind:user_nofollowing_account="{{ json_encode($user_nofollowing_account) }}"
    v-bind:autofollow_selected="{{ ($autofollow_selected) }}"
    v-bind:follow_num="{{ ($follow_num) }}"></twitteraccount-component>
</div>
@endsection
