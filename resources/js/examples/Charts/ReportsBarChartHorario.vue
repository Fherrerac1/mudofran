<template>
    <div class="chart w-100 h-100 position-relative">
        <canvas :id="id" class="chart-canvas w-100" :height="height"></canvas>
    </div>
</template>

<script>
import Chart from "chart.js/auto";

export default {
    name: "ReportsBarChart",
    props: {
        id: { type: String, default: "bar-chart" },
        height: { type: [Number, String], default: 280 },
        chart: {
        type: Object,
        required: true,
        labels: Array,
        datasets: {
            type: Object,
            label: String,
            data: Array,
            pauseData: Array,
        },
        },
    },
    mounted() {
        const ctx = document.getElementById(this.id).getContext("2d");
        const chartStatus = Chart.getChart(this.id);
        if (chartStatus) chartStatus.destroy();

        const pauseData = this.chart?.datasets?.pauseData ?? [];
        const workData = this.chart?.datasets?.data ?? [];

        const workBorderRadius = workData.map((_, i) =>
        pauseData[i] > 0
            ? 0
            : { topRight: 8, bottomRight: 8, topLeft: 0, bottomLeft: 0 }
        );

        const pauseBorderRadius = pauseData.map((v) =>
        v > 0
            ? { topRight: 8, bottomRight: 8, topLeft: 0, bottomLeft: 0 }
            : 0
        );

        new Chart(ctx, {
        type: "bar",
        data: {
            labels: this.chart.labels ?? [],
            datasets: [
            {
                label: "Trabajo",
                backgroundColor: "#76a9ff",
                data: workData,
                stack: "total",
                borderRadius: workBorderRadius,
                barThickness: 16,
            },
            {
                label: "Pausa",
                backgroundColor: "#f7b731",
                data: pauseData,
                stack: "total",
                borderRadius: pauseBorderRadius,
                barThickness: 16,
            },
            ],
        },
        options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 600,
                easing: 'easeOutQuart',
            },
            plugins: {
            legend: {
                display: true,
                position: "bottom",
                labels: {
                    font: { size: 15},
                    color: "#444",
                    usePointStyle: true,
                    pointStyle: "circle",
                    padding: 20,
                },
            },
            tooltip: {
                backgroundColor: "#fff",
                borderColor: "#ccc",
                borderWidth: 1,
                titleColor: "#111",
                bodyColor: "#333",
                padding: 12,
                cornerRadius: 6,
                boxPadding: 8,
                bodyFont: {
                    weight: "500",
                },
                callbacks: {
                label(ctx) {
                    const val = ctx.raw;
                    const h = Math.floor(val);
                    const m = Math.round((val - h) * 60);
                    return ` ${ctx.dataset.label}: ${h}h ${m}m`;
                },
                },
            },
            },
            scales: {
            x: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                color: "#666",
                font: { size: 12, family: "Poppins" },
                callback: (val) => `${val}h`,
                padding: 5,
                },
                grid: {
                color: "#e9e9e9",
                borderDash: [3, 3],
                drawBorder: false,
                },
            },
            y: {
                stacked: true,
                ticks: {
                align: "start",
                padding: 10,
                color: "#7b809a",
                font: { size: 14, weight: "600", family: "Poppins" },
                },
                    grid: {
                    display: false,
                },
            },
            },
        },
        });
    },
};
</script>

<style scoped>
.chart-canvas {
    min-height: 230px;
}
</style>
