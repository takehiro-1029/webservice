@extends('layouts.app')

@section('content')
<div class="l-main__inner">
    <div class="p-top">
        <div class="p-top__header">
            <img src="{{ asset('/img/top_msg.jpg') }}" alt="">
            <div class="p-top__header__message">情報収集に時間かけるのはもうやめませんか？</div>
            <div class="p-top__header__btn">
                <a class="p-top__header__btn-register" href="{{ route('register') }}">
                  {{ __('Register_top') }}
                </a>
            </div>
        </div>

        <div class="p-top__main">
            <div class="p-top__main__section">
              <div class="p-top__main__section__content">
                <img src="{{ asset('/img/top_benefit1.jpg') }}" alt="">
                <div>通貨の注目度が一目でわかる</div>
                <p>coincheck取り扱い12銘柄のトレンドを誰よりも早く察知できます</p>
              </div>
              <div class="p-top__main__section__content">
                <img src="{{ asset('/img/top_benefit2.jpg') }}" alt="">
                <div>最新情報を取り逃がさない</div>
                <p>仮想通貨に特化した最新情報をいつでも確認できます</p>
              </div>
              <div class="p-top__main__section__content">
                <img src="{{ asset('/img/top_benefit3.jpg') }}" alt="">
                <div>Twitter運用もお任せ</div>
                <p>自動フォロー機能で手間なくフォロー数を増やすことができます</p>
              </div>
            </div>
            <div class="p-top__main__section__btn">
                <a class="p-top__main__section__btn-register" href="{{ route('register') }}">
                  {{ __('Register_main') }}
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
