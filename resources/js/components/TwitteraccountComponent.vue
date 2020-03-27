<template>

    <div class="container">
        <div class="message" v-if="message">
            <p class="alert alert-success">{{ message }}</p>
        </div>

        <div>
            <button type="button" v-on:click="auto_follow" :disabled="processing">自動フォロー</button>
        </div>

        <div>
            <button type="button" v-on:click="user_reload" :disabled="processing">ユーザー情報再取得</button>
        </div>
    
        <div v-for="(user,index) in follow_user" :key="user.id">
            <button type="button" v-on:click="user_follow(index)" :disabled="processing">フォロー</button>
            <div>{{user.account_id}}</div>     
            <div>{{user.user_name}}</div>
            <div>{{user.screen_name}}</div>       
            <div>{{user.description}}</div>
            <div>{{user.follows_count}}フォロワー</div>     
            <div>{{user.friends_count}}フォロー</div>     
            <div>{{user.recent_tweet}}</div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['user_nofollowing_account'],
        data: function() {
            return { 
                follow_user: this.user_nofollowing_account,
                message:null,
                processing: false,
            }
        },
        computed: {
        },
        methods: {
            user_follow: function(index){
                this.processing = true;
                axios.post('/api/follow',{action:this.user_nofollowing_account[index].screen_name}).then((res)=>{
                        console.log(res.data.message);
                        this.message = res.data.message;
                        this.user_nofollowing_account.splice(index, 1);
                        setTimeout(() => {this.message = null;}, 2000);
                        this.processing = false;
                }).catch(error => console.log(error))
            },
            user_reload: function(){
                this.processing = true;
                axios.get('/api/reload').then((res)=>{
                        this.follow_user = res.data.user_nofollowing_account;
                        this.user_nofollowing_account = res.data.user_nofollowing_account;
                        this.message = res.data.message;
                        setTimeout(() => {this.message = null;}, 2000);
                        this.processing = false;
                }).catch(error => console.log(error))
            },
             auto_follow: function(){
                 let auto_id = [];
                 for (var i=0, l=this.follow_user.length; i<l ;i++){
                        console.log((this.follow_user[i].screen_name));
                        auto_id[i] = this.follow_user[i].screen_name;
                 };
                 if (auto_id.length > 0){
                     this.processing = true;
                     axios.post('/api/autofollow',{action:auto_id}).then((res)=>{
                            console.log(res.data.message);
                            this.message = res.data.message;
                            this.user_nofollowing_account = [];
                            this.follow_user = [];
                            setTimeout(() => {this.message = null;}, 2000);
                            this.processing = false;
                     }).catch(error => console.log(error))
                 }
            }
        }
    }
</script>
