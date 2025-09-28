<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div
    class="flex min-h-screen items-center justify-center bg-gray-100"
  >
    <div class="flex flex-col items-center w-full sm:max-w-md px-6">
      <!-- Logo -->
      <div class="mb-6">
        <Link href="/">
          <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
        </Link>
      </div>

      <!-- Card konten -->
      <div
        class="w-full overflow-hidden bg-white px-6 py-4 shadow-md rounded-lg"
      >
        <slot />
      </div>
    </div>
  </div>
</template>

<style>
form input{
    height: 30px;
    padding: 2px 6px;
}
</style>