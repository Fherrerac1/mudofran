<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'nav-link active'
        : 'nav-link'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

<style scoped>
.nav-link {
    padding: 0.5rem 1rem; /* Equivalent to Tailwind's px-1 pt-1 */
    font-size: 0.875rem; /* Equivalent to Tailwind's text-sm */
    font-weight: 500; /* Equivalent to Tailwind's font-medium */
    color: #6c757d; /* Equivalent to Tailwind's text-gray-500 */
    border-bottom: 2px solid transparent; /* Equivalent to Tailwind's border-b-2 border-transparent */
    transition: color 0.15s ease-in-out, border-color 0.15s ease-in-out; /* Equivalent to Tailwind's transition */
}

.nav-link.active {
    color: #0d6efd; /* Equivalent to Tailwind's text-gray-900 */
    border-bottom-color: #0d6efd; /* Equivalent to Tailwind's border-indigo-400 */
}

.nav-link:hover {
    color: #0a58ca; /* Equivalent to Tailwind's hover:text-gray-700 */
    border-bottom-color: #0a58ca; /* Equivalent to Tailwind's hover:border-gray-300 */
}
</style>
