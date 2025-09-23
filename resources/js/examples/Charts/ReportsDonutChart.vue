<template>
    <div class="chart pt-1">
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
        default: "220",
        },
        chart: {
        type: Object,
        required: true,
        },
        textColor: {
        type: String,
        default: "#f8f9fa", // blanco
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

        // ✅ Dataset adaptado por tipo con realValues para tooltip
        const datasets = Array.isArray(this.chart.datasets)
        ? this.chart.datasets.map((dataset, index) => {
            const base = {
                label: dataset.label,
                data: dataset.data,
                realValues: dataset.realValues, // 💡 valores reales para tooltip
            };

            return this.type === "doughnut"
                ? {
                    ...base,
                    backgroundColor:
                    dataset.backgroundColor || [
                        "#4e73df",
                        "#1cc88a",
                        "#36b9cc",
                        "#f6c23e",
                        "#e74a3b",
                        "#858796",
                    ],
                    borderColor: "#fff",
                    borderWidth: 2,
                }
                : {
                    ...base,
                    tension: 0.3,
                    fill: false,
                    borderWidth: 2,
                    pointRadius: 2,
                    borderColor:
                    dataset.borderColor || `hsl(${index * 50}, 80%, 60%)`,
                    backgroundColor: dataset.backgroundColor || "transparent",
                };
            })
        : [
            {
                label: this.chart.datasets.label,
                data: this.chart.datasets.data,
                ...(this.type === "doughnut"
                ? {
                    backgroundColor:
                        this.chart.datasets.backgroundColor || [
                        "#4e73df",
                        "#1cc88a",
                        "#36b9cc",
                        "#f6c23e",
                        "#e74a3b",
                        "#858796",
                        ],
                    borderColor: "#fff",
                    borderWidth: 2,
                    }
                : {
                    tension: 0.3,
                    fill: true,
                    borderWidth: 4,
                    pointRadius: 5,
                    pointBackgroundColor: "rgba(255, 255, 255, .8)",
                    pointBorderColor: "transparent",
                    borderColor:
                        this.chart.datasets.borderColor ||
                        "rgba(255, 255, 255, .8)",
                    backgroundColor:
                        this.chart.datasets.backgroundColor || "transparent",
                    maxBarThickness: 6,
                    }),
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
            plugins: {
            legend: {
                display: true,
                position: "right",
                align: "center",
                labels: {
                color: this.textColor,
                boxWidth: 12,
                padding: 17,
                font: {
                    size: 10,
                    weight: "normal",
                },
                },
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                    const realValues = context.dataset.realValues || [];
                    const real = realValues[context.dataIndex] || { total: 0, count: 0 };
                    // const label = context.label || '';

                    if (real.count > 0) {
                        const formatted = real.total.toLocaleString('es-ES', {
                        style: 'currency',
                        currency: 'EUR',
                        minimumFractionDigits: 2,
                        });
                        return `${formatted} (${real.count})`;
                    }

                    // ❌ Si no hay ni conteo ni total
                    return `No hay datos`;
                    }
                }
            }
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
                    grid: {
                        drawBorder: false,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: false,
                        borderDash: [5, 5],
                        color: "rgba(200, 200, 200, 0.3)",
                    },
                    ticks: {
                        display: true,
                        color: this.textColor,
                        padding: 10,
                        font: {
                        size: 14,
                        weight: 300,
                        family: "Roboto",
                        style: "normal",
                        lineHeight: 2,
                        },
                    },
                    },
                    x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                        borderDash: [5, 5],
                    },
                    ticks: {
                        display: true,
                        color: this.textColor,
                        padding: 10,
                        font: {
                        size: 14,
                        weight: 300,
                        family: "Roboto",
                        style: "normal",
                        lineHeight: 2,
                        },
                    },
                    },
                },
                }),
        },
        };

        new Chart(ctx, config);
    },
    };
</script>
