<template>
    <div></div>
</template>

<script>
export default {
    props: {
        pageLength: { type: [Number, String], default: 10 },
        defaultOrder: { type: Array, default: () => [[0, 'desc']], },
        tableId: { type: String, default: "material-table" },
        tableKey: { type: [String, Number], default: null },
        columnDefs: { type: Array, default: () => [] },
    },
    mounted() {
        this.loadDataTablesScript().then(() => {
            this.initDataTables();
        });
    },
    methods: {
        loadDataTablesScript() {
            return new Promise((resolve, reject) => {
                if (
                    document.getElementById("dataTablesCSS") &&
                    document.getElementById("dataTablesScript") &&
                    document.getElementById("dataTablesButtons")
                )
                    return resolve();

                // Core DataTables CSS
                const cssLink = document.createElement("link");
                cssLink.href = "https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css";
                cssLink.rel = "stylesheet";
                cssLink.id = "dataTablesCSS";
                document.head.appendChild(cssLink);

                // Buttons CSS
                const cssButtons = document.createElement("link");
                cssButtons.href = "https://cdn.datatables.net/buttons/3.0.1/css/buttons.bootstrap5.min.css";
                cssButtons.rel = "stylesheet";
                cssButtons.id = "dataTablesButtonsCSS";
                document.head.appendChild(cssButtons);

                // Core DataTables script
                const script = document.createElement("script");
                script.src = "https://cdn.datatables.net/2.1.8/js/dataTables.min.js";
                script.id = "dataTablesScript";

                script.onload = () => {
                    const scriptBootstrap = document.createElement("script");
                    scriptBootstrap.src = "https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js";
                    scriptBootstrap.id = "dataTablesScriptBootstrap5";

                    scriptBootstrap.onload = () => {
                        // Load buttons core
                        const scriptButtons = document.createElement("script");
                        scriptButtons.src = "https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.min.js";
                        scriptButtons.id = "dataTablesButtons";

                        scriptButtons.onload = () => {
                            // Load buttons bootstrap
                            const scriptButtonsBootstrap = document.createElement("script");
                            scriptButtonsBootstrap.src = "https://cdn.datatables.net/buttons/3.0.1/js/buttons.bootstrap5.min.js";

                            // Load buttons ColVis
                            const scriptColVis = document.createElement("script");
                            scriptColVis.src = "https://cdn.datatables.net/buttons/3.0.1/js/buttons.colVis.min.js";

                            document.body.appendChild(scriptButtonsBootstrap);
                            document.body.appendChild(scriptColVis);

                            scriptColVis.onload = resolve;
                            scriptColVis.onerror = () => reject(new Error("Failed to load ColVis extension"));
                        };

                        scriptButtons.onerror = () => reject(new Error("Failed to load Buttons extension"));
                        document.body.appendChild(scriptButtons);
                    };

                    scriptBootstrap.onerror = () => reject(new Error("Failed to load DataTables Bootstrap5 script"));
                    document.body.appendChild(scriptBootstrap);
                };

                script.onerror = () => reject(new Error("Failed to load DataTables script"));
                document.body.appendChild(script);
            });
        },
        initDataTables() {
            const tables = document.querySelectorAll(".material-table");
            $.fn.dataTable.ext.errMode = "none";

            tables.forEach((table) => {
                const $table = $(table);

                if ($.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().destroy();
                }

                $table.DataTable({
                    paging: true,
                    ordering: true,
                    pageLength: this.pageLength,
                    lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                    columnDefs: [
                        { targets: "no-sort", orderable: false },
                        { targets: 0, type: "num" },
                        ...this.columnDefs
                    ],
                    stateSave: true,
                    stateDuration: -1,
                    order: this.defaultOrder || false,
                    info: true,
                    responsive: true,
                    dom: '<"row mb-3 align-items-center"<"col-sm-3 dt-length"l><"col-sm-9 dt-search-and-buttons d-flex justify-content-end align-items-center"fB>>' +
                        '<"row"<"col-12"tr>>' +
                        '<"row mt-2"<"col-sm-5"i><"col-sm-7 d-flex justify-content-end"p>>',
                    buttons: [
                        {
                        extend: 'colvis',
                        text: '<i class="fa fa-columns"></i>',
                        titleAttr: 'Mostrar/Ocultar columnas',
                        className: 'btn style_color m-0 btn-sm ms-2',
                        }
                    ],
                    language: {
                        processing: "Procesando...",
                        search: "Buscar:",
                        lengthMenu: "Mostrar _MENU_ registros",
                        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        infoEmpty: "Mostrando 0 a 0 de 0 registros",
                        infoFiltered: "(filtrado de _MAX_ registros en total)",
                        loadingRecords: "Cargando...",
                        zeroRecords: "No se encontraron registros coincidentes",
                        emptyTable: "No hay datos disponibles en la tabla",
                        paginate: {
                        first: "«",
                        previous: "‹",
                        next: "›",
                        last: "»",
                        },
                        aria: {
                        sortAscending: ": activar para ordenar ascendente",
                        sortDescending: ": activar para ordenar descendente",
                        },
                    },
                    });
                });
            },
    },
};
</script>


<style>
.dataTables_wrapper {
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.dataTables_filter input {
    width: 200px !important;
    display: inline-block;
    margin-left: 0.5rem;
}

.dataTables_paginate {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 0.5rem !important;
    margin-top: 1rem !important;
    flex-wrap: wrap;
}

.dataTables_paginate .paginate_button {
    padding: 6px 12px !important;
    border: 1px solid #dee2e6 !important;
    background-color: #f9f9f9 !important;
    color: #253249 !important;
    font-size: 0.875rem !important;
    border-radius: 6px !important;
    cursor: pointer !important;
    transition: all 0.2s ease-in-out !important;
}

.dataTables_paginate .paginate_button:hover {
    background-color: #e6e9f1 !important;
    color: #253249 !important;
}

.dataTables_paginate .paginate_button.current {
    font-weight: bold !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
}

.dataTables_paginate .paginate_button.disabled {
    opacity: 0.5 !important;
    pointer-events: none !important;
}

div.dt-container div.dt-search input {
    border: 1px solid #f0f2f5;
    border-radius: 7px;
}

/**********************************************************************
                    TÍTULO CENTRADO PERSONALIZADO
**********************************************************************/
.custom-title-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}

.title-wrapper {
    background-color: #253249;
    padding: 0.5rem 1.25rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.title-wrapper h6 {
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
    margin: 0;
}

.dt-button-collection {
    right: 0 !important;
    /* align right edge of dropdown with button */
    left: auto !important;
    /* cancel left positioning */
    transform-origin: top right !important;
    /* for nice animation */
    box-shadow: 0px 0px 3px black;
}
</style>
