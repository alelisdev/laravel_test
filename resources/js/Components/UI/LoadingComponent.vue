<template>
    <div v-if="isLoading" class="is-loading-component w-full h-[calc(200vh-100px)] z-[999999]">
        <div class="text-center flex-col">
            <div role="status" class="absolute grid -translate-x-2/2 -translate-y-2/2 top-4/4 left-2/2">
                <div class="text-center mx-auto mb-5">
                <img src="https://cdn271.picsart.com/125815c2-5fdc-478c-8508-5594d49714cc/451830222036201.jpg?to=crop&type=webp&r=1456x1352&q=85" width="1000" loading="eager" alt="" class="ico-brand-type-footer">
                </div>
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['isLoading'],
    components: {},
    data() {
        return {

        }
    },
    setup(props) {


        return {};
    },
    computed: {

    },
    mounted() {

    },
    methods: {

    },
    watch: {

    },
};
</script>

<style>
    .is-loading-component{
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100vw;
        height: 100vh;
    }
</style>
