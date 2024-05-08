<template>
    <div class="item-game text-gray-700 w-full h-auto mr-4 cursor-pointer">
        <RouterLink v-if="game.distribution === 'kagaming'" :to="{ name: 'casinoPlayPage', params: { id: game.id, slug: game.game_code }}">
            <img :src="game.cover" alt="" class="w-full">
        </RouterLink>
        <RouterLink v-else :to="{ name: 'casinoPlayPage', params: { id: game.id, slug: game.game_code }}">
            <img :src="`/storage/`+game.cover" alt="" class="w-full ">
        </RouterLink>
        <div class="flex justify-between w-full text-gray-700 dark:text-gray-400 px-3 py-2">
            <div class="flex flex-col justify-start items-start">

            </div>

        </div>
    </div>
</template>


<script>
import { RouterLink } from "vue-router";

export default {
    props: [ 'index', 'game'],
    components: { RouterLink },
    data() {
        return {
            isLoading: false,
            modalGame: null,
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
    created() {

    },
    watch: {

    },
};
</script>

<style scoped>

</style>
