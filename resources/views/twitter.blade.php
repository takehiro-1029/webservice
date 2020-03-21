@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
       
    　　<div id="app">
        <!-- デフォルトだとこの中ではvue.jsが有効 -->
        <!-- example-component はLaravelに入っているサンプルのコンポーネント -->
        <example-component  v-bind:user_nofollowing_account="{{ json_encode($user_nofollowing_account) }}"></example-component>
        </div>
        
        <div class="col-md-10">
            <table class="table table-striped table-dark mt-5">

            <form action="{{ route('twitter.follow') }}" method="post">
                    @csrf<button type="submit">フォロー</button>
                    <input type="hidden"  name="action[]" value="{{$user_nofollowing_account[3]['account_id']}}" />
                    </form> 


           @for ($i = 0; $i <= 14; $i++) 
               <div>
                <form action="{{ route('twitter.follow') }}" method="post">
                    @csrf<button type="submit" name="action[]"  value="{{$user_nofollowing_account[$i]['account_id']}}">フォロー</button></form>                  
                <div>{{$user_nofollowing_account[$i]['account_id']}}</div>     
                <div>{{$user_nofollowing_account[$i]['user_name']}}</div>     
                <div><span>@</span>{{$user_nofollowing_account[$i]['screen_name']}}</div>     
                <div>{{$user_nofollowing_account[$i]['description']}}</div>     
                <div>{{$user_nofollowing_account[$i]['follows_count']}}フォロワー</div>     
                <div>{{$user_nofollowing_account[$i]['friends_count']}}フォロー</div>     
                <div>{{$user_nofollowing_account[$i]['recent_tweet']}}</div>
                <img src="{{$user_nofollowing_account[$i]['profile_image_url']}}" width="60" height="60"></img>
                </div>         
            @endfor

            </table>
        </div>
    </div>
</div>
@endsection