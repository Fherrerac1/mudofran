<template>
    <AuthenticatedLayout :user="user" :title="'DASHBOARD'">
        <div class="px-2 container-fluid">
            <div class="row g-2 align-items-center flex-wrap">
                <!-- Botón Filtros -->
                <div class="col-auto d-flex align-items-center">
                    <button class="btn btn-outline-secondary d-flex align-items-center gap-1 p-1 border-0 "
                        type="button" @click="isFilterVisible = !isFilterVisible"
                        style="font-size: 0.65rem; text-decoration: none; margin-top: 8px;">
                        <i class="fa-solid fa-filter "></i>
                        <strong class="text-secondary">FILTROS</strong>
                    </button>
                </div>

                <!-- Filtros desplegables -->
                <template v-if="isFilterVisible">
                    <!-- Año -->
                    <div class="col-3 col-lg-2 d-flex align-items-center gap-1 py-0 my-0">
                        <label for="years" class="fw-bold mb-0 small">Año</label>
                        <div class="flex-grow-1">
                            <MaterialSelect id="years" :key="year" :options="yearsArray" v-model="year"
                                placeholder="Año" class="form-select-sm w-100" />
                        </div>
                    </div>
                </template>
            </div>

            <!-- VISTA ESCRITORIO -->
            <div class="row screen-width me-0 flex-grow-1 h-100 overflow-auto">
                <div class="row screen-width me-0 flex-grow-1 h-100 overflow-auto">
                    <div class="d-none d-lg-flex">
                        <!-- KPIS -->
                        <div class="col-3 col-xxl-2 d-flex flex-column h-100">
                            <div v-for="(card, index) in kpis" :key="index" class="mb-2">
                                <mini-statistics-card :title="card.title" :detail="card.detail" :estados="card.estados"
                                    :icon="card.icon" customHeightClosed="85px" />
                            </div>
                        </div>

                        <div class="col-9 col-xxl-10 px-3 d-flex flex-column h-100">
                            <!-- SISTEMA-->
                            <div class="row">
                                <div v-for="(card, index) in sistemaKpis" :key="'sistema-' + index" class=" col-lg-4">
                                    <a :href="card.title.url" class="w-100 h-100 text-decoration-none text-reset">
                                        <mini-statistics-card onlyCard :title="card.title" :icon="card.icon" />
                                    </a>
                                </div>
                            </div>

                            <!-- GRÁFICAS  -->
                            <div class="row d-flex align-items-stretch h-100">
                                <!-- Columna de la gráfica general -->
                                <div class="col-lg-7 d-flex flex-column">
                                    <div class="my-2 h-100">
                                        <chart-holder-card class="flex-fill d-flex flex-column"
                                            :title="graficoGeneralTitulo" :key="year + '-general'" color="white"
                                            :showToggle="true" @toggle-prev="modoGraficoGeneralAnterior"
                                            @toggle-next="alternarModoGraficoGeneral">
                                            <ReportsBarLinesGeneral :key="`${year}-${modoGraficoGeneral}`"
                                                :type="tipoGraficoGeneral" textColor="#000" :chart="graficoGeneral" />
                                        </chart-holder-card>
                                    </div>
                                </div>

                                <!-- Columna del balance doughnut -->
                                <div class="col-lg-5 d-flex flex-column">
                                    <div class="my-2 h-100">
                                        <chart-holder-card class="flex-fill d-flex flex-column"
                                            :title="graficoDonutTitulo" :subtitle="graficoDonutSubtitulo"
                                            :footer="graficoDonutFooter" color="white" :showToggle="true"
                                            @toggle-prev="modoGraficoAnterior" @toggle-next="alternarModoGraficoDonut">
                                            <ReportsDonutChart :id="year + '-balance-doughnut'"
                                                :key="`donut-${modoGraficoActual}-${year}`" :type="'doughnut'"
                                                textColor="#000" :chart="graficosDonut" />
                                        </chart-holder-card>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- MOBILE / TABLET -->
            <div class="d-lg-none w-100">
                <!-- BOTÓN KPIS -->
                <div class="d-flex justify-content-end align-items-stretch gap-2">
                    <button
                        class="btn btn-outline-secondary d-flex align-items-center gap-1 px-2 py-1 border-0 rounded-2 mb-0"
                        type="button" @click="iskpissCollapseOpen = !iskpissCollapseOpen"
                        :aria-expanded="iskpissCollapseOpen.toString()" aria-controls="kpisCollapse"
                        style="min-height: 36px; font-size: 0.75rem;">
                        <i class="fa-solid fa-chevron-down chevron-icon" :class="{ rotated: iskpissCollapseOpen }"
                            style="font-size: 0.75rem;"></i>
                        <span class="fw-semibold">KPIS</span>
                    </button>
                </div>

                <!-- KPIS -->
                <transition name="collapse-smooth">
                    <div v-show="iskpissCollapseOpen" id="kpisCollapse" class="row justify-content-center">
                        <div v-for="(card, index) in kpis" :key="`${index}`"
                            class="col-6 col-sm-4 col-md-4 my-2 d-flex justify-content-center">
                            <mini-statistics-card :title="card.title" :detail="card.detail" :estados="card.estados"
                                :icon="card.icon" customHeightClosed="85px" />
                        </div>
                    </div>
                </transition>

                <!-- BOTÓN SISTEMA -->
                <div class="d-flex justify-content-end align-items-stretch gap-2">
                    <button
                        class="btn btn-outline-secondary d-flex align-items-center gap-1 px-2 py-1 border-0 rounded-2 mb-0"
                        type="button" @click="isUsersCollapseOpen = !isUsersCollapseOpen"
                        :aria-expanded="isUsersCollapseOpen.toString()" aria-controls="usersCollapse"
                        style="min-height: 36px; font-size: 0.75rem;">
                        <i class="fa-solid fa-chevron-down chevron-icon" :class="{ rotated: isUsersCollapseOpen }"
                            style="font-size: 0.75rem;"></i>
                        <span class="fw-semibold">Sistema</span>
                    </button>
                </div>

                <!-- CONTENIDO COLAPSABLE DE SISTEMA-->
                <transition name="collapse-smooth">
                    <div v-show="isUsersCollapseOpen" id="usersCollapse">
                        <!-- SISTEMA-->
                        <div class="row">
                            <div v-for="(card, index) in sistemaKpis" :key="'sistema-' + index"
                                class="col-lg-3 col-md-6 col-sm-6 mt-lg-0">
                                <a :href="card.title.url" class="w-100 h-100 text-decoration-none text-reset">
                                    <mini-statistics-card onlyCard :title="card.title" :icon="card.icon" />
                                </a>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- BOTÓN GRÁFICAS -->
                <div class="d-flex justify-content-end align-items-stretch gap-2">
                    <button
                        class="btn btn-outline-secondary d-flex align-items-center gap-1 px-2 py-1 border-0 rounded-2 mb-0"
                        type="button" @click="isGraficasCollapseOpen = !isGraficasCollapseOpen"
                        :aria-expanded="isGraficasCollapseOpen.toString()" aria-controls="graficasCollapse"
                        style="min-height: 36px; font-size: 0.75rem;">
                        <i class="fa-solid fa-chevron-down chevron-icon" :class="{ rotated: isGraficasCollapseOpen }"
                            style="font-size: 0.75rem;"></i>
                        <span class="fw-semibold">Gráficas</span>
                    </button>
                </div>

                <!-- GRÁFICAS DESPLEGABLE -->
                <transition name="collapse-smooth">
                    <div v-if="isGraficasCollapseOpen" id="graficasCollapse" class="w-100">
                        <div class="row d-flex align-items-stretch h-100">
                            <!-- Gráfica General -->
                            <div class="col-12 d-flex flex-column w-100">
                                <div class="mt-4 mb-3 h-100">
                                    <chart-holder-card class="flex-fill d-flex flex-column"
                                        :title="graficoGeneralTitulo" :key="year + '-general'" color="white"
                                        :showToggle="true" @toggle-prev="modoGraficoGeneralAnterior"
                                        @toggle-next="alternarModoGraficoGeneral">
                                        <ReportsBarLinesGeneralMobile :key="`${year}-${modoGraficoGeneral}`"
                                            :type="tipoGraficoGeneral" textColor="#000" :chart="graficoGeneral" />
                                    </chart-holder-card>
                                </div>
                            </div>

                            <!-- Gráfica Balance / Donut -->
                            <div class="col-12 d-flex flex-column w-100">
                                <div class="mt-4 mb-3 h-100">
                                    <chart-holder-card class="flex-fill d-flex flex-column" :title="graficoDonutTitulo"
                                        :subtitle="graficoDonutSubtitulo" :footer="graficoDonutFooter" color="white"
                                        :showToggle="true" height="300px" @toggle-prev="modoGraficoAnterior"
                                        @toggle-next="alternarModoGraficoDonut">
                                        <ReportsDonutCharttMobile class="d-block d-lg-none"
                                            :id="year + '-balance-doughnut-mobile'"
                                            :key="`donut-mobile-${modoGraficoActual}-${year}`" type="doughnut"
                                            textColor="#000" :chart="graficosDonut" />
                                    </chart-holder-card>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import ChartHolderCard from "@/views/components/ChartHolderCard.vue";
