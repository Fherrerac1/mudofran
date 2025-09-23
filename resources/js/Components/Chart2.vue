<script>
import Chart from 'chart.js/auto';

export default {
    data() {
        return {
            logout: '/logout',
            totalInscripcion: 0,
            totalCuotas: 0,
            totalCompras: 0,
            totalBeneficios: [],
            years: [2023, 2024, 2025],
            year: new Date().getFullYear().toString(),
            datosGrafico: {
                cuotas: '',
                inscripciones: '',
            },
        };
    },
    computed: {},
    props: ['datosGrafico_inscripcion', 'datosGrafico_cuotas'],

    methods: {
        filterValues() {
            this.datosGrafico.cuotas = this.datosGrafico_cuotas[this.year];
            this.datosGrafico.inscripciones = this.datosGrafico_inscripcion[this.year];

            this.updateChart2();
        },
        updateChart2() {
            const ctx2 = document.getElementById("myChart2");

            const newData = {
                cuotas: this.datosGrafico.cuotas,
                inscripciones: this.datosGrafico.inscripciones,
            };

            // Destroy the previous chart instance to prevent memory leaks
            if (this.chart2) {
                this.chart2.destroy();
            }

            const chart2 = new Chart(ctx2, {
                type: 'bar', // Change chart type to 'bar'
                data: {
                    datasets: [
                        {
                            label: 'Cuotas',
                            data: newData.cuotas,
                            backgroundColor: 'rgba(25, 158, 25, 0.75)',
                            borderColor: 'rgba(25, 158, 25, 1)',
                            borderWidth: 1,
                        },
                        {
                            label: 'Inscripciones',
                            data: newData.inscripciones,
                            backgroundColor: 'rgba(0, 123, 255, 0.75)',
                            borderColor: 'blue',
                            borderWidth: 1,
                        },
                    ],
                    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });

            // Store the new chart instance
            this.chart2 = chart2;
        },
    },

    created() {
        let currentDate = new Date();
        this.year = currentDate.getFullYear().toString();
    },

    mounted() {
        this.filterValues();
    },
};
</script>

<template>
    <div class="d-flex align-items-center mx-1">
        <div class="col py-4 rounded">
            <div class="row position-relative">
                <div class="position-absolute">
                    <select @change="filterValues" id="yearSelector" v-model="year">
                        <option v-for="year in Object.keys(datosGrafico_cuotas)" :value="year">{{ year }}</option>
                    </select>
                </div>
                <div class="rounded bg-white p-3 sombras col-12">
                    <canvas id="myChart2" width="900" height="350"></canvas>
                </div>
            </div>
        </div>
    </div>
</template>
