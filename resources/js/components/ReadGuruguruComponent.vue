<template>
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
</template>

<script>
    export default {
        props: ['isActive'],
    }
</script>