<template>
    <app-layout>
        <Head title="Подтверждение пароля" />

        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-2">
                    Подтверждение пароля
                </h2>

                <p class="text-center text-gray-600 mb-6">
                    Пожалуйста, подтвердите ваш пароль перед продолжением
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Пароль
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.password }"
                            placeholder="Введите ваш пароль"
                        />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Проверка...' : 'Подтвердить' }}
                    </button>
                </form>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const form = useForm({
    password: ''
})

const submit = () => {
    form.post(route('password.confirm'))
}
</script>
