<template>

    <div v-if="isShowElement">
        <img src="/images/whitecat.png" class="w-100" alt="">
        <p class="text-white" style="font-size: 11px;">Versión 1.0.0 -
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