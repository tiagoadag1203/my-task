<script setup>
defineOptions({ inheritAttrs: false })

const model = defineModel({ type: [String, Number, Boolean], default: '' })
const props = defineProps({
  id: { type: String, default: '' },
  label: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  placeholder: { type: String, default: 'Selecione' },
  options: { type: Array, default: () => [] },
  disabled: Boolean,
  required: Boolean,
})

const generatedId = useId()
const inputId = computed(() => props.id || `select-${generatedId}`)
const descriptionId = computed(() => `${inputId.value}-description`)
const optionValue = (option) => (typeof option === 'object' ? option.value : option)
const optionLabel = (option) => (typeof option === 'object' ? option.label : option)
</script>

<template>
  <div>
    <label v-if="label" class="app-label" :for="inputId">
      {{ label }}<span v-if="required" aria-hidden="true"> *</span>
    </label>
    <select
      :id="inputId"
      v-model="model"
      :disabled="disabled"
      :required="required"
      :aria-invalid="Boolean(error)"
      :aria-describedby="hint || error ? descriptionId : undefined"
      class="app-control appearance-none px-3"
      :class="{ '!border-red-600': error }"
      v-bind="$attrs"
    >
      <option value="" disabled>{{ placeholder }}</option>
      <option v-for="option in options" :key="String(optionValue(option))" :value="optionValue(option)">
        {{ optionLabel(option) }}
      </option>
    </select>
    <p
      v-if="error || hint"
      :id="descriptionId"
      class="mt-1.5 text-xs"
      :class="error ? 'text-red-600 dark:text-red-400' : 'text-[var(--app-text-muted)]'"
    >
      {{ error || hint }}
    </p>
  </div>
</template>
