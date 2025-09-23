<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="btn btn-light text-gray-700 rounded shadow-sm text-uppercase fw-semibold" :class="{
        'btn-sm': size === 'small',
        'btn-lg': size === 'large',
        'disabled': isDisabled
    }" :disabled="isDisabled">
        <slot />
    </button>
</template>

<style scoped>
.btn-light {
    background-color: #ffffff;
    /* Equivalent to Tailwind's bg-white */
    border-color: #d6d6d6;
    /* Equivalent to Tailwind's border-gray-300 */
    color: #6c757d;
    /* Equivalent to Tailwind's text-gray-700 */
    transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
    /* Transition for hover and focus states */
}

.btn-light:hover {
    background-color: #f8f9fa;
    /* Equivalent to Tailwind's hover:bg-gray-50 */
}

.btn-light:focus {
    box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.5);
    /* Equivalent to Tailwind's focus:ring-indigo-500 */
    outline: none;
    /* Remove default focus outline */
}

.btn:disabled {
    opacity: 0.65;
    /* Equivalent to Tailwind's disabled:opacity-25 */
    cursor: not-allowed;
    /* Ensure disabled cursor */
}
</style>
