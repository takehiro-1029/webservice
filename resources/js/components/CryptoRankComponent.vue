<template>
    <div class="l-main__inner">
        
        <readguruguru-component :isActive="isActive_readdone"></readguruguru-component>
        
        <div class="p-rank-box">
            <div class="p-rank-box__title">トレンドランキング</div>
            <div class="p-rank-box__info">
                <div class="p-rank-box__info__price">BTC過去24時間価格</div>
                <div class="p-rank-box__info__price">最高:{{btc.high}}円</div>
                <div class="p-rank-box__info__price">最低:{{btc.low}}円</div>
            </div>
            <div class="p-rank-box__select">
                <label class="p-rank-box__select__check" v-for="value in cryptocheck">
                    <input type="checkbox" :value="value" v-model="cryptoselected">
                    <span>{{value}}</span>
                </label>
            </div>
            <div class="p-rank-box__buttons">
                <ul>
                    <li class="p-rank-box__buttons__btn" :class="{'is-active': isActive_hour}" @click="hourcomment">過去1時間</li>
                    <li class="p-rank-box__buttons__btn" :class="{'is-active': isActive_day}" @click="daycomment">過去1日</li>
                    <li class="p-rank-box__buttons__btn" :class="{'is-active': isActive_week}" @click="weekcomment">過去1週間</li>
                </ul>
            </div>
            <div class="p-rank-box__table">
                <div class="p-rank-box__table__update">更新日時:{{updatetime}}</div>
                <table>
                    <thead>
                        <tr>
                            <th class="p-rank-box__table__no">No.</th>
                            <th class="p-rank-box__table__name">銘柄</th>
                            <th class="p-rank-box__table__tweets">ツイート数</th>
                            <th class="p-rank-box__table__price">現在価格（円）</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(crypto, index) in sortedItemsByAmount">
                            <td class="p-rank-box__table__no">{{index+1}}</td>
                            <td class="p-rank-box__table__name"><a :href="'https://twitter.com/search?q=%23' + crypto.name" target="_blank">{{crypto.name}}</a></td>
                            <td class="p-rank-box__table__tweets">{{crypto.commentnum}}</td>
                            <td class="p-rank-box__table__price">{{crypto.currentprice}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        /*
        btc:コインチェックAPIから取得したbitcoinの最高価格等を入れる
        updatetime:最後にコメント取得した時間をDBから取り出して入れる
        isActive_hour,isActive_day,isActive_week:trueにした箇所のボタンをactiveにする
        isActive_readdone:falseで読み込み中のぐるぐるマークを表示
        cryptodetail：それぞれの通貨の情報を入れる
        cryptocheck：checkboxで通貨を表示するために使用（処理では使用していない）
        cryptoselected：現在ユーザーが選択している通貨を表示
        */
        data: function() {
            return {
                btc : [],
                updatetime : '',
                isActive_hour : true,
                isActive_day : false,
                isActive_week : false,
                isActive_readdone : true,
                cryptodetail : [
                    {name: "BTC", currentprice : 0, commentnum : 0},
                    {name: "ETH", currentprice : 0, commentnum : 0},
                    {name: "ETC", currentprice : 0, commentnum : 0},
                    {name: "LSK", currentprice : 0, commentnum : 0},
                    {name: "FCT", currentprice : 0, commentnum : 0},
                    {name: "XRP", currentprice : 0, commentnum : 0},
                    {name: "XEM", currentprice : 0, commentnum : 0},
                    {name: "LTC", currentprice : 0, commentnum : 0},
                    {name: "BCH", currentprice : 0, commentnum : 0},
                    {name: "MONA", currentprice : 0, commentnum : 0},
                    {name: "XLM", currentprice : 0, commentnum : 0},
                    {name: "QTUM", currentprice : 0, commentnum : 0},
                    ],
                cryptocheck : ["BTC","ETH","ETC","LSK","FCT","XRP","XEM","LTC","BCH","MONA","XLM","QTUM"],
                cryptoselected: ["BTC","ETH","ETC","LSK","FCT","XRP","XEM","LTC","BCH","MONA","XLM","QTUM"],
            }
        },
        /*
        この画面遷移時に直近1h間のコメント数をDBから取得+コインチェックAPIから直近のBTC価格とそれぞれの通貨の現在価格を取得して格納
        */
        mounted : function(){
            //読みこみ中を表示するためにfalseにする
            this.isActive_readdone = false;
            //直近1h間のコメント数をDBから取得する処理
            axios.get('/api/hourcomment').then((res)=>{
                //通貨の数ループを回してひとつずつ情報を入れていき、最後に更新時間を入れる
                for(let i = 0; i < Object.keys(res.data.hourcomment).length; i++){
                    this.$set(this.cryptodetail[i], 'commentnum', res.data.hourcomment[this.cryptodetail[i].name])
                };
                this.updatetime = res.data.searchendtime.search_endtime;
            }).catch(error => console.log(error))
            //コインチェックAPIから直近のBTC価格とそれぞれの通貨の現在価格を取得する処理
            axios.get('/api/coincheck').then((res)=>{
                //通貨の数ループを回してひとつずつ情報を入れていく、最後にBTC価格情報を入れて読み込み中を戻す
                for(let i = 0; i < res.data.current_price.length; i++){
                    this.$set(this.cryptodetail[i], 'currentprice', res.data.current_price[i])
                };
                this.btc = res.data.btc_rate;
                this.isActive_readdone = true;
            }).catch(error => console.log(error))
        },
        /*
        コメント数を昇順で並べる+ユーザーが選択した通貨のみを表示するための処理
        最初にユーザーが選択している通貨のみをフィルターで判別して表示し、最後に昇順に並ぶ処理をしている
        */
        computed: {
            sortedItemsByAmount: function () {
                var cryptoselected = this.cryptoselected;
                return this.cryptodetail.filter(function(value, index){
                    for(let i in cryptoselected) {
                        if (value.name === cryptoselected[i]) return true;
                    }
                }).sort((a, b) => {
                    return (a.commentnum < b.commentnum) ? 1 : (a.commentnum > b.commentnum) ? -1 : 0;
                })
            }
        },
        /*
        コメント数を時間ごとに取り出す処理
        hourcomment：直近1hのコメント数
        daycomment：直近1日のコメント数
        weekcomment：直近1週間のコメント数
        */
        methods: {
            hourcomment: function(){
                this.isActive_readdone = false;
                axios.get('/api/hourcomment').then((res)=>{
                    for(let i = 0; i < Object.keys(res.data.hourcomment).length; i++){
                    this.$set(this.cryptodetail[i], 'commentnum', res.data.hourcomment[this.cryptodetail[i].name])
                    };
                    this.updatetime = res.data.searchendtime.search_endtime;
                    //選択中のボタンのみをactiveにするための処理（cssでactiveボタンのみ見た目を変える）
                    this.isActive_hour = true;
                    this.isActive_day = false;
                    this.isActive_week = false;
                    this.isActive_readdone = true;
                }).catch(error => console.log(error))
            },
            daycomment: function(){
                this.isActive_readdone = false;
                axios.get('/api/daycomment').then((res)=>{
                    for(let i = 0; i < Object.keys(res.data.daycomment).length; i++){
                        this.$set(this.cryptodetail[i], 'commentnum', res.data.daycomment[this.cryptodetail[i].name])
                    };
                    this.updatetime = res.data.searchendtime.search_endtime;
                    this.isActive_hour = false;
                    this.isActive_day = true;
                    this.isActive_week = false;
                    this.isActive_readdone = true;
                }).catch(error => console.log(error))
            },
            weekcomment: function(){
                this.isActive_readdone = false;
                axios.get('/api/weekcomment').then((res)=>{
                    for(let i = 0; i < Object.keys(res.data.weekcomment).length; i++){
                        this.$set(this.cryptodetail[i], 'commentnum', res.data.weekcomment[this.cryptodetail[i].name])
                    };
                    this.updatetime = res.data.searchendtime.search_endtime;
                    this.isActive_hour = false;
                    this.isActive_day = false;
                    this.isActive_week = true;
                    this.isActive_readdone = true;
                }).catch(error => console.log(error))
            },
        }
    }
</script>
