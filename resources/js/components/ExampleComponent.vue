<template>
    <div class="container">
        <div v-for="(user,index) in follow_user" :key="user.id">
            <button type="button" v-on:click="user_follow(index)">フォロー</button>
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
                follow_user: this.user_nofollowing_account
            }
        },
        computed: {
        },
        methods: {
            user_follow: function(index){
            axios.post('/api/follow',{action:this.user_nofollowing_account[index].screen_name}).then((res)=>{
                    console.log(res.data.message);
                })
                    .catch(error => console.log(error))
            this.user_nofollowing_account.splice(index, 1);
            }
        }
    }
</script>
