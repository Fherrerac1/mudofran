<template>
    <div class="chart pt-1" style="min-height: 180px;">
        <canvas :id="id" class="chart-canvas w-100" :height="height"></canvas>
    </div>
</template>

<script>
    import Chart from "chart.js/auto";

    export default {
    name: "ReportsLineChartMobile",
    props: {
        id: { type: String, default: "line-chart-mobile" },
        height: { type: [Number, String], default: "180" },
        chart: { type: Object, required: true },
        textColor: { type: String, default: "#000" },
        type: { type: String, default: "line" },
    },
    data() {
        return {
        chartInstance: null,
        };
    },
    mounted() {
        this.renderChart();
    },
    watch: {
        chart: {
        handler() {
            this.reRenderChart();
        },
        deep: true,
        },
        type() {
        this.reRenderChart();
        },
        textColor() {
        this.reRenderChart();
        },
    },
    methods: {
        renderChart() {
        const ctx = document.getElementById(this.id).getContext("2d");

        const datasets = Array.isArray(this.chart.datasets)
            ? this.chart.datasets.map((dataset, index) => {
                const base = {
                label: dataset.label,
                data: dataset.data,
                realValues: dataset.realValues,
                };
                return this.type === "doughnut"
                ? {
                    ...base,
                    backgroundColor: dataset.backgroundColor || [
                        "#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", "#858796",
                    ],
                    borderColor: "#fff",
                    borderWidth: 2,
                    }
                : {
                    ...base,
                    tension: 0.3,
                    fill: false,
                    borderWidth: 2,
                    pointRadius: 1,
                    borderColor: dataset.borderColor || `hsl(${index * 50}, 80%, 60%)`,
                    backgroundColor: dataset.backgroundColor || "transparent",
                    };
            })
            : [];

        const config = {
            type: this.type,
            data: {
            labels: this.chart.labels,
            datasets,
            },
            options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                display: true,
                position: "bottom",
                labels: {
                    color: this.textColor,
                    font: { size: 10 },
                },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const realValues = context.dataset.realValues || null;
                            const value = context.raw || 0;

                            // Si tiene realValues (para DOUGHNUT y gráficos especiales)
                            if (realValues) {
                                const real = realValues[context.dataIndex] || { total: 0, count: 0 };

                                if (real.count > 0) {
                                    const formatted = real.total.toLocaleString('es-ES', {
                                        style: 'currency',
                                        currency: 'EUR',
                                        minimumFractionDigits: 2,
                                    });
                                    return `${formatted} (${real.count})`;
                                }

                                return `No hay datos`;
                            }

                            // Si NO tiene realValues (gráfica general de líneas)
                            const formattedValue = value.toLocaleString('es-ES', {
                                style: 'currency',
                                currency: 'EUR',
                                minimumFractionDigits: 2,
                            });

                            return formattedValue;
                        }
                    },
                },
            },
            ...(this.type === "doughnut"
                ? {}
                : {
                    interaction: {
                    intersect: false,
                    mode: "index",
                    },
                    scales: {
                    y: {
                        ticks: { color: this.textColor, font: { size: 12 } },
                        grid: { color: "rgba(200,200,200,0.2)" },
                    },
                    x: {
                        ticks: { color: this.textColor, font: { size: 12 } },
                        grid: { display: false },
                    },
                    },
                }),
            },
        };

        this.chartInstance = new Chart(ctx, config);
        },
        reRenderChart() {
        if (this.chartInstance) {
            this.chartInstance.destroy();
        }
        this.renderChart();
        },
    },
    beforeUnmount() {
        if (this.chartInstance) {
        this.chartInstance.destroy();
        }
    },
    };
</script>
