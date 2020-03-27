<template>
    <div class="container">
        <div class="p-rank-box__header">
            <div class="p-rank-box__tit">仮想通貨トレンドランキング</div> 
            <div class="p-rank-box__update">更新日時:{{updatetime}}</div>
            <div class="p-rank-box__update">ビットコイン最高価格:{{btc.high}}円</div>
            <div class="p-rank-box__update">ビットコイン最低価格:{{btc.low}}円</div>
        </div>

        <div class="p-rank-box__buttons">
            <ul>
            <li class="p-rank-box__buttons-btn active" @click="hourcomment">過去1時間</li> 
            <li class="p-rank-box__buttons-btn" @click="daycomment">過去1日</li> 
            <li class="p-rank-box__buttons-btn" @click="weekcomment">過去1週間</li>
            </ul>
        </div>

        <div>
            <label class="p-rank-box__select-chk" v-for="value in cryptocheck">
                <input type="checkbox" :value="value" v-model="cryptoselected"> 
                <span>{{value}}</span>
            </label>
        </div>

        <div class="p-rank-box__table">
            <table>
                <thead>
                    <tr>
                        <th class="p-rank-box__table-no">No.</th> 
                        <th class="p-rank-box__table-name">銘柄</th> 
                        <th class="p-rank-box__table-tweets">ツイート数</th> 
                        <th class="p-rank-box__table-price">現在価格（円）</th> 
                    </tr>
                </thead> 
                <tbody>
                    <tr v-for="(crypto, index) in sortedItemsByAmount">
                        <td class="p-rank-box__table-no">{{index+1}}</td> 
                        <td class="p-rank-box__table-name"><a :href="'https://twitter.com/search?q=%23' + crypto.name" target="_blank">{{crypto.name}}</a></td> 
                        <td class="p-rank-box__table-tweets">{{crypto.commentnum}}</td> 
                        <td class="p-rank-box__table-price">{{crypto.currentprice}}</td> 
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        props: [],
        data: function() {
            return { 
                btc : [],
                updatetime : '',
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
        created : function(){
            axios.get('/api/coincheck').then((res)=>{
                for(let i = 0; i < res.data.current_price.length; i++){
                    this.$set(this.cryptodetail[i], 'currentprice', res.data.current_price[i])
                };
                this.btc = res.data.btc_rate;
            }).catch(error => console.log(error))
            axios.get('/api/weekcomment').then((res)=>{
                for(let i = 0; i < Object.keys(res.data.weekcomment).length; i++){
                    this.$set(this.cryptodetail[i], 'commentnum', res.data.weekcomment[this.cryptodetail[i].name])
                };
                this.updatetime = res.data.searchendtime.search_endtime;
            }).catch(error => console.log(error))
        },
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
        methods: {
            hourcomment: function(){
                axios.get('/api/hourcomment').then((res)=>{
                    for(let i = 0; i < Object.keys(res.data.hourcomment).length; i++){
                    this.$set(this.cryptodetail[i], 'commentnum', res.data.hourcomment[this.cryptodetail[i].name])
                    };
                this.updatetime = res.data.searchendtime.search_endtime;
                }).catch(error => console.log(error))
            },
            daycomment: function(){
                axios.get('/api/daycomment').then((res)=>{
                    for(let i = 0; i < Object.keys(res.data.daycomment).length; i++){
                    this.$set(this.cryptodetail[i], 'commentnum', res.data.daycomment[this.cryptodetail[i].name])
                    };
                this.updatetime = res.data.searchendtime.search_endtime;
                }).catch(error => console.log(error))
            },
            weekcomment: function(){
                axios.get('/api/weekcomment').then((res)=>{
                    for(let i = 0; i < Object.keys(res.data.weekcomment).length; i++){
                    this.$set(this.cryptodetail[i], 'commentnum', res.data.weekcomment[this.cryptodetail[i].name])
                    };
                this.updatetime = res.data.searchendtime.search_endtime;
                }).catch(error => console.log(error))
            },
        }
    }
</script>
         