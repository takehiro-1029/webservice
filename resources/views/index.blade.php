<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
    <main class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <table class="table table-striped table-dark mt-5">
                    
                   @for ($i = 0; $i < 100; $i++)                      
                        <a href="{{$posts['channel']['item'][$i]['link']}}" target="_blank"> 
                            <div>{{$posts['channel']['item'][$i]['title']}}</div>
                        </a>
                        <div>{{$posts['channel']['item'][$i]['pubDate']}}</div>                       
                    @endfor
                                          
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>

</html>