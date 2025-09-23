<script setup>
import App from '@/App.vue';
import { Head } from '@inertiajs/vue3';
import Firebase from '@/Components/Firebase.vue';


// Define props for the component
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        default: '', // Default value for title
    },
});
</script>

<template>
    <div id="app" class="g-sidenav-show">
        <Firebase />

        <Head :title="title" />
        <!-- Sidebar -->
        <App :user="user" >
            <!-- Page Content -->
            <slot />
        </App>
    </div>

</template>
