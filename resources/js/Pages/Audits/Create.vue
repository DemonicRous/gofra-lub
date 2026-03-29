<!-- resources/js/Pages/Audits/Create.vue -->
<template>
    <AppLayout>
        <Head title="Создание аудита" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-24">
            <!-- Шапка -->
            <div class="sticky top-0 z-10 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 shadow-lg">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link :href="route('audits.index')" class="text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <h1 class="text-xl font-bold text-white">Новый выездной аудит</h1>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="px-4 py-4 space-y-4">
                <!-- Основная информация -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Основная информация
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Название аудита <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.title" type="text"
                                   class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   :class="form.errors.title ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                   placeholder="Например: Аудит линии №3">
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Тип аудита
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="type in auditTypes" :key="type.value" type="button"
                                        @click="form.audit_type = type.value"
                                        :class="[
                                        'p-3 rounded-lg border-2 text-center transition text-sm',
                                        form.audit_type === type.value
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-600'
                                            : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'
                                    ]">
                                    {{ type.label }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Ответственный
                            </label>
                            <select v-model="form.assigned_to"
                                    class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Назначить сотрудника</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.full_name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Информация о клиенте -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Информация о клиенте
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Название клиента
                            </label>
                            <input v-model="form.client_name" type="text"
                                   class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="ООО «Ромашка»">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Контактное лицо
                            </label>
                            <input v-model="form.client_contact" type="text"
                                   class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="Иванов Иван +7(999)123-45-67">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Адрес объекта
                            </label>
                            <textarea v-model="form.address" rows="2"
                                      class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                      placeholder="г. Москва, ул. Примерная, д. 1"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Название объекта/оборудования
                            </label>
                            <input v-model="form.object_name" type="text"
                                   class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="Линия розлива №3">
                        </div>
                    </div>
                </div>

                <!-- Дата и время -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Дата и время
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Дата аудита
                            </label>
                            <input v-model="form.audit_date" type="date"
                                   class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Время начала
                                </label>
                                <input v-model="form.start_time" type="time"
                                       class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Время окончания
                                </label>
                                <input v-model="form.end_time" type="time"
                                       class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Описание -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        Описание
                    </h2>
                    <textarea v-model="form.description" rows="4"
                              class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                              placeholder="Опишите цели и задачи аудита..."></textarea>
                </div>

                <!-- Кнопки -->
                <div class="flex gap-3 pt-4">
                    <Link :href="route('audits.index')"
                          class="flex-1 text-center px-4 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg">
                        Отмена
                    </Link>
                    <button type="submit"
                            class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition disabled:opacity-50"
                            :disabled="form.processing">
                        {{ form.processing ? 'Создание...' : 'Создать аудит' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    users: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] }
})

const auditTypes = [
    { value: 'measurement', label: 'Замеры' },
    { value: 'production_line', label: 'Производственная линия' },
    { value: 'quality_check', label: 'Проверка качества' },
    { value: 'consultation', label: 'Консультация' },
    { value: 'other', label: 'Другое' }
]

const form = useForm({
    title: '',
    description: '',
    client_name: '',
    client_contact: '',
    address: '',
    object_name: '',
    audit_type: 'measurement',
    audit_date: new Date().toISOString().split('T')[0],
    start_time: '',
    end_time: '',
    assigned_to: null,
    related_task_id: null,
    latitude: null,
    longitude: null
})

const submit = () => {
    form.post(route('audits.store'), {
        onSuccess: () => {
            // Автоматический редирект на список
        }
    })
}
</script>
