<template>
    <div class="card h-100 d-flex">
        <!-- Título centrado -->
        <div class="card-body text-center pt-3 px-4 pb-0">
         <h6 class="card-title mb-1 fw-bold">{{ title }}</h6>
        </div>

        <!-- Slot para el gráfico -->
        <div class="flex-grow-1 d-flex align-items-center justify-content-center px-3 pb-3">
            <div class="w-100">
                <slot />
            </div>
        </div>
    </div>
</template>

<script>
    export default {
    name: 'ChartHolderCardSolo',
    props: {
        title: String,
    },
    };
</script>
