<script setup>
defineProps({
  name: { type: String, required: true },
})
</script>

<template>
  <svg
    class="size-5"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
  >
    <template v-if="name === 'home'">
      <path d="m3 11 9-8 9 8" />
      <path d="M5 10v10h14V10M9 20v-6h6v6" />
    </template>
    <template v-else-if="name === 'tasks'">
      <path d="m4 7 2 2 4-4M4 17l2 2 4-4M13 7h7M13 17h7" />
    </template>
    <template v-else-if="name === 'projects'">
      <rect x="3" y="4" width="18" height="16" rx="2" />
      <path d="M8 4v16M8 9h13" />
    </template>
    <template v-else-if="name === 'users'">
      <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
      <circle cx="9" cy="7" r="4" />
      <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
    </template>
    <template v-else-if="name === 'more'">
      <circle cx="12" cy="5" r="1.4" fill="currentColor" stroke="none" />
      <circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none" />
      <circle cx="12" cy="19" r="1.4" fill="currentColor" stroke="none" />
    </template>
  </svg>
</template>
