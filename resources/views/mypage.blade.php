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
   
    <main class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <table class="table table-striped table-dark mt-5">
                    
                   @for ($i = 0; $i < 20; $i++)
                       <div>                  
                        <a href="{{$googlenews['channel']['item'][$i]['link']}}" target="_blank"> 
                            <div>{{$googlenews['channel']['item'][$i]['title']}}</div>
                        </a>
                        <div>{{$googlenews['channel']['item'][$i]['pubDate']}}</div>
                        </div>                    
                    @endfor
                    </table>
                </div>
            </div>
    </main>
</body>
@endsection