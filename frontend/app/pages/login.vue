<script setup>
definePageMeta({
  layout: false,
  middleware: 'guest',
})

useHead({
  title: 'Entrar | MyTask',
})

const auth = useAuthStore()
const form = reactive({
  email: '',
  password: '',
})
const errors = reactive({
  email: '',
  password: '',
})
const submitError = ref('')
const isSubmitting = ref(false)

function validate() {
  errors.email = form.email.trim() ? '' : 'Informe seu e-mail.'
  errors.password = form.password ? '' : 'Informe sua senha.'
  return !errors.email && !errors.password
}

async function submit() {
  if (isSubmitting.value) return

  submitError.value = ''
  if (!validate()) return

  isSubmitting.value = true

  try {
    await auth.login({
      email: form.email.trim(),
      password: form.password,
    })
    await navigateTo('/dashboard')
  } catch (error) {
    const apiErrors = error?.data?.errors ?? {}
    errors.email = apiErrors.email?.[0] ?? ''
    errors.password = apiErrors.password?.[0] ?? ''
    submitError.value = error?.data?.message ?? 'Não foi possível entrar. Tente novamente.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <AuthShell title="Entre na sua conta" description="Continue organizando seu trabalho com clareza.">
    <form class="space-y-5" novalidate @submit.prevent="submit">
      <UiInput
        id="email"
        v-model="form.email"
        label="E-mail"
        type="email"
        name="email"
        autocomplete="email"
        required
        :error="errors.email"
      />
      <UiInput
        id="password"
        v-model="form.password"
        label="Senha"
        type="password"
        name="password"
        autocomplete="current-password"
        required
        :error="errors.password"
      />

      <p
        v-if="submitError"
        class="rounded-xl border-2 border-black bg-neutral-100 px-4 py-3 text-sm font-medium"
        role="alert"
      >
        {{ submitError }}
      </p>

      <UiButton type="submit" variant="cta" block :loading="isSubmitting">
        {{ isSubmitting ? 'Entrando…' : 'Entrar' }}
      </UiButton>
    </form>

    <p class="mt-7 text-center text-neutral-600">
      Ainda não tem conta?
      <NuxtLink
        to="/register"
        class="font-semibold text-black underline decoration-accent decoration-2 underline-offset-4"
      >
        Criar conta
      </NuxtLink>
    </p>
  </AuthShell>
</template>
