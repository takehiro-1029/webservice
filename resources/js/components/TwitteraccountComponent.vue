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
              <button class="c-btn__autofollow" type="button" v-on:click="auto_follow" :disabled="processing">自動フォロー</button>
          </div>

          <div class="p-tw-list__box">
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
              <button class="c-btn__autofollow" type="button" v-on:click="user_reload" :disabled="processing">ユーザー情報再取得</button>
          </div>

        </div>
    </div>
</template>

<script>
    export default {
        props: ['user_nofollowing_account'],
        data: function() {
            return {
                follow_user: this.user_nofollowing_account,
                message: null,
                processing: false,
                isActive: true,
            }
        },
        computed: {
        },
        methods: {
            user_follow: function(index){
                this.isActive = false;
                this.processing = true;
                axios.post('/api/follow',{action:this.user_nofollowing_account[index].screen_name}).then((res)=>{
                        console.log(res.data.message);
                        this.message = res.data.message;
                        this.user_nofollowing_account.splice(index, 1);
                        this.isActive = true;
                        setTimeout(() => {this.message = null;}, 3000);
                        this.processing = false;
                }).catch(error => console.log(error))
            },
            user_reload: function(){
                this.isActive = false;
                this.processing = true;
                axios.get('/api/reload').then((res)=>{
                        this.follow_user = res.data.user_nofollowing_account;
                        this.user_nofollowing_account = res.data.user_nofollowing_account;
                        this.message = res.data.message;
                        this.isActive = true;
                        setTimeout(() => {this.message = null;}, 3000);
                        this.processing = false;
                }).catch(error => console.log(error))
            },
             auto_follow: function(){
                 let auto_id = [];
                 try{
                   for (var i=0, l=this.follow_user.length; i<l ;i++){
                     // console.log((this.follow_user[i].screen_name));
                     auto_id[i] = this.follow_user[i].screen_name;
                   };
                 }catch(e){
                    // console.log(e);
                 }
                 if (auto_id.length > 0){
                     this.isActive = false;
                     this.processing = true;
                     axios.post('/api/autofollow',{action:auto_id}).then((res)=>{
                        console.log(res.data.message);
                        this.message = res.data.message;
                        this.user_nofollowing_account = [];
                        this.follow_user = [];
                        this.isActive = true;
                        setTimeout(() => {this.message = null;}, 3000);
                        this.processing = false;
                     }).catch(error => console.log(error))
                 }
                 else{
                   this.message = "フォローできるユーザーがいません。";
                   setTimeout(() => {this.message = null;}, 3000);
                 }
            }
        }
    }
</script>
