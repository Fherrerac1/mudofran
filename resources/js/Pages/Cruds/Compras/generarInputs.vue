<template>
    <div id="contenedor">
        <div v-for="(servicio, indexServicio) in form.servicios" :key="indexServicio"
            class="row mb-2 this-servicio border rounded p-3" :id="'s' + indexServicio">
            <!-- Servicio -->
            <div class="col-lg-6 form-group">
                <ServiciosCompleter label="Servicio" :id="'contenido' + indexServicio" type="text"
                    v-model="servicio.contenido" :servicio="servicio" class="servicios" />
            </div>
            <div class="col-lg-1 d-flex align-items-end">
                <MaterialButton class="col btn btn-danger delete-btn" @click="deleteServicio(indexServicio)"
                    type="button">
                    <i class="material-icons">delete</i>
                </MaterialButton>
            </div>

            <div class="col-12 form-group mt-2">
                <MaterialTextarea :id="'descripcion' + indexServicio" placeholder="Descripción" :rows="2"
                    v-model="servicio.descripcion" />
            </div>

            <!-- Productos dentro de este servicio (SortableJS wrapper) -->
            <div class="col-12" :id="'sortable-' + indexServicio">
                <div class="col-12 row this-producto" v-for="(producto, index) in servicio.productos" :key="producto.id"
                    :id="'p' + producto.id">
                    <div class="col-12 row border my-1 mx-2 rounded">
                        <div class="row col-lg-11 mt-2 producto">
                            <div class="form-group col-lg-4">
                                <ProductosCompleter type="text" label="Nombre del Producto" :id="'nombre' + index"
                                    v-model="producto.nombre" :producto="producto" />
                            </div>
                            <div class="form-group col-lg-2">
                                <MaterialInput variant="outline" :id="'precio' + index" label="Precio" type="number"
                                    step="any" v-model="producto.precio" @input="calculateTotals" />
                            </div>
                            <div class="form-group col-lg-2">
                                <MaterialInput variant="outline" :id="'cantidad' + index" label="Cantidad" type="number"
                                    step="any" v-model="producto.cantidad" @input="calculateTotals" />
                            </div>
                            <div class="form-group col-lg-2">
                                <MaterialInput variant="outline" :id="'descuento' + index" label="Dto." type="number"
                                    step="any" max="99" min="0" v-model="producto.descuento" @input="calculateTotals" />
                            </div>
                            <div class="form-group col-lg-2">
                                <MaterialInput variant="outline" :id="'precio_sin_iva' + index" label="Precio Sin Iva"
                                    v-model="producto.precio_sin_iva" readonly />
                            </div>
                            <div class="form-group col-lg-11 my-2">
                                <MaterialTextarea :id="'descripcion' + index" placeholder="Descripcion"
                                    v-model="producto.descripcion" :rows="1" />
                            </div>
                            <div class="form-group col-lg-1 mt-2">
                                <MaterialButton class="col btn btn-danger delete-btn mt-1" type="button"
                                    @click="deleteProducto(indexServicio, producto.id)">
                                    <i class="material-icons m-0" style="font-size: 14px;">close</i>
                                </MaterialButton>
                            </div>
                        </div>
                        <div class="col-lg-1" title="Haz click y arrastra a la posición deseada.">
                            <i class="material-icons drag-button border p-1 mt-4 mx-3">height</i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add producto -->
            <div class="col-lg-2 d-flex align-items-end">
                <MaterialButton class="col btn btn-danger mt-2" @click="generateProducto(indexServicio)" type="button">
                    Añadir Producto
                </MaterialButton>
            </div>
        </div>

        <!-- Add servicio -->
        <div>
            <MaterialButton type="button" @click="generateServicio" class="btn btn-primary d-flex mt-3">
                <i class="material-icons pe-2">add_circle</i>Agregar Servicio
            </MaterialButton>
        </div>

        <!-- Totals -->
        <div class="row mt-5">
            <div class="col-lg-3 col-sm-6">
                <MaterialInput variant="outline" label="Total sin IVA" id="sumaTotalDiv" type="text"
                    v-model="form.total_sin_iva" readonly />
            </div>
            <div class="col-lg-3 col-sm-6">
                <MaterialInput variant="outline" label="IVA" id="totalIva" type="text" v-model="form.total_iva"
                    readonly />
            </div>
            <div class="col-lg-3 col-sm-6">
                <MaterialInput variant="outline" label="IRPF" id="totalIRPF" type="text" v-model="form.total_irpf"
                    readonly />
            </div>
            <div class="col-lg-3 col-sm-6">
                <MaterialInput variant="outline" label="Total" id="sumaTotalDivIva" type="text" v-model="form.total"
                    readonly />
            </div>
        </div>
    </div>
