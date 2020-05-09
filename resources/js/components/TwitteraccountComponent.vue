<template>
    <div class="l-main__inner">
       
        <readguruguru-component :isActive="isActive"></readguruguru-component>
        
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
                    <ul>
                        <li>チェックすると自動フォロー機能On</li>
                        <li>30min毎に10ユーザーを自動フォロー</li>
                        <li>全ユーザーをフォローすると機能Off</li>
                    </ul>
                </div>
            </div>
            
            <pagination-component :last_page="last_page" :current_page="current_page" @parentMethod="updateCurrentPage"></pagination-component>
            
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
            <div class="p-tw-list__topscroll c-btn">
                <a class="c-btn__topscroll"　href="#">画面上部に戻る</a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        /*
        画面遷移時にcontrollerからデータを取得してくる
        user_nofollowing_account：フォロー対象のツイッターアカウントデータ
        autofollow_selected：画面遷移時にログインユーザーが自動フォロー機能をonにしているかどうか
        follow_num：画面遷移時にログインユーザーがこのシステムでフォローした数を取得
        */
        props: ['user_nofollowing_account','autofollow_selected','follow_num'],
        /*
        follow_user：フォロー対象のユーザーデータ
        message：ユーザー画面に通知したいメッセージを入れる
        processing：trueでボタンの非活性化（ユーザーが押しても無効化する）
        isActive：falseで読み込み中のぐるぐるマークを表示
        last_page：ページネーションの最後のページ数を取得
        current_page：現在のページ数（初期値は1）
        total_user:DBから取得したユーザーの総数
        auto_selected：ログイン中のユーザーの自動フォロー機能をOn/Offの判定
        user_follow_num：ログイン中のユーザーがこのシステムでフォローした数
        */
        data: function() {
            return {
                follow_user: this.user_nofollowing_account.data,
                message: null,
                processing: false,
                isActive: true,
                last_page: this.user_nofollowing_account.last_page,
                current_page: 1,
                total_user: this.user_nofollowing_account.total,
                auto_selected: this.autofollow_selected,
                user_follow_num: this.follow_num,
            }
        },
        /*
        active_usernum：現在表示中のユーザーが何件中何件目のユーザーなのかを表示（1ページにつき15件のユーザーが格納されている）
        */
        computed: {
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
        /*
        updateCurrentPage：ユーザーが遷移したいページのデータを取得するための処理(PaginationComponentから実行)
        user_follow：任意のユーザーをフォローする処理
        auto_follow：自動フォロー機能のOn/Offを切り替えるための処理
        */
        methods: {
            updateCurrentPage (page) {
                this.current_page = page;
                const url = 'api/usershow/?page='+ this.current_page;
                axios.get(url).then((response) => {
                    this.follow_user = response.data.user_nofollowing_account.data;
                }).catch(error => console.log(error))
            },
            user_follow: function(index){
                //読み込み中のぐるぐるとボタンの非活性化を実施
                this.isActive = false;
                this.processing = true;
                //ログイン中のユーザーがフォローしたいアカウントのユーザーデータを送る
                axios.post('/api/follow',{action:this.follow_user[index].screen_name}).then((res)=>{
                    console.log(res.data.message);
                    this.message = res.data.message;
                    //ログイン中のユーザーがこのシステムでフォローした数を更新
                    this.user_follow_num = res.data.follow_num;
                    //処理完了後に対象アカウントを画面から削除する
                    this.follow_user.splice(index, 1);
                    this.isActive = true;
                    //処理完了後は画面に3秒間メッセージを通知する
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
