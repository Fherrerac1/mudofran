<template>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white rounded-3 p-4 x-0 ps-0 pt-0 pb-1 mb-0">
            <li
                v-for="(item, index) in items"
                :key="index"
                :class="['breadcrumb-item', { active: index === items.length - 1 }]"
                aria-current="page"
            >
                <template v-if="item.url && index !== items.length - 1">
                    <Link :href="item.url">{{ item.label }}</Link>
                </template>
                <template v-else>
                    {{ item.label }}
                </template>
            </li>
        </ol>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

/**
 * Propiedades:
 * - items: Array -> Elementos del breadcrumb.
 * */
defineProps({
    items: { type: Array, required: true },
})
</script>
