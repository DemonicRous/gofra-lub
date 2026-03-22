<template>
    <app-layout>
        <Head title="Вход в аккаунт" />

        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-6">Вход в аккаунт</h2>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Email или Никнейм
                        </label>
                        <input
                            v-model="form.login"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.login }"
                            placeholder="example@mail.ru или username"
                        />
                        <p v-if="form.errors.login" class="text-red-500 text-xs mt-1">{{ form.errors.login }}</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Пароль</label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="form.remember" class="h-4 w-4 text-blue-600" />
                            <span class="ml-2 text-sm text-gray-600">Запомнить меня</span>
                        </label>
                        <Link :href="route('password.request')" class="text-sm text-blue-600 hover:text-blue-500">
                            Забыли пароль?
                        </Link>
                    </div>

                    <div v-if="generalError" class="mb-4 text-red-500 text-sm text-center">{{ generalError }}</div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Вход...' : 'Войти' }}
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <Link :href="route('register')" class="text-sm text-blue-600 hover:text-blue-500">
                        Нет аккаунта? Зарегистрироваться
                    </Link>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from "@/Layouts/AppLayout.vue";

const page = usePage()
const generalError = computed(() => page.props.errors?.login || page.props.errors?.email)

const form = useForm({
    login: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'))
}
</script>
