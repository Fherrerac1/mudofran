<template>
    <div class="chart">
        <canvas :id="id" class="chart-canvas" :height="height"></canvas>
    </div>
</template>

<script>
    import Chart from "chart.js/auto";

    export default {
        name: "ReportsLineChart",
        props: {
            id: {
                type: String,
                default: "line-chart",
            },
            height: {
                type: [Number, String],
                default: "280",
            },
            chart: {
                type: Object,
                required: true,
            },
            textColor: {
                type: String,
                default: "#f8f9fa",
            },
            type: {
                type: String,
                default: "line",
            },
        },
        mounted() {
            const ctx = document.getElementById(this.id).getContext("2d");

            const existingChart = Chart.getChart(this.id);
            if (existingChart) existingChart.destroy();

            const datasets = Array.isArray(this.chart.datasets)
                ? this.chart.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.data,
                    realValues: dataset.realValues,
                    tension: 0.3,
                    fill: this.type === "bar",
                    borderWidth: 2,
                    pointRadius: this.type === "line" ? 2 : 0,
                    borderColor: dataset.borderColor || `hsl(${index * 50}, 80%, 60%)`,
                    backgroundColor:
                    dataset.backgroundColor ||
                    (this.type === "bar" ? `hsl(${index * 50}, 80%, 60%)` : "transparent"),
                    maxBarThickness: this.type === "bar" ? 20 : undefined,
                }))
            : [
                {
                    label: this.chart.datasets.label,
                    data: this.chart.datasets.data,
                    tension: 0.3,
                    fill: this.type === "bar",
                    borderWidth: 4,
                    pointRadius: this.type === "line" ? 5 : 0,
                    pointBackgroundColor: "rgba(255, 255, 255, .8)",
                    pointBorderColor: "transparent",
                    borderColor: this.chart.datasets.borderColor || "rgba(255, 255, 255, .8)",
                    backgroundColor: this.chart.datasets.backgroundColor || "transparent",
                    maxBarThickness: 6,
                },
            ];

            const config = {
                type: this.type,
                data: {
                    labels: this.chart.labels,
                    datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: this.type === "bar" ? "y" : "x",
                    interaction: {
                        intersect: true, // Precisión hover
                        mode: "nearest",
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: this.type === "bar" ? "bottom" : "right",
                            align: "center",
                            labels: {
                                usePointStyle: true,
                                pointStyle: "rect",
                                color: this.textColor,
                                boxWidth: 10,
                                padding: 20,
                                font: {
                                size: 9,
                                weight: "normal",
                                },
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                const realValues = context.dataset.realValues || [];
                                const real = realValues[context.dataIndex] || { total: 0, count: 0 };
                                const formatted = real.total.toLocaleString('es-ES', {
                                    style: 'currency',
                                    currency: 'EUR',
                                    minimumFractionDigits: 2,
                                });
                                return `${formatted}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                drawTicks: false,
                                borderDash: [5, 5],
                                color: "rgba(200, 200, 200, 0.3)",
                            },
                            ticks: {
                                color: this.textColor,
                                padding: this.type === "bar" ? 0 : 5,
                                font: {
                                size: 10,
                                weight: 300,
                                style: "normal",
                                lineHeight: 2,
                                },
                            },
                        },
                        y: {
                            offset: true,
                            grid: {
                                drawBorder: false,
                                display: false,
                                drawOnChartArea: false,
                                drawTicks: false,
                            },
                            ticks: {
                                align: "start",
                                crossAlign: "start",
                                padding: 5,
                                color: this.textColor,
                                font: {
                                    size: 11,
                                    weight: 300,
                                    lineHeight: 1.2,
                                },
                                callback: function(value, index, ticks) {
                                    let label = this.getLabelForValue(value);

                                    if (!label) return '';

                                    label = label.toUpperCase();

                                    if (label.length > 25) {
                                        return label.slice(0, 25) + '...';
                                    }

                                    return label;
                                },
                                title: {
                                display: false,
                                },
                            },
                        },
                    },
                },
            };

            new Chart(ctx, config);
        },
    };
</script>

<style scoped>
.chart {
    position: relative;
    height: 100%;
}
</style>
