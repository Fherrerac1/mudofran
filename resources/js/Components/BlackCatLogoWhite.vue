<template>

    <div v-if="isShowElement" class="px-5 text-center">
        <img src="/images/blackcat.png" class="w-100" alt="">
        <p class="text-black" style="font-size: 11px;">Versión 1.0.0 -
            STABLE</p>
    </div>


</template>

<script>
export default {
    data() {
        return {

        };
    },
    mounted() { },
    methods: {
    }, computed: {
        isShowElement() {
            return window.innerHeight >= 800;
        }
    }
};
</script>