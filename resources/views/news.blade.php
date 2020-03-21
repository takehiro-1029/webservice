@extends('layouts.app')

@section('content')
<body>
    <main class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <table class="table table-striped table-dark mt-5">
                    
                   @for ($i = 0; $i < 20; $i++)
                       <div>                  
                        <a href="{{$posts['channel']['item'][$i]['link']}}" target="_blank"> 
                            <div>{{$posts['channel']['item'][$i]['title']}}</div>
                        </a>
                        <div>{{$posts['channel']['item'][$i]['pubDate']}}</div>
                        </div>                    
                    @endfor
                    </table>
                </div>
            </div>
    </main>
</body>
@endsection