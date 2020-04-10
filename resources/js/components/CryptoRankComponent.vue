<template>
    <div class="l-main__inner">

      <div class="p-wrapper" :class="{'is-visible': isActive_readdone}">
        <div class="p-wrapper_balls-guruguru">
          <span class="c-ball c-ball-1"></span>
          <span class="c-ball c-ball-2"></span>
          <span class="c-ball c-ball-3"></span>
          <span class="c-ball c-ball-4"></span>
          <span class="c-ball c-ball-5"></span>
          <span class="c-ball c-ball-6"></span>
          <span class="c-ball c-ball-7"></span>
          <span class="c-ball c-ball-8"></span>
        </div>
      </div>

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
        props: [],
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
        mounted : function(){
              this.isActive_readdone = false;
              axios.get('/api/hourcomment').then((res)=>{
                  for(let i = 0; i < Object.keys(res.data.hourcomment).length; i++){
                    this.$set(this.cryptodetail[i], 'commentnum', res.data.hourcomment[this.cryptodetail[i].name])
                  };
                  this.updatetime = res.data.searchendtime.search_endtime;
              }).catch(error => console.log(error))
              axios.get('/api/coincheck').then((res)=>{
                  for(let i = 0; i < res.data.current_price.length; i++){
                    this.$set(this.cryptodetail[i], 'currentprice', res.data.current_price[i])
                  };
                  this.btc = res.data.btc_rate;
                  this.isActive_readdone = true;
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
                this.isActive_readdone = false;
                axios.get('/api/hourcomment').then((res)=>{
                    for(let i = 0; i < Object.keys(res.data.hourcomment).length; i++){
                    this.$set(this.cryptodetail[i], 'commentnum', res.data.hourcomment[this.cryptodetail[i].name])
                    };
                this.updatetime = res.data.searchendtime.search_endtime;
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
