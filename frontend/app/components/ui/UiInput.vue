<script setup>
defineOptions({ inheritAttrs: false })

const model = defineModel({ type: [String, Number], default: '' })
const props = defineProps({
  id: { type: String, default: '' },
  label: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  type: { type: String, default: 'text' },
  disabled: Boolean,
  required: Boolean,
})

const generatedId = useId()
const inputId = computed(() => props.id || `input-${generatedId}`)
const descriptionId = computed(() => `${inputId.value}-description`)
</script>

<template>
  <div>
    <label v-if="label" class="app-label" :for="inputId">
      {{ label }}<span v-if="required" aria-hidden="true"> *</span>
    </label>
    <div class="relative">
      <span v-if="$slots.leading" class="absolute inset-y-0 left-3 flex items-center text-[var(--app-text-muted)]">
        <slot name="leading" />
      </span>
      <input
        :id="inputId"
        v-model="model"
        :type="type"
        :disabled="disabled"
        :required="required"
        :aria-invalid="Boolean(error)"
        :aria-describedby="hint || error ? descriptionId : undefined"
        class="app-control px-3"
        :class="{ 'pl-10': $slots.leading, 'pr-10': $slots.trailing, '!border-red-600': error }"
        v-bind="$attrs"
      >
      <span v-if="$slots.trailing" class="absolute inset-y-0 right-3 flex items-center text-[var(--app-text-muted)]">
        <slot name="trailing" />
      </span>
    </div>
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
