<template>
  <div>
    <!-- Header / Navbar -->
    <header>
      <!-- ... -->
    </header>

    <!-- Page Content -->
    <main>
      <slot />
    </main>

    <!-- Chat Widget -->
    <ChatWidget />
  </div>
</template>

<script setup>
import ChatWidget from '@/Components/ChatWidget.vue';
</script>
