@extends('layouts.app')

@section('content')

<section>
         <nav id="side-menu-nav">
          <ul>
              <li><a href="{{route('cryptotrend.mypage')}}">マイページ</a></li>
              <li><a href="{{route('cryptotrend.rank')}}">トレンド表示</a></li>
              <li><a href="{{route('cryptotrend.usershow')}}">アカウント一覧</a></li>
          </ul>
          </nav>
    </section>
    
<div class="container">
    <div class="row justify-content-center">
       
    　　<div id="app">
        <twitteraccount-component  v-bind:user_nofollowing_account="{{ json_encode($user_nofollowing_account) }}"></twitteraccount-component>
        </div>
        
    </div>
</div>
@endsection