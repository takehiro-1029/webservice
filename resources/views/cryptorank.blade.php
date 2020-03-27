@extends('layouts.app')

@section('content')
<body>
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
        <!-- デフォルトだとこの中ではvue.jsが有効 -->
        <!-- example-component はLaravelに入っているサンプルのコンポーネント -->
        <cryptorank-component></cryptorank-component>
        </div>
        
    </div>
</div>
</body>
@endsection