<template>
    <AuthenticatedLayout :user="user" :title="'Vista de Cliente'">
        <div class="container-fluid">

            <div class="card-header">
                <div class="user d-flex align-items-center">
                    <div class="col-lg-6">
                        <a class="d-flex text-decoration-none fs-5 p-2 agregar" href="javascript:history.go(-1);"
                            role="button"><i class="material-icons pe-2 pt-1"><span
                                    class="material-symbols-outlined">arrow_back</span></i>Volver</a>
                    </div>

                </div>
            </div>

            <div class="row my-4 screen-width">
                <div class="col-lg-12 position-relative">
                    <div class="row mt-4">

                        <div class="col-12 my-3">
                            <div class="card p-3">
                                <span class="h5 text-bold">Detalles Del Cliente</span>
                                <div class="row">
                                    <div class="col-lg-3 col-md-6 col-sm-6">
                                        <a data-bs-toggle="modal" data-bs-target="#Modal347" class="btn btn-warning">
                                            <i class="material-icons">picture_as_pdf</i>
                                            <div class="text-center">
                                                Modelo 347
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6 col-sm-6">
                                        <mini-statistics-card data-bs-toggle="modal" data-bs-target="#facturasModal"
                                            role="button"
                                            :title="{ text: 'FACTURAS', value: formatCurrency(totals.facturas_total) }"
                                            :icon="{
                                                name: 'weekend',
                                                color: 'text-white',
                                                background: 'facturas-icon',
                                            }" />
                                        <Facturas :facturas="facturas" />
                                    </div>
                                </div>
                                <hr>

                                <div class="row">

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">person</i>
                                        <span><strong>Cliente :</strong> {{ cliente.nombre }} {{
                                            cliente.cliente?.apellido_1 }}</span>
                                    </div>

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">fingerprint</i>
                                        <span><strong>NIF/CIF :</strong> {{ cliente.dni }}</span>
                                    </div>

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">phone</i>
                                        <span><strong>Telefono movil :</strong> {{ cliente.telefono_mobile }}</span>
                                    </div>

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">phone</i>
                                        <span><strong>Telefono fijo :</strong> {{ cliente.telefono_fijo }}</span>
                                    </div>

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">email</i>
                                        <span><strong>Email :</strong> {{ cliente.email }}</span>
                                    </div>

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">account_balance</i>
                                        <span><strong>Cuenta Bancaria :</strong> {{ cliente.num_cuenta }}</span>
                                    </div>

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">account_balance</i>
                                        <span><strong>Trabajador :</strong> {{ cliente.trabajador?.name }}</span>
                                    </div>

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">location_on</i>
                                        <span><strong>Dirección :</strong> {{ cliente.localidad }}, {{
                                            cliente.direccion }},
                                            {{
                                                cliente.cp }}</span>
                                    </div>

                                    <div class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">description</i>
                                        <span><strong>Descripcion :</strong> {{ cliente.descripcion }}</span>
                                    </div>

                                    <div v-if="cliente.share_data" class="col-lg-6 d-flex align-items-center">
                                        <i class="material-icons mr-2">key</i>
                                        <span><strong>API KEY :</strong> {{ cliente.api_key }}</span>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div>
                            <chart-holder-card title="FACTURAS" :subtitle="`<span class='font-weight-bolder'>${chartData.facturas.percentageDifference}</span>
                 ${chartData.facturas.percentageDifference.includes('+') ? 'aumento' : 'disminución'}.`"
                                :update="facturas.length ? timeAgo(facturas[facturas.length - 1].updated_at) : null"
                                color="success">

                                <reports-line-chart :chart="{
                                    labels: chartData.facturas?.labels,
                                    datasets: {
                                        label: 'Facturas',
                                        data: [...this.chartData.facturas.data],
                                    },
                                }" />
                            </chart-holder-card>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        <Modal347Individual :cliente="cliente" />
    </AuthenticatedLayout>
</template>
<script>
import ChartHolderCard from "@/views/components/ChartHolderCard.vue";
import ReportsBarChart from "@/examples/Charts/ReportsBarChart.vue";
import ReportsLineChart from "@/examples/Charts/ReportsLineChart.vue";
import MiniStatisticsCard from "@/views/components/MiniStatisticsCard.vue";
import ProjectCard from "@/views/components/ProjectCard.vue";
import TimelineList from "@/examples/Cards/TimelineList.vue";
import TimelineItem from "@/examples/Cards/TimelineItem.vue";
import placeholder from '@/assets/img/placeholder.jpg';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import moment from 'moment';
import Facturas from "./Relations/Facturas.vue";
import Modal347Individual from "./Relations/Modal347Individual.vue";

export default {
    name: "dashboard_admin",
    data() {
        return {
        };
    }, props: {
        user: Object,
        cliente: Object,
        totals: {
            type: Object,
            default: () => ({
                facturas_total: 0,
            })
        },
        chartData: {
            type: Object,
            default: () => ({
                facturas: { data: [], percentageDifference: '' },
            })
        },
        facturas: Array,
    },
    mounted() {
    },
    components: {
        ChartHolderCard,
        ReportsBarChart,
        ReportsLineChart,
        MiniStatisticsCard,
        ProjectCard,
        TimelineList,
        TimelineItem,
        AuthenticatedLayout,
        Facturas,
        Modal347Individual
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', options);
        },
        timeAgo(date) {
            return moment(date).fromNow();  // Returns time difference like "1 day ago"
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount);
        }, getArchivoUrl(archivo) {
            if (archivo != null && archivo != '') {
                if (typeof archivo === 'string') {
                    const modifiedArchivo = archivo.replace("public/", ""); // Remove 'public/' segment
                    return "/storage/" + modifiedArchivo;
                } else if (archivo instanceof File) {
                    return URL.createObjectURL(archivo);
                } else {
                    console.error("Invalid file type");
                    return placeholder; // Return placeholder in case of an invalid file type
                }
            } else {
                return placeholder; // Return placeholder when archivo is null
            }
        },
    }
};
</script>
