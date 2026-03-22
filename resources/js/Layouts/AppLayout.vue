<script setup>

import AppHeader from "@/Components/Navigation/AppHeader.vue";
import AppFooter from "@/Components/Navigation/AppFooter.vue";
</script>

<template>

    <app-header/>

    <slot/>

    <app-footer/>

</template>

<style scoped>

</style>
