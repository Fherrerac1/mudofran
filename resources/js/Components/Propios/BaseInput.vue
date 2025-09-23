<template>
  <div class="mb-3">
    <label v-if="label" :for="id" class="form-label ms-0 mb-0 fw-bold">
      {{ label }}
      <span v-if="isRequired" class="text-danger">*</span>
    </label>

    <!-- Textarea dinámico -->
    <template v-if="type === 'textarea'">
      <textarea
        ref="inputRef"
        :id="id"
        :name="name"
        class="form-control shadow-sm p-3"
        :class="inputClass"
        :placeholder="placeholder"
        :maxlength="maxlength"
        :required="isRequired"
        :readonly="readonly"
        :disabled="disabled"
        :rows="rows"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
      ></textarea>
    </template>

    <!-- Input normal -->
    <template v-else>
      <input
        ref="inputRef"
        :id="id"
        :name="name"
        :type="type"
        class="form-control shadow-sm p-3"
        :class="inputClass"
        :placeholder="placeholder"
        :maxlength="maxlength"
        :required="isRequired"
        :readonly="readonly"
        :disabled="disabled"
        :pattern="pattern || null"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
      />
    </template>

    <div v-if="error" class="invalid-feedback d-block mt-1">
      {{ error }}
    </div>
  </div>
</template>

<script>

export default {
  name: 'BaseInput',
  props: {
    id: { type: String, required: true },
    name: { type: String, default: '' },
    label: { type: String, default: '' },
    modelValue: { type: String, default: '' },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    isRequired: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    maxlength: { type: [String, Number], default: '' },
    error: { type: Boolean, default: false },
    errorMessages: { type: String, default: '' },
    pattern: { type: String, default: '' },
    inputClass: { type: String, default: '' },
    rows: { type: [String, Number], default: 3 }
  }
};
</script>