import ReportsBarLinesGeneral from "@/examples/Charts/ReportsBarLinesGeneral.vue";
import ReportsBarLinesGeneralMobile from "@/examples/Charts/ReportsBarLinesGeneralMobile.vue";
import ReportsDonutChart from "@/examples/Charts/ReportsDonutChart.vue";
import ReportsDonutCharttMobile from "@/examples/Charts/ReportsDonutChartMobile.vue";
import MiniStatisticsCard from "@/views/components/MiniStatisticsCard.vue";
import ProjectCard from "@/views/components/ProjectCard.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MaterialSelect from "@/Components/MaterialSelect.vue";
import { array } from "yup";
import MaterialTableDashboard from "@/Components/MaterialTableDashboard.vue";

export default {
    name: "dashboard_admin",

    data() {
        return {
            year: new Date().getFullYear(),
            isFilterVisible: false,
            iskpissCollapseOpen: true,
            isUsersCollapseOpen: true,
            isGraficasCollapseOpen: true,
            modoGraficoActual: 'balance',
            modoGraficoGeneral: 'evolucion',
        };
    },
    props: {
        user: Object,
        chartData: {
            type: Object,
            default: () => ({
                facturas: { data: [], percentageDifference: "" },
                presupuestos: { data: [], percentageDifference: "" },
                balance: { data: [], percentageDifference: "" },
            }),
        },
        presupuestos: Array,
        facturas: Array,
        facturas_estados: Array,
        clientes: Array,
        activities: Array,
        users: Array,
        balance: array,
    },
    computed: {
        //----------------------------------------------------------
        //-------------------DATOS KPIs-----------------------------
        //----------------------------------------------------------
        kpis() {
            return [
                {
                    title: { text: 'Presupuestos', value: this.formatCurrency(this.totalPresupuestos), url: this.route('presupuestos.index') },
                    detail: String(this.filteredPresupuestos.length),
                    estados: this.estados(this.presupuestos, 'fechaInicio'),
                    icon: { name: 'request_quote', color: 'text-white', background: 'presupuesto-icon' },
                },
                {
                    title: { text: 'Facturas', value: this.formatCurrency(this.totalFacturas), url: this.route('facturas.index') },
                    detail: String(this.filteredFacturas.length),
                    estados: this.estados(this.facturas_estados, 'fechaInicio'),
                    icon: { name: 'real_estate_agent', color: 'text-white', background: 'facturas-icon' },
                },
            ]
        },
        sistemaKpis() {
            return [
                {
                    title: {
                        text: 'Clientes',
                        value: [
                            { label: 'Empresas', count: this.totalEmpresas },
                            { label: 'Personales', count: this.totalPersonas }
                        ],
                        url: this.route('clientes.index'),
                    },
                    icon: {
                        name: 'people',
                        color: 'text-white',
                        background: 'usuarios-icon',
                    },
                },
                {
                    title: {
                        text: 'Usuarios',
                        value: this.filteredUsers.length,
                        url: this.route('user.index'),
                    },
                    icon: {
                        name: 'people',
                        color: 'text-white',
                        background: 'usuarios-icon',
                    },
                },
                {
                    title: {
                        text: 'Actividades',
                        value: this.activities.length,
                        url: this.route('activities.index'),
                    },
                    icon: {
                        name: 'timeline',
                        color: 'text-white',
                        background: 'actividades-icon',
                    },
                },
            ];
        },
        //----------------------------------------------------------
        //-------------------ESTADISTICAS ANUALES-------------------
        //----------------------------------------------------------
        chartDataFacturas() {
            return this.obtenerDatosPorMes(this.filteredFacturas, 'fechaInicio');
        },
        chartDataBalance() {
            const meses = Array(12).fill(0);

            const facturas = this.obtenerDatosPorMes(this.filteredFacturas, 'fechaInicio');

            for (let i = 0; i < 12; i++) {
                const totalFacturas = Number(facturas[i] || 0);

                meses[i] = totalFacturas;
            }

            return meses;
        },
        topBalanceClientesFull() {


            // Devolvemos el top 10 de clientes con mayor balance
            return Object.values(resumen)
                .filter(cliente => cliente.balance !== 0) // Excluimos los clientes con balance cero
                .sort((a, b) => b.balance - a.balance)    // Ordenamos de mayor a menor balance
                .slice(0, 10);                            // Nos quedamos solo con los primeros 10
        },
        tipoGraficoGeneral() {
            return this.modoGraficoGeneral === 'evolucion' ? 'line' : 'bar';
        },
        graficoGeneral() {
            if (this.modoGraficoGeneral === 'evolucion') {
                return this.obtenerGraficoEvolucion();
            }
            if (this.modoGraficoGeneral === 'topClientes') {
                return this.obtenerGraficoTop(this.filteredFacturas, 'cliente', 'total_cobrado', 'Facturado', '#26adea');
            }
            if (this.modoGraficoGeneral === 'topClientesBalance') {
                return this.obtenerGraficoTopClientesBalance();
            }

            return { labels: [], datasets: [] };
        },
        graficoGeneralTitulo() {
            switch (this.modoGraficoGeneral) {
                case 'evolucion':
                    return `Evolución General ${this.year}`;
                case 'topClientes':
                    return `Top 10 Clientes ${this.year}`;
                case 'topClientesBalance':
                    return `Top 10 Clientes (Balance) ${this.year}`;
                default:
                    return 'Evolución General';
            }
        },
        //----------------------------------------------------------
        //-------------------ESTADISTICAS DONUT---------------------
        //----------------------------------------------------------
        /**
         * Devuelve un objeto que contiene los datos para dibujar un gráfico de donut que muestra la composición del balance en función del tipo de movimiento.
         * Los tipos de movimiento son: Facturas.
         * y "filteredPartes" para obtener los datos necesarios. Si no hay datos para mostrar, devuelve un gráfico con un solo elemento que indica que no hay datos.
         *
         * @returns {Object} Un objeto con la estructura de un gráfico de donut, con las etiquetas y los datos formateados para ser utilizados en un gráfico.
         */
        graficosDonut() {
            let resultado;

            if (this.modoGraficoActual === 'presupuestos') {
                resultado = this.generarGraficoPorEstado(this.filteredPresupuestos, 'total');
            }
            else if (this.modoGraficoActual === 'facturas') {
                resultado = this.generarGraficoPorEstado(this.filteredFacturasEstados, 'total');
            }
            else {
                const labels = ['Facturas'];

                const reales = [
                    { total: this.calculateTotal(this.filteredFacturas, "total_cobrado", 'total'), count: this.filteredFacturas.length },
                ];

                const data = reales.map(v => v.total === 0 ? 0.001 : v.total);

                const dataOriginal = reales.map(v => v.total || 0);

                const resultadoBalance = {
                    labels,
                    datasets: [
                        {
                            label: 'Composición del Balance',
                            data,
                            realValues: reales,
                            backgroundColor: ['#88df90'],
                            borderColor: '#fff',
                            borderWidth: 2,
                        },
                    ],
                };

                // ❌ Si no hay ni conteo ni total
                const noDataAvailable = dataOriginal.every(total => total === 0);

                if (noDataAvailable) {
                    return {
                        labels: ["No hay datos"],
                        datasets: [
                            {
                                label: "Sin datos",
                                data: [1],
                                backgroundColor: ["#d3d3d3"],
                                borderColor: "#fff",
                                borderWidth: 2,
                            },
                        ],
                    };
                }

                return resultadoBalance;
            }

            const data = resultado.datasets?.[0]?.data || [];
            const noDataAvailable = data.length === 0;

            if (noDataAvailable) {
                return {
                    labels: ["No hay datos"],
                    datasets: [
                        {
                            label: "Sin datos",
                            data: [1],
                            backgroundColor: ["#d3d3d3"],
                            borderColor: "#fff",
                            borderWidth: 2,
                        },
                    ],
                };
            }

            return resultado;
        },
        /**
         * Titulo para el grafico de donut, dependiendo del modo actual en el que se encuentra el dashboard
         *
         * @returns {String} El t tulo para el gr fico de donut
         */
        graficoDonutTitulo() {
            if (this.modoGraficoActual === 'presupuestos') return 'Presupuestos';
            if (this.modoGraficoActual === 'facturas') return 'Facturas';
            return 'Balance';
        },
        /**
         * Subtitulo para el grafico de donut, dependiendo del modo actual en el que se encuentra el dashboard
         *
         * @returns {String} El subt tulo para el gr fico de donut
         */
        graficoDonutSubtitulo() {
            switch (this.modoGraficoActual) {
                case 'presupuestos':
                    return `Total de presupuestos agrupados por estado en ${this.year}`;
                case 'facturas':
                    return `Total de facturas agrupadas por estado en ${this.year}`;
                default:
                    return `Distribución de gastos e ingresos en ${this.year}`;
            }
        },
        /**
         * Texto para el footer del grafico de donut, dependiendo del modo actual en el que se encuentra el dashboard
         *
         * @returns {String} El texto para el footer del gr fico de donut
         */
        graficoDonutFooter() {
            switch (this.modoGraficoActual) {
                case 'presupuestos':
                    return `Total: ${this.formatCurrency(this.totalPresupuestos)}`;
                case 'facturas':
                    return `Total: ${this.formatCurrency(this.totalFacturas)}`;
                default:
                    return `Total: ${this.formatCurrency(this.totalBalance)}`;
            }
        },
        /**
         * El tipo de grafico de donut que se debe mostrar, dependiendo del modo actual en el que se encuentra el dashboard.
         * El tipo de grafico se determina concatenando el string 'doughnut-' con el nombre del modo actual.
         *
         * @returns {String} El tipo de gr fico de donut
         */
        graficoDonutTipo() {
            return 'doughnut-' + this.modoGraficoActual;
        },
        //-----------------------------------------------------------
        //----------------------------FILTROS------------------------
        //----------------------------------------------------------
        filteredPresupuestos() {
            return (this.filterByYear(this.presupuestos, 'fechaInicio'));
        },
        filteredFacturas() {
            return (this.filterByYear(this.facturas, 'fechaInicio'));
        },
        filteredUsers() {
            return (this.users);
        },
        filteredFacturasEstados() {
            return (this.filterByYear(this.facturas_estados, 'fechaInicio'));
        },
        yearsArray() {
            if (this.chartData?.facturas?.data && Object.keys(this.chartData.facturas.data).length) {
                return Object.keys(this.chartData.facturas.data).map(year => ({
                    value: year,
                    text: year,
                }));
            }

            if (this.chartData?.presupuestos?.data && Object.keys(this.chartData.presupuestos.data).length) {
                return Object.keys(this.chartData.presupuestos.data).map(year => ({
                    value: year,
                    text: year,
                }));
            }

            // Si no hay datos, al menos muestra el año actual
            return [
                { value: this.year, text: this.year }
            ];
        },
        //-----------------------------------------------------------
        //------------------------TOTALES----------------------------
        //-----------------------------------------------------------
        totalFacturas() {
            return this.calculateTotal(this.filteredFacturas, "total_cobrado");
        },
        totalPresupuestos() {
            return this.calculateTotal(this.filteredPresupuestos);
        },
        /**
         * Calcula el balance total de las facturas, restando los totales, y luego agregando el total
         * cobrado del balance del chartData si está disponible.
         *
         * @returns {number} El valor del balance total calculado.
         */
        totalBalance() {
            const balance = this.totalFacturas;

            return balance + (this.chartData.balance?.totalCobrado || 0);
        },
        totalEmpresas() {
            return this.contarPorCampo(this.clientes, 'category', 'Empresa');
        },
        totalPersonas() {
            return this.contarPorCampo(this.clientes, 'category', 'Personal');
        },
    },
    mounted() {
        document.addEventListener("click", this.closeOnClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener("click", this.closeOnClickOutside);
    },
    components: {
        ChartHolderCard,
        ReportsBarLinesGeneral,
        ReportsBarLinesGeneralMobile,
        ReportsDonutChart,
        ReportsDonutCharttMobile,
        MiniStatisticsCard,
        ProjectCard,
        AuthenticatedLayout,
        MaterialSelect,
        MaterialTableDashboard,
    },
    methods: {
        //-----------------------------------------------------------
        //----------------------------FILTROS------------------------
        //-----------------------------------------------------------
        /**
         * Filtra un array de objetos para incluir solo aquellos cuyo año en el campo de fecha especificado
         * coincide con el año actualmente seleccionado en el componente (this.year).
         *
         * @param {Array} array - El array de objetos a filtrar.
         * @param {string|Array} fecha - Un campo de fecha o un array de campos de fecha donde se buscará la fecha.
         * @returns {Array} - Un array filtrado con objetos cuyo año coincide con el año seleccionado.
         */
        filterByYear(array, fecha) {
            if (!array || array.length === 0) return [];

            return array.filter(item => {
                let fechaItem;

                if (Array.isArray(fecha)) {
                    // Si es un array de posibles campos de fecha (como ['fechaInicio', 'created_at'])
                    for (const campo of fecha) {
                        if (item[campo]) {
                            fechaItem = new Date(item[campo]);
                            break;
                        }
                    }
                } else {
                    // Si es un solo campo
                    fechaItem = new Date(item[fecha]);
                }

                if (!fechaItem || isNaN(fechaItem)) return false;

                // Devuelve true solo si el año coincide
                return fechaItem.getFullYear() === Number(this.year);
            });
        },
        //-----------------------------------------------------------
        //----------------------------TOTALES------------------------
        //-----------------------------------------------------------
        /**
         * Suma el valor de un campo (por defecto "total") en un array de objetos. Si el campo no existe, se considera que su valor es 0.
         *
         * @param {Array} array - Un array de objetos.
         * @param {string} [key="total"] - El campo a sumar. Por defecto es "total".
         * @returns {number} - La suma total de los valores del campo.
         */
        calculateTotal(array, key = "total") {
            return array.reduce((sum, item) => sum + Number(item[key] || 0), 0);
        },
        /**
         * Contabiliza el numero de objetos en un array que coinciden con el valor de un campo (o todos los valores de un campo).
         * Si se pasa un valor, se devuelve la cantidad de objetos que coinciden con ese valor.
         * Si no se pasa un valor, se devuelve un objeto con la contabilizacion agrupada por el campo.
         *
         * @param {Array} array - El array a contar.
         * @param {string} campo - El campo a contar.
         * @param {any} [valor] - El valor a contar. Si no se pasa, se devuelve un conteo agrupado.
         * @returns {number|Object} - La cantidad de objetos que coinciden con el valor (si se pasa) o un objeto con la contabilizaci n agrupada.
         */
        contarPorCampo(array, campo, valor = null) {
            if (!Array.isArray(array)) return 0;

            if (valor) {
                // Si se pasa valor, contamos solo los que coincidan
                return array.filter(item => item[campo] === valor).length;
            }

            // Si no se pasa valor, devolvemos un conteo agrupado
            return array.reduce((acc, item) => {
                const key = item[campo];
                if (!key) return acc;
                acc[key] = (acc[key] || 0) + 1;
                return acc;
            }, {});
        },
        //-----------------------------------------------------------
        //---------------------------FORMATOS------------------------
        //-----------------------------------------------------------
        /**
         * Formatea una fecha en formato string a una cadena de texto con formato dd/mm/yyyy.
         *
         * @param {string} dateString - La fecha en formato string (por ejemplo "2022-01-01T00:00:00.000Z").
         * @returns {string} - La fecha formateada como cadena de texto (por ejemplo "1 de enero de 2022").
         */
        formatDate(dateString) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(dateString).toLocaleDateString("es-ES", options);
        },
        /**
         * Formatea un numero como una cadena de texto con formato de moneda EUR con dos decimales.
         *
         * @param {number} amount - El numero a formatear.
         * @returns {string} - La cadena de texto formateada.
         */
        formatCurrency(amount) {
            return new Intl.NumberFormat("es-ES", {
                style: "currency",
                currency: "EUR",
                minimumFractionDigits: 2,
            }).format(amount);
        },
        //----------------------------------------------------------
        //-------------------------KPIS-----------------------------
        //----------------------------------------------------------
        /**
         * Devuelve un string con el conteo de cada estado en la lista de elementos.
         * Por ejemplo, si se pasa una lista de elementos con estados "pendiente",
         * "en curso" y "terminado", el resultado ser a "pendiente: 3, en curso: 2, terminado: 1".
         *
         * @param {Array} array - lista de elementos con un campo "estado_text"
         * @param {string} campoFecha - nombre del campo de fecha en el que filtrar
         * @return {String} string con el conteo de cada estado
         */
        estados(array, campoFecha = 'fecha') {
            const counts = {};

            if (!Array.isArray(array)) return '';

            array.forEach(item => {
                // Filtrar por año si tiene fecha
                let fechaItem = item[campoFecha] ? new Date(item[campoFecha]) : null;

                if (!fechaItem || isNaN(fechaItem) || fechaItem.getFullYear() !== Number(this.year)) {
                    return; // Saltar si no es del año seleccionado
                }

                const estado = item.estado_text || 'Sin estado';
                counts[estado] = (counts[estado] || 0) + 1;
            });

            return Object.entries(counts)
                .map(([estado, count]) => `${estado}: ${count}`)
                .join(', ');
        },
        //----------------------------------------------------------
        //--------------ESTADISTICAS GENERALES Y TOP----------------
        //----------------------------------------------------------
        /**
         * Alterna entre los diferentes modos de grafico general disponibles (evolucion, top clientes, top clientes balance).
         * El modo actual se determina por el valor de la propiedad "modoGraficoGeneral".
         */
        alternarModoGraficoGeneral() {
            const modos = ['evolucion', 'topClientes', 'topClientesBalance'];

            const index = modos.indexOf(this.modoGraficoGeneral);
            this.modoGraficoGeneral = modos[(index + 1) % modos.length];
        },
        /**
         * Muestra el modo de grafico general anterior al actual.
         * El modo actual se determina por el valor de la propiedad "modoGraficoGeneral".
         */
        modoGraficoGeneralAnterior() {
            const modos = ['evolucion', 'topClientes', 'topClientesBalance'];

            const index = modos.indexOf(this.modoGraficoGeneral);
            this.modoGraficoGeneral = modos[(index - 1 + modos.length) % modos.length];
        },
        /**
         * Genera un grafico de evolucion mensual de Facturas
         * Los datos se extraen de las propiedades chartDataFacturas
         *
         * @returns {Object} - Un objeto con la estructura de un grafico, con los datos de evolucion mensual.
         */
        obtenerGraficoEvolucion() {
            const labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            return {
                labels,
                datasets: [
                    {
                        label: 'Facturas',
                        data: this.chartDataFacturas,
                        realValues: this.chartDataFacturas.map(total => ({ total })),
                        backgroundColor: '#88df90',
                        borderColor: '#88df90',
                    },
                    {
                        label: 'Balance',
                        data: this.chartDataBalance,
                        realValues: this.chartDataBalance.map(total => ({ total })),
                        backgroundColor: '#f8df81',
                        borderColor: '#f8df81',
                    },
                ],
            };
        },
        /**
         * Genera un gráfico de los 10 principales elementos basados en un campo de relación y un campo de valor.
         *
         * @param {Array} array - Un array de objetos que contienen los datos a resumir.
         * @param {string} campoRelacion - El campo en los objetos que define la relación, por ejemplo, 'cliente' o 'gasto'.
         * @param {string} campoValor - El campo en los objetos que contiene el valor numérico a sumar.
         * @param {string} etiqueta - La etiqueta para el conjunto de datos del gráfico.
         * @param {string|Array} color - El color o los colores del gráfico.
         * @returns {Object} Un objeto con las etiquetas y los datos formateados para ser utilizados en un gráfico.
         */
        obtenerGraficoTop(array, campoRelacion, campoValor, etiqueta, color) {
            const resumen = {};

            array.forEach(item => {
                let id, nombreFinal;


                id = item[`${campoRelacion}_id`] || 'desconocido';
                nombreFinal = item[campoRelacion]?.nombre || `Sin ${campoRelacion}`;

                const total = Number(item[campoValor]) || 0;

                if (!resumen[id]) {
                    resumen[id] = { nombre: nombreFinal, total: 0 };
                }

                resumen[id].total += total;
            });

            const top = Object.values(resumen)
                .sort((a, b) => b.total - a.total)
                .slice(0, 10);

            return {
                labels: top.map(item => item.nombre),
                datasets: [
                    {
                        label: etiqueta,
                        data: top.map(item => item.total),
                        realValues: top.map(item => ({ total: item.total })),
                        backgroundColor: color,
                        borderColor: color,
                    },
                ],
            };
        },
        /**
         * @returns {Object} Un objeto con las etiquetas y los datos formateados para ser utilizados en un gráfico.
         */
        obtenerGraficoTopClientesBalance() {
            const top = this.topBalanceClientesFull;

            return {
                labels: top.map(cliente => cliente.nombre),
                datasets: [
                    {
                        label: "Balance Clientes (Verde: positivo, Rojo: negativo)",
                        data: top.map(cliente => Number(cliente.balance)),
                        realValues: top.map(cliente => ({ total: Number(cliente.balance) })),
                        backgroundColor: top.map(cliente => Number(cliente.balance) >= 0 ? '#88df90' : '#FF746C'),
                        borderColor: '#fff',
                        borderWidth: 2,
                    },
                ],
            };
        },
        /**
         * Calcula los totales por mes a partir de un array de objetos, sumando los valores del campo especificado para cada mes.
         *
         * @param {Array} array - Un array de objetos que contienen los datos a procesar.
         * @param {string|string[]} campoFecha - El nombre del campo que contiene la fecha o un array de nombres de campos a comprobar.
         * @param {string} [campoTotal='total'] - El nombre del campo que contiene el valor numérico a sumar.
         * @returns {number[]} Un array de 12 elementos, cada uno representando el total del campo especificado para cada mes del año actual.
         */
        obtenerDatosPorMes(array, campoFecha, campoTotal = 'total') {
            const meses = Array(12).fill(0);

            if (!Array.isArray(array)) return meses;

            array.forEach(item => {
                let fechaValor;

                if (Array.isArray(campoFecha)) {
                    for (const campo of campoFecha) {
                        if (item[campo]) {
                            fechaValor = item[campo];
                            break;
                        }
                    }
                } else {
                    fechaValor = item[campoFecha];
                }

                if (!fechaValor) return;

                const fecha = new Date(fechaValor);

                if (!(fecha instanceof Date) || isNaN(fecha.getTime())) return;

                const anio = fecha.getFullYear();
                const mes = fecha.getMonth();

                if (anio === Number(this.year)) {
                    meses[mes] += Number(item[campoTotal] || 0);
                }
            });

            return meses;
        },
        //----------------------------------------------------------
        //-------------------ESTADISTICAS DONUT---------------------
        //----------------------------------------------------------
        /**
         * Genera un gráfico de donut que muestra la composición de un array por estado. Los datos se extraen de la propiedad "estado_text" y se agrupan por estado.
         * Se devuelve un objeto con la estructura de un gráfico, con las etiquetas y los datos formateados para ser utilizados en un gráfico.
         *
         * @param {Array} array - Un array de objetos que contienen los datos a procesar.
         * @param {string} [campoTotal='total'] - El nombre del campo que contiene el valor numérico a sumar.
         * @param {string} [label='Por Estado'] - El título del gráfico.
         * @returns {Object} Un objeto con las etiquetas y los datos formateados para ser utilizados en un gráfico.
         */
        generarGraficoPorEstado(array, campoTotal = 'total', label = 'Por Estado') {
            const resumen = {};

            // 2. Agrupar totales y contar elementos por estado
            filtrado.forEach(item => {
                const estado = item.estado_text || 'Sin estado';
                const total = Number(item[campoTotal] || 0);

                if (!resumen[estado]) {
                    resumen[estado] = {
                        total: 0,
                        count: 0,
                    };
                }

                resumen[estado].total += total;
                resumen[estado].count += 1;
            });

            const labels = Object.keys(resumen);

            // Esta estructura es la que se pasa al tooltip: { total, count }
            const realValues = labels.map(estado => resumen[estado]);

            // Visualización: usamos 0.001 solo si el total es 0
            const dataParaGrafico = realValues.map(val => val.total === 0 ? 0.001 : val.total);

            const estadoColors = {
                'Aceptado': '#88df90',
                'Pagado': '#88df90',
                'Enviado': '#88df90',
                'Pagado Parcialmente': '#66f1c2',
                'Recepcionado': '#88df90',
                'Facturado': '#d3d3ff',
                'Facturado Parcialmente': '#b3ebf2',
                'Pendiente': '#f7e6ca',
                'Incompleto': '#f7e6ca',
                'Rechazado': '#FF746C',
                'Gasto': '#FF746C',
                'Rectificada': '#ffb27f',
                'Cancelado': '#FF746C',
                'Desconocido': '#777',
                'Sin estado': '#ccc',
            };

            const backgroundColors = labels.map(label => estadoColors[label] || '#ccc');

            return {
                labels,
                datasets: [
                    {
                        label,
                        data: dataParaGrafico,
                        realValues, // ← contiene { total, count } por estado
                        backgroundColor: backgroundColors,
                        borderColor: '#fff',
                        borderWidth: 2,
                    },
                ],
            };
        },
        /**
         * Alterna entre los diferentes modos de grafico donut disponibles (balance, presupuestos).
         * El modo actual se determina por el valor de la propiedad "modoGraficoActual".
         */
        alternarModoGraficoDonut() {
            const modos = ['balance', 'presupuestos', 'facturas'];
            const index = modos.indexOf(this.modoGraficoActual);
            this.modoGraficoActual = modos[(index + 1) % modos.length];
        },
        /**
         * Muestra el modo de grafico donut anterior al actual.
         * El modo actual se determina por el valor de la propiedad "modoGraficoActual".
         */
        modoGraficoAnterior() {
            const modos = ['balance', 'presupuestos', 'facturas'];
            const index = modos.indexOf(this.modoGraficoActual);
            this.modoGraficoActual = modos[(index - 1 + modos.length) % modos.length];
        },
    },
};
</script>

<style scoped>
/* Transición más suave y sin salto */
.collapse-smooth-enter-active,
.collapse-smooth-leave-active {
    max-height: 1000px;
    transition: max-height 0.6s ease, opacity 0.6s ease;
    overflow: hidden;
}

.collapse-smooth-enter-from,
.collapse-smooth-leave-to {
    max-height: 0;
    opacity: 0;
}

.collapse-smooth-enter-to,
.collapse-smooth-leave-from {
    max-height: 1000px;
    opacity: 1;
}

button:focus {
    box-shadow: none !important;
    outline: none !important;
}

label {
    line-height: 1 !important;
    font-size: 0.75rem !important;
}

.material-select-wrapper {
    margin-bottom: 0 !important;
}

::v-deep(.form-select-sm) {
    padding-top: 0.2rem !important;
    padding-bottom: 0.2rem !important;
    font-size: 0.75rem !important;
}
</style>
