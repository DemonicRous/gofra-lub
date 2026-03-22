<template>
    <app-layout>
        <Head title="Сброс пароля" />

        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-6">
                    Сброс пароля
                </h2>

                <p class="text-center text-gray-600 mb-6">
                    Придумайте новый пароль для вашего аккаунта
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="example@mail.ru"
                        />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Новый пароль
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.password }"
                            placeholder="Минимум 8 символов"
                        />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        <p class="text-gray-500 text-xs mt-1">Пароль должен содержать минимум 8 символов</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Подтверждение пароля
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.password_confirmation }"
                            placeholder="Повторите пароль"
                        />
                        <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Сохранение...' : 'Сохранить пароль' }}
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <Link :href="route('login')" class="text-sm text-blue-600 hover:text-blue-500">
                        Вернуться ко входу
                    </Link>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    token: {
        type: String,
        required: true
    }
})

const form = useForm({
    email: '',
    password: '',
    password_confirmation: '',
    token: props.token,
})

const submit = () => {
    form.post(route('password.update'))
}
</script>
