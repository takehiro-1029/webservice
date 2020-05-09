<template>
    <ul class="p-tw-list__pagination">
        <div class="p-tw-list__pagination-div-prev">
            <li class="p-tw-list__pagination__link" v-if="hasPrev">
                <a href="#" v-on:click.prevent="move(first_page)">&laquo;</a>
            </li>
            <li class="p-tw-list__pagination__link" v-if="hasPrev">
                <a href="#" v-on:click.prevent="move(current_page-1)">&lt;</a>
            </li>
        </div>
        <div class="p-tw-list__pagination-div-main">
            <li class="p-tw-list__pagination__link" :class="getPageClass(page)" v-for="page in pages">
                <a href="#" v-text="page" v-on:click.prevent="move(page)"></a>
            </li>
        </div>
        <div class="p-tw-list__pagination-div-next">
            <li class="p-tw-list__pagination__link" v-if="hasNext">
                <a href="#" v-on:click.prevent="move(current_page+1)">&gt;</a>
            </li>
            <li class="p-tw-list__pagination__link" v-if="hasNext">
                <a href="#" v-on:click.prevent="move(last_page)">&raquo;</a>
            </li>
        </div>
    </ul>
</template>

<script>
    export default {
         /*
         (親から取得)
        last_page：ページネーションの最後のページ数を取得
        current_page：現在のページ数（初期値は1）
        */
        props: ['last_page','current_page'],
        /*
        first_page：ページネーションの初めのページ
        */
        data: function() {
            return {
                first_page: 1,
            }
        },
        /*
        hasPrev：現在ページが1よりも大きい場合、ページネーションの1つ前へボタンと1ページ目へ戻るリンクを画面に表示
        hasNext：現在ページが最終ページよりも小さい場合、ページネーションの1つ次へボタンと最終ページへ進むリンクを画面に表示
        pages：ページネーション遷移で表示するページ数を場合分けで取得して表示
        */
        computed: {
            hasPrev() {
                return (this.current_page > 1);
            },
            hasNext() {
                return (this.current_page < this.last_page);
            },
            pages() {
                let pages = [];
                //現在のページが4よりも大きく最終ページ-3より小さい場合は現在のページ前後4ページを表示する
                if(4 < this.current_page && this.current_page < this.last_page-3 ) {
                    for(let i = this.current_page-4 ; i <= this.current_page+4 ; i++) {
                        pages.push(i);
                    }
                }
                //現在のページが4よりも小さい場合は1-9ページ目を表示する
                if(4 >= this.current_page) {
                    for(let i = this.first_page ; i <= this.first_page+8 ; i++) {
                        pages.push(i);
                    }
                }
                //現在のページが最終ページ-3より大きい場合は最終ページ-8から最終ページを表示する
                if(this.last_page-3 <= this.current_page) {
                    for(let i = this.last_page-8 ; i <= this.last_page ; i++) {
                        pages.push(i);
                    }
                }
                return pages;
            },
        },
        /*  
        move(page)：実際にユーザーがページ遷移するための処理
        getPageClass(page)：現在表示中のページにcssのクラスを追加して見た目を変えるための処理
        */
        methods: {
            move(page)  {
                if(!(this.current_page === page)) {
                    this.$emit('parentMethod', page);
                }
            },
            getPageClass(page) {
                let classes = [];
                if(this.current_page === page) {
                    classes.push('is-active');
                }
                return classes;
            },
        }
    }
</script>