<template>
    <div>
        <!-- Toggle Button -->
        <button class="btn btn-secondary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#clienteDetails"
            aria-expanded="false" aria-controls="clienteDetails">
            Ver / Ocultar Detalles
        </button>

        <!-- Collapsible Section -->
        <div class="collapse" id="clienteDetails">
            <div class="row">
                <div class="col-lg-6 form-group">
                    <MaterialInput type="text" name="cliente_nombre" id="cliente_nombre" label="Nombre del Cliente"
                        v-model="form.cliente_nombre" />
                </div>
                <div class="col-lg-6 form-group">
                    <MaterialInput type="text" name="cliente_dni" id="cliente_dni" label="DNI"
                        v-model="form.cliente_dni" />
                </div>
                <div class="col-lg-6 form-group">
                    <MaterialInput type="text" name="cliente_localidad" id="cliente_localidad" label="Localidad"
                        v-model="form.cliente_localidad" />
                </div>
                <div class="col-lg-6 form-group">
                    <MaterialInput type="text" name="cliente_provincia" id="cliente_provincia" label="Provincia"
                        v-model="form.cliente_provincia" />
                </div>
                <div class="col-lg-6 form-group">
                    <MaterialInput type="text" name="cliente_direccion" id="cliente_direccion" label="Dirección"
                        v-model="form.cliente_direccion" />
                </div>
                <div class="col-lg-6 form-group">
                    <MaterialInput type="text" name="cliente_cp" id="cliente_cp" label="Código Postal"
                        v-model="form.cliente_cp" />
                </div>
                <div class="col-lg-6 form-group">
                    <MaterialInput type="text" name="cliente_telefono" id="cliente_telefono" label="Teléfono"
                        v-model="form.cliente_telefono" />
                </div>
                <div class="col-lg-6 form-group">
                    <MaterialInput type="text" name="cliente_email" id="cliente_email" label="Email"
                        v-model="form.cliente_email" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import MaterialInput from '@/Components/MaterialInput.vue';

export default {
    components: {
        MaterialInput
    },
    props: {
        clientes: {
            type: Array,
            required: true
        },
        form: {
            type: Object,
            required: true
        }
    },
    watch: {
        // Watch for changes in cliente_id and sync details
        'form.cliente_id': {
            immediate: true,
            handler(newId) {
                if (!newId) return;

                const cliente = this.clientes.find(c => c.id === newId);
                if (cliente) {
                    this.form.cliente_nombre = [
                        cliente.nombre,
                        cliente.apellido_1,
                        cliente.apellido_2
                    ].filter(Boolean).join(' ');
                    this.form.cliente_dni = cliente.dni || '';
                    this.form.cliente_localidad = cliente.localidad || '';
                    this.form.cliente_provincia = cliente.provincia || '';
                    this.form.cliente_direccion = cliente.direccion || '';
                    this.form.cliente_cp = cliente.cp || '';
                    this.form.cliente_telefono = cliente.telefono_fijo || cliente.telefono_movil || '';
                    this.form.cliente_email = cliente.email || '';
                }
            }
        }
    }
}
</script>
