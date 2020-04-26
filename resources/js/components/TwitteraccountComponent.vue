<template>
    <div class="l-main__inner">

        <div class="p-wrapper" :class="{'is-visible': isActive}">
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

        <div class="p-tw-list">
          <div class="p-tw-list__title">Twitterアカウント</div>

          <div class="p-tw-list__message" v-if="message">
              {{ message }}
          </div>

          <div class="p-tw-list__btn c-btn">
             <input type="checkbox" v-model="auto_selected">
             <button class="c-btn__autofollow" type="button" v-on:click="auto_follow" :disabled="processing">
                 自動フォロー機能設定変更
             </button>
             <div class="p-tw-list__btn__content">
                 <p>チェックをOnにすると自動フォロー機能を使用できます。</p>
                 <p>30min毎に15ユーザーのフォローを自動で行います。</p>
                 <p>全てのユーザーをフォローすると自動でチェックが外れます。</p>
             </div>
          </div>

          <ul class="p-tw-list__pagination">
              <div class="p-tw-list__pagination-div">
                  <li class="p-tw-list__pagination__link" v-if="hasPrev">
                      <a href="#" v-on:click.prevent="move(first_page)">&laquo;</a>
                  </li>
                  <li class="p-tw-list__pagination__link" v-if="hasPrev">
                      <a href="#" v-on:click.prevent="move(current_page-1)">&lt;</a>
                  </li>
              </div>
              <li class="p-tw-list__pagination__link" :class="getPageClass(page)" v-for="page in pages">
                  <a href="#" v-text="page" v-on:click.prevent="move(page)"></a>
              </li>
              <div>
                  <li class="p-tw-list__pagination__link" v-if="hasNext">
                      <a href="#" v-on:click.prevent="move(current_page+1)">&gt;</a>
                  </li>
                  <li class="p-tw-list__pagination__link" v-if="hasNext">
                      <a href="#" v-on:click.prevent="move(last_page)">&raquo;</a>
                  </li>
              </div>
          </ul>
          
          <div class="p-tw-list__box">
              <div class="p-tw-list__box__num">{{total_user}}件中{{active_usernum.start}}～{{active_usernum.end}}件表示</div>
              <div class="p-tw-list__box__num">{{user_follow_num}}件フォロー済</div>
              <div class="p-tw-list__box__detail" v-for="(user,index) in follow_user" :key="user.id">
                  <div class="p-tw-list__box__detail__btn c-btn">
                    <button class="c-btn__userfollow" type="button" v-on:click="user_follow(index)" :disabled="processing">フォロー</button>
                  </div>
                  <div class="p-tw-list__box__detail__name">{{user.user_name}}</div>
                  <div class="p-tw-list__box__detail__sname">@{{user.screen_name}}</div>
                  <div class="p-tw-list__box__detail__desc">{{user.description}}</div>
                  <div class="p-tw-list__box__detail__count">
                    <p class="p-tw-list__box__detail__count-followers">{{user.follows_count}}フォロワー</p>
                    <p class="p-tw-list__box__detail__count-friends">{{user.friends_count}}フォロー</p>
                  </div>
                  <div class="p-tw-list__box__detail__text">
                    <p>最新ツイート</p>
                    {{user.recent_tweet}}
                  </div>
              </div>
          </div>
          
          <div class="p-tw-list__btn c-btn">
              <a class="c-btn__topscroll"　href="#">画面上部に戻る</a>
          </div>

        </div>
    </div>
</template>

<script>
    export default {
        props: ['user_nofollowing_account','autofollow_selected','follow_num'],
        data: function() {
            return {
                follow_user: this.user_nofollowing_account.data,
                message: null,
                processing: false,
                isActive: true,
                last_page: this.user_nofollowing_account.last_page,
                current_page: 1,
                total_user: this.user_nofollowing_account.total,
                first_page: 1,
                auto_selected: this.autofollow_selected,
                user_follow_num: this.follow_num,
            }
        },
        computed: {
            hasPrev() {
                return (this.current_page > 1);
            },
            hasNext() {
                return (this.current_page < this.last_page);
            },
            pages() {
                let pages = [];
                if(4 < this.current_page && this.current_page < this.last_page-3 ) {
                    for(let i = this.current_page-4 ; i <= this.current_page+4 ; i++) {
                        pages.push(i);
                    }
                }
                if(4 >= this.current_page) {
                    for(let i = this.first_page ; i <= this.first_page+8 ; i++) {
                        pages.push(i);
                    }
                }
                if(this.last_page-3 <= this.current_page) {
                    for(let i = this.last_page-8 ; i <= this.last_page ; i++) {
                        pages.push(i);
                    }
                }
                return pages;
            },
            active_usernum () {
                let active_usernum = {};
                if (this.current_page !== this.last_page) {
                    active_usernum.start = this.current_page * 15 -14;
                    active_usernum.end = this.current_page * 15;            
                }else if (this.current_page === this.last_page){
                    active_usernum.start = this.current_page * 15 -14;
                    active_usernum.end = this.total_user;
                }
                return active_usernum;
            }
        },
        methods: {
            getItems() {
                const url = 'api/usershow/?page='+ this.current_page;
                axios.get(url).then((response) => {
                    this.follow_user = response.data.user_nofollowing_account.data;
                    this.current_page = response.data.user_nofollowing_account.current_page;
                }).catch(error => console.log(error))
            },
            move(page)  {
                if(!(this.current_page === page)) {
                    this.current_page = page;
                    this.getItems();
                }
            },
            getPageClass(page) {
                let classes = [];
                if(this.current_page === page) {
                    classes.push('is-active');
                }
                return classes;
            },
            user_follow: function(index){
                this.isActive = false;
                this.processing = true;
                axios.post('/api/follow',{action:this.follow_user[index].screen_name}).then((res)=>{
                    console.log(res.data.message);
                    this.message = res.data.message;
                    this.user_follow_num = res.data.follow_num;
                    this.follow_user.splice(index, 1);
                    this.isActive = true;
                    setTimeout(() => {this.message = null;}, 3000);
                    this.processing = false;
                }).catch(error => console.log(error))
            },
            auto_follow: function(){
                axios.post('/api/autofollow',{action:this.auto_selected}).then((res)=>{
                    this.message = res.data.message;
                    setTimeout(() => {this.message = null;}, 3000);
                }).catch(error => console.log(error))
            }
        }
    }
</script>
