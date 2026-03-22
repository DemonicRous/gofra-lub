<script setup>

import {Link} from "@inertiajs/vue3";
</script>

<template>
    <header class="w-full flex justify-between items-center">
        <div class="text-2xl">
            Логотип
        </div>
        <div class=""></div>
        <div class="">
            <Link :href="route('')">Вход</Link>
            <Link>Регистрация</Link>
        </div>
    </header>
</template>

<style scoped>

</style>
