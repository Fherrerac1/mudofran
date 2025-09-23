<template>
    <div class="card p-3 shadow-sm mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0">Métodos de Pago</h5>
            <MaterialButton data-bs-toggle="modal" data-bs-target="#addMethodModal" class="btn btn-sm ">
                <i class="fas fa-plus me-2"></i> Añadir método de pago
            </MaterialButton>
        </div>

        <div class="list-group">
            <!-- Cabecera -->
            <div class="list-group-item d-flex fw-bold bg-light border-bottom">
                <div class="col-4">Concepto</div>
                <div class="col-3">Banco</div>
                <div class="col-4">Número de Cuenta</div>
                <div class="col-1">Acciones</div>
            </div>

            <!-- Datos -->
            <div class="list-group-item d-flex align-items-center" v-for="metodo in methods" :key="metodo.id">
                <div class="col-4">{{ metodo.concepto }}</div>
                <div class="col-3">{{ metodo.bank_name }}</div>
                <div class="col-4">{{ formatIBAN(metodo.metodo) }}</div>
                <div class="col-1 text-end">

                    <div class="buttons-action d-flex gap-3">
                        <MaterialButton class="btn-sm py-2 btn-edit" title="Editar método de pago"
                            data-bs-toggle="modal" data-bs-target="#editMethodModal" @click="openEditModal(metodo)">
                            <i class="material-icons">edit</i>
                        </MaterialButton>
                        <MaterialButton class="btn-sm py-2 btn-delete" title="Eliminar método de pago"
                            @click="confirmDelete(`/metodo/destroy/${metodo.id}`)">
                            <i class="material-icons">delete</i>
                        </MaterialButton>

                    </div>
                </div>
            </div>

            <div v-if="methods.length === 0" class="list-group-item text-center text-muted">
                No hay métodos de pago registrados.
            </div>
        </div>
    </div>

    <!-- MODAL REGISTRAR MÉTODO DE PAGO -->
    <BootstrapModal id='addMethodModal' :-modal-size="'modal-md modal-dialog-centered'"
        :title="'Añadir Método de Pago'">
        <form @submit.prevent="submit" method="POST" class="p-2">

            <div class="mb-4">
                <BaseInput id="cuenta" label="Número de cuenta bancaria" v-model="ibanModel"
                    placeholder="ES00 0000 0000 00 0000000000" :is-required="true" :error="form.errors.metodo"
                    :maxlength="29" />

            </div>

            <div class="mb-4">
                <BaseInput id="bank_name" label="Nombre del Banco" v-model="form.bank_name"
                    :placeholder="'Ej: Banco Santander, BBVA, etc.'" :is-required="true"
                    :error="form.errors.bank_name" />
            </div>

            <div class="mb-4">
                <BaseInput id="concepto" label="Concepto / Área de negocio" v-model="form.concepto"
                    :placeholder="'Ej: Marketing, Logística...'" :is-required="true" :error="form.errors.concepto" />
            </div>


            <div class="d-flex justify-end">
                <MaterialButton type="submit" class=" align-items-center gap-2 w-100 text-center"
                    :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                    <div class="item-metodo d-flex justify-content-center gap-2 align-items-center">
                        <i class="material-icons" style="font-size: 18px;">credit_card</i>
                        <span clas>Registrar Método de Pago</span>
                    </div>
                </MaterialButton>
            </div>

        </form>
    </BootstrapModal>


    <!-- MODAL EDITAR -->
    <BootstrapModal id='editMethodModal' :-modal-size="'modal-md modal-dialog-centered'"
        :title="'Editar Método de Pago'">
        <form @submit.prevent="updateMethod" method="POST" class="p-2">

            <div class="mb-4">
                <BaseInput id="edit_metodo" label="Número de cuenta bancaria" v-model="editIbanModel"
                    placeholder="ES00 0000 0000 00 0000000000" :is-required="true" :error="editForm.errors.metodo"
                    :maxlength="29" />


            </div>

            <div class="mb-4">
                <BaseInput id="edit_bank_name" label="Nombre del Banco" v-model="editForm.bank_name"
                    :placeholder="'Ej: Banco Santander, BBVA, etc.'" :is-required="true"
                    :error="editForm.errors.bank_name" />
            </div>

            <div class="mb-4">
                <BaseInput id="edit_concepto" label="Concepto / Área de negocio" v-model="editForm.concepto"
                    :placeholder="'Ej: Marketing, Logística...'" :is-required="true"
                    :error="editForm.errors.concepto" />
            </div>

            <MaterialButton type="submit" class="d-flex align-items-center gap-2 w-100 justify-content-center"
                :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing">
                <i class="material-icons">save</i>
                <span>Actualizar Método de Pago</span>
            </MaterialButton>

        </form>
    </BootstrapModal>


