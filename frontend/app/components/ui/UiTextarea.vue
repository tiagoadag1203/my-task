<script setup>
defineOptions({ inheritAttrs: false })

const model = defineModel({ type: String, default: '' })
const props = defineProps({
  id: { type: String, default: '' },
  label: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  rows: { type: Number, default: 4 },
  disabled: Boolean,
  required: Boolean,
})

const generatedId = useId()
const inputId = computed(() => props.id || `textarea-${generatedId}`)
const descriptionId = computed(() => `${inputId.value}-description`)
</script>

<template>
  <div>
    <label v-if="label" class="app-label" :for="inputId">
      {{ label }}<span v-if="required" aria-hidden="true"> *</span>
    </label>
    <textarea
      :id="inputId"
      v-model="model"
      :rows="rows"
      :disabled="disabled"
      :required="required"
      :aria-invalid="Boolean(error)"
      :aria-describedby="hint || error ? descriptionId : undefined"
      class="app-control resize-y px-3 py-2.5"
      :class="{ '!border-red-600': error }"
      v-bind="$attrs"
    />
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