</template>

<script>
import Sortable from "sortablejs";
import MaterialButton from "@/Components/MaterialButton.vue";
import MaterialInput from "@/Components/MaterialInput.vue";
import MaterialTextarea from "@/Components/MaterialTextarea.vue";
import ServiciosCompleter from "../Servicios/ServiciosCompleter.vue";
import ProductosCompleter from "../Servicios/Productos/ProductosCompleter.vue";

export default {
    props: {
        form: Object,
    },
    components: {
        MaterialButton,
        MaterialInput,
        MaterialTextarea,
        ServiciosCompleter,
        ProductosCompleter,
    },
    methods: {
        initSortable(servicioIndex) {
            const el = document.getElementById(`sortable-${servicioIndex}`);
            if (!el || el.dataset.sortableInit) return;

            el.dataset.sortableInit = true; // avoid duplicate init
            Sortable.create(el, {
                animation: 150,
                handle: ".drag-button",
                onEnd: (evt) => {
                    const productos = this.form.servicios[servicioIndex].productos;
                    const movedItem = productos.splice(evt.oldIndex, 1)[0];
                    productos.splice(evt.newIndex, 0, movedItem);
                    this.calculateTotals();
                },
            });
        },
        generateServicio() {
            this.form.servicios.push({
                id: Date.now(),
                contenido: "",
                descripcion: "",
                productos: [],
            });
            this.$nextTick(() => {
                this.initSortable(this.form.servicios.length - 1);
            });
            this.calculateTotals();
        },
        deleteServicio(index) {
            this.form.servicios.splice(index, 1);
            this.calculateTotals();
        },
        generateProducto(servicioIndex) {
            this.form.servicios[servicioIndex].productos.push({
                id: Date.now(),
                nombre: "",
                cantidad: 0,
                precio: 0,
                descuento: 0,
                descripcion: "",
            });
            this.calculateTotals();
        },
        deleteProducto(servicioIndex, productoId) {
            const productos = this.form.servicios[servicioIndex].productos;
            const index = productos.findIndex((p) => p.id === productoId);
            if (index !== -1) productos.splice(index, 1);
            this.calculateTotals();
        },
        calculateTotals() {
            this.form.servicios.forEach((servicio) => {
                servicio.productos.forEach((producto) => {
                    const subtotal = (producto.precio * producto.cantidad) / 3;
                    const discountedSubtotal =
                        subtotal - subtotal * (producto.descuento / 100);
                    producto.precio_sin_iva = (discountedSubtotal * 3).toFixed(2);
                });
            });

            const baseTotalSinIva = this.form.servicios.reduce(
                (totalServicios, servicio) => {
                    const subtotalServicio =
                        servicio.productos?.reduce((total, producto) => {
                            const subtotal = producto.precio * producto.cantidad;
                            const discountedSubtotal =
                                subtotal - subtotal * (producto.descuento / 100);
                            return total + discountedSubtotal;
                        }, 0) || 0;
                    return totalServicios + subtotalServicio;
                },
                0
            );

            const porcentaje = (this.form.porcentaje ?? 100) / 100;
            const totalSinIva = baseTotalSinIva * porcentaje;

            const ivaRate = parseFloat(this.form.iva) || 0;
            const totalIva = (baseTotalSinIva * (ivaRate / 100)) * porcentaje;

            const irpfRate = parseFloat(this.form.irpf) || 0;
            const totalIrpf = (baseTotalSinIva * (irpfRate / 100)) * porcentaje;

            let totalConIva = totalSinIva + totalIva - totalIrpf;
            if (this.form.retencion) {
                totalConIva =
                    totalSinIva - totalSinIva * (this.form.retencion / 100) + totalIva - totalIrpf;
            }

            this.form.total_sin_iva = (Math.round(totalSinIva * 100) / 100).toFixed(2);
            this.form.total_iva = (Math.round(totalIva * 100) / 100).toFixed(2);
            this.form.total_irpf = (Math.round(totalIrpf * 100) / 100).toFixed(2);
            this.form.total = (Math.round(totalConIva * 100) / 100).toFixed(2);
        },
    },
    mounted() {
        this.form.servicios.forEach((_, index) => {
            this.initSortable(index);
        });
        this.calculateTotals();
    },
    updated() {
        this.form.servicios.forEach((_, index) => {
            this.initSortable(index);
        });
    },
};
</script>