</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import BootstrapModal from '@/Components/BootstrapModal.vue';
import MaterialInput from '@/Components/MaterialInput.vue';
import MaterialButton from '@/Components/MaterialButton.vue';
import BaseInput from '@/Components/Propios/BaseInput.vue';

const props = defineProps({
    methods: { type: Array, required: false },
});

const form = useForm({
    metodo: '',
    bank_name: '',
    concepto: '',
});

const editForm = useForm({
    metodo: '',
    bank_name: '',
    concepto: '',
});

const selectedMethod = ref(null);

const ibanModel = computed({
    get: () => form.metodo,
    set: (val) => { form.metodo = formatIBAN(val); }
});

const editIbanModel = computed({
    get: () => editForm.metodo,
    set: (val) => { editForm.metodo = formatIBAN(val); }
});

const formatIBAN = (value = '') => {
    // 1) limpiar y mayúsculas
    let clean = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

    // 2) separar prefijo y resto
    let letters = clean.slice(0, 2).replace(/[^A-Z]/g, '');
    let digits = clean.slice(2).replace(/[^0-9]/g, '');

    // 3) forzar prefijo ES
    letters = 'ES';

    // 4) limitar a 22 dígitos
    digits = digits.slice(0, 22);

    // 5) construir y agrupar cada 4 desde el inicio
    const raw = letters + digits;                 // ej: ES1111111111111111111234
    return raw.replace(/(.{4})/g, '$1 ').trim();  // -> ES11 1111 1111 1111 1111 1234
};





const isValidIBAN = (value) =>
    /^ES\d{22}$/.test(value.replace(/\s+/g, '').toUpperCase());

const normalizeIBAN = (iban = '') => iban.replace(/\s+/g, '').toUpperCase();





const updateMethod = () => {
    // Validar IBAN antes de enviar
    if (!isValidIBAN(editForm.metodo)) {
        Swal.fire({
            icon: 'warning',
            title: 'Número de cuenta inválido',
            text: 'Debe contener exactamente 24 caracteres alfanuméricos con el formato correcto.',
            confirmButtonText: 'Corregir'
        });
        return;
    }

    Swal.fire({
        title: 'Actualizando...',
        html: 'Estamos guardando los cambios. Por favor, espera.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    editForm
        .transform(data => ({
            ...data,
            metodo: normalizeIBAN(data.metodo),
        }))
        .post(route('perfil.update_metodo', selectedMethod.value.id), {
            onSuccess: () => {
                Swal.fire({
                    title: 'Método actualizado con éxito',
                    text: 'La información del método de pago ha sido guardada correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => location.reload());
            },
            onError: (errors) => {
                const errorMessage = Object.values(errors).flat().join('\n') || 'Ocurrió un error al actualizar el método.';
                Swal.fire({
                    title: 'Error al actualizar',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        });
};



const submit = () => {
    if (!isValidIBAN(form.metodo)) {
        Swal.fire({
            icon: 'warning',
            title: 'Número de cuenta inválido',
            text: 'Debe contener exactamente 24 caracteres alfanuméricos con el formato correcto.',
            confirmButtonText: 'Corregir'
        });
        return;
    }


    // Mostrar loading mientras se guarda el nuevo método
    Swal.fire({
        title: 'Registrando...',
        html: 'Estamos guardando el nuevo método de pago. Por favor, espera.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    form
        .transform(data => ({
            ...data,
            metodo: normalizeIBAN(data.metodo),
        }))
        .post(route('perfil.store_metodo'), {
            onSuccess: () => {
                Swal.fire({
                    title: 'Método registrado con éxito!',
                    text: 'El nuevo método de pago ha sido guardado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => location.reload());
            },
            onError: (errors) => {
                const errorMessage = Object.values(errors).flat().join('\n') || 'Ocurrió un error al registrar el método.';
                Swal.fire({
                    title: 'Error al guardar',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            }
        });
};


const confirmDelete = (url) => {
    Swal.fire({
        title: '¿Seguro?',
        text: 'Esta acción es irreversible.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, borrar',
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
};

const openEditModal = (metodo) => {
    selectedMethod.value = metodo;

    editForm.metodo = metodo.metodo;
    editForm.bank_name = metodo.bank_name;
    editForm.concepto = metodo.concepto;

    // AÑADIR CLASE 'is-filled' MANUALMENTE
    setTimeout(() => {
        const fields = ['edit_metodo', 'edit_bank_name', 'edit_concepto'];
        fields.forEach(id => {
            const inputGroup = document.getElementById(id)?.closest('.input-group');
            const input = document.getElementById(id);

            if (input && input.value) {
                inputGroup?.classList.add('is-filled');
            } else {
                inputGroup?.classList.remove('is-filled');
            }
        });
    }, 50);
};


</script>


<style scoped>
/* Para que se vea más suave */
.input-group input::placeholder {
    opacity: 0.5 !important;
    font-style: italic;
}
</style>