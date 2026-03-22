<script setup>

import {Link} from "@inertiajs/vue3";
</script>

<template>
    <header class="w-full flex justify-between items-center">
        <div class="text-2xl">
            Логотип
        </div>
        <div class="">
            <span>Ссылка 1</span>
            <span>Ссылка 2</span>
            <span>Ссылка 3</span>
        </div>
        <div class="">
            <Link :href="route('login')">Вход</Link>
            <Link :href="route('register')">Регистрация</Link>
        </div>
    </header>
</template>

<style scoped>

</style>
