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
        ? 'nav-link active custom-active'
        : 'nav-link custom-inactive'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

<style scoped>
.nav-link {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem; /* Equivalent to ps-3 pe-4 py-2 */
    border-left: 4px solid transparent; /* Equivalent to border-l-4 border-transparent */
    text-align: start;
    font-size: 1rem; /* Equivalent to text-base */
    font-weight: 500; /* Equivalent to font-medium */
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
}

.custom-active {
    border-left-color: #6610f2; /* Equivalent to border-indigo-400 */
    color: #6610f2; /* Equivalent to text-indigo-700 */
    background-color: #e2e3e5; /* Equivalent to bg-indigo-50 */
}

.custom-active:focus {
    color: #4b4f56; /* Equivalent to focus:text-indigo-800 */
    background-color: #d0d0d0; /* Equivalent to focus:bg-indigo-100 */
    border-left-color: #5a3e8d; /* Equivalent to focus:border-indigo-700 */
}

.custom-inactive {
    color: #6c757d; /* Equivalent to text-gray-600 */
    background-color: #f8f9fa; /* Equivalent to hover:bg-gray-50 */
}

.custom-inactive:hover {
    color: #343a40; /* Equivalent to hover:text-gray-800 */
    background-color: #e9ecef; /* Equivalent to hover:bg-gray-50 */
    border-left-color: #dee2e6; /* Equivalent to hover:border-gray-300 */
}

.custom-inactive:focus {
    color: #343a40; /* Equivalent to focus:text-gray-800 */
    background-color: #e9ecef; /* Equivalent to focus:bg-gray-50 */
    border-left-color: #dee2e6; /* Equivalent to focus:border-gray-300 */
}
</style>
