@extends('layouts.app')

@section('content')

@include('menubar')

<div class="l-main__inner">
    <div class="p-news">
        <div class="p-news__header">
            <div class="p-news__title">最新ニュース</div>
            @for ($i = 0; $i < 20; $i++)
                <ul class="p-news__box">
                    <li class="p-news__box-list-link">
                        <a href="{{$googlenews['channel']['item'][$i]['link']}}" target="_blank">
                            <div>{{$googlenews['channel']['item'][$i]['title']}}</div>
                        </a>
                    </li>
                    <li class="p-news__box-list-date">{{$googlenews['channel']['item'][$i]['pubDate']}}</li>
                </ul>
            @endfor
        </div>
    </div>
</div>
@endsection
