<script setup>
definePageMeta({
  layout: false,
  middleware: 'guest',
})

useHead({
  title: 'Criar conta | MyTask',
})

const auth = useAuthStore()
const form = reactive({
  nickname: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})
const errors = reactive({
  nickname: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})
const submitError = ref('')
const isSubmitting = ref(false)

function validate() {
  errors.nickname = form.nickname.trim() ? '' : 'Informe como você quer ser chamado.'
  errors.email = form.email.trim() ? '' : 'Informe seu e-mail.'
  errors.password = form.password.length >= 8 ? '' : 'A senha deve ter pelo menos 8 caracteres.'
  errors.passwordConfirmation =
    form.passwordConfirmation === form.password ? '' : 'As senhas não coincidem.'

  return !Object.values(errors).some(Boolean)
}

async function submit() {
  if (isSubmitting.value) return

  submitError.value = ''
  if (!validate()) return

  isSubmitting.value = true

  try {
    await auth.register({
      nickname: form.nickname.trim(),
      email: form.email.trim(),
      password: form.password,
    })
    await navigateTo('/dashboard')
  } catch (error) {
    const apiErrors = error?.data?.errors ?? {}
    errors.nickname = apiErrors.nickname?.[0] ?? ''
    errors.email = apiErrors.email?.[0] ?? ''
    errors.password = apiErrors.password?.[0] ?? ''
    submitError.value = error?.data?.message ?? 'Não foi possível criar sua conta. Tente novamente.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <AuthShell title="Crie sua conta" description="Comece a transformar planos em progresso.">
    <form class="space-y-5" novalidate @submit.prevent="submit">
      <UiInput
        id="nickname"
        v-model="form.nickname"
        label="Como quer ser chamado?"
        name="nickname"
        autocomplete="nickname"
        maxlength="50"
        required
        :error="errors.nickname"
      />
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
        autocomplete="new-password"
        minlength="8"
        required
        hint="Use pelo menos 8 caracteres."
        :error="errors.password"
      />
      <UiInput
        id="password-confirmation"
        v-model="form.passwordConfirmation"
        label="Confirme a senha"
        type="password"
        name="password_confirmation"
        autocomplete="new-password"
        required
        :error="errors.passwordConfirmation"
      />

      <p
        v-if="submitError"
        class="rounded-xl border-2 border-black bg-neutral-100 px-4 py-3 text-sm font-medium"
        role="alert"
      >
        {{ submitError }}
      </p>

      <UiButton type="submit" variant="cta" block :loading="isSubmitting">
        {{ isSubmitting ? 'Criando conta…' : 'Criar conta' }}
      </UiButton>
    </form>

    <p class="mt-7 text-center text-neutral-600">
      Já tem uma conta?
      <NuxtLink
        to="/login"
        class="font-semibold text-black underline decoration-accent decoration-2 underline-offset-4"
      >
        Entrar
      </NuxtLink>
    </p>
  </AuthShell>
</template>
