<template>
    <div class="chart">
      <canvas :id="id" class="chart-canvas" :height="height"></canvas>
    </div>
  </template>

  <script>
  import Chart from "chart.js/auto";

  export default {
    name: "ReportsBarLinesGeneralMobile",
    props: {
      id: {
        type: String,
        default: () => `chart-mobile-${Math.random().toString(36).substr(2, 9)}`,
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
        default: "#000",
      },
      type: {
        type: String,
        default: "line", // ✅ Ahora correctamente por defecto es 'line'
      },
    },
    mounted() {
      this.renderChart();
    },
    watch: {
      chart: {
        deep: true,
        handler() {
          this.renderChart();
        },
      },
      type() {
        this.renderChart();
      },
    },
    methods: {
      renderChart() {
        const ctx = document.getElementById(this.id)?.getContext("2d");
        if (!ctx) return;

        const existingChart = Chart.getChart(this.id);
        if (existingChart) existingChart.destroy();

        const datasets = Array.isArray(this.chart.datasets)
          ? this.chart.datasets.map((dataset, index) => ({
              label: dataset.label,
              data: dataset.data,
              realValues: dataset.realValues,
              tension: this.type === "line" ? 0.3 : 0,
              fill: this.type === "bar",
              borderWidth: this.type === "line" ? 2 : 1,
              pointRadius: this.type === "line" ? 2 : 0,
              borderColor: dataset.borderColor || `hsl(${index * 50}, 80%, 60%)`,
              backgroundColor:
                dataset.backgroundColor ||
                (this.type === "bar" ? `hsl(${index * 50}, 80%, 60%)` : "transparent"),
              maxBarThickness: this.type === "bar" ? 20 : undefined,
            }))
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
            indexAxis: this.type === "bar" ? "y" : "x",
            interaction: {
              intersect: true,
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
                  label: (context) => {
                    const realValues = context.dataset.realValues || [];
                    const real = realValues[context.dataIndex] || { total: 0 };
                    return this.formatCurrency(real.total);
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
                  callback: (value) => {

                    if (this.type === 'bar' && this.chart.labels) {
                      let label = this.chart.labels[value] ?? value;
                      if (!label) return '';
                      label = label.toString().toUpperCase();
                      if (label.length > 20) {
                        return label.slice(0, 20) + '...';
                      }
                      return label;
                    } else {
                      if (typeof value === "number") {
                        return value.toLocaleString("es-ES");
                      }
                      return value;
                    }
                  },
                },
              },
            },
          },
        };

        new Chart(ctx, config);
      },
      formatCurrency(amount) {
        return new Intl.NumberFormat("es-ES", {
          style: "currency",
          currency: "EUR",
          minimumFractionDigits: 2,
        }).format(amount || 0);
      },
    },
  };
  </script>

  <style scoped>
  .chart {
    position: relative;
    height: 100%;
  }
  </style>
