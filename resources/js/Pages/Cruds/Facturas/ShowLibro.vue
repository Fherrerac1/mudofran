<template>
    <AuthenticatedLayout :user="user" :title="'Libros de las facturas'">
        <div class="container-fluid overflow-hidden">
            <div class="row">

                <div class="row" id="nav">
                    <a class="d-flex text-decoration-none fs-5" href="javascript:history.go(-1);" role="button">
                        <i class="material-icons pe-2 pt-1">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </i>
                        Volver
                    </a>
                </div>



                <div class="col-lg-9 min-vh-100">
                    <iframe :src="'data:application/pdf;base64,' + pdfContent" width="100%" height="100%"
                        style="min-height: 400px;"></iframe>
                </div>

                <div class="col-lg-3 p-0 card">
                    <div class="card-body">
                        <!-- Sidebar content -->
                        <p>Libro de las facturas</p>
                        <div class="row">
                            <div class="col animated-element">

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MaterialButton from "@/Components/MaterialButton.vue";

export default {
    components: {
        AuthenticatedLayout,
        MaterialButton
    },
    props: {
        facturas: {
            type: Object,
            required: true,
        },
        user: {
            type: Object,
            required: true,
        },
        pdfContent: {
            type: String,
            required: true,
        },
        startDate: {
            type: String,
            required: true,
        },
        finishDate: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
        };
    },
    computed: {
    },
    methods: {
    },
    mounted() {
        // Remove the "show" class from modal-backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.classList.remove('show');
            backdrop.classList.add('d-none');
        }
    },
};
</script>
