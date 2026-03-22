<template>
    <AppLayout>
        <Head title="Управление пользователями" />

        <div class="container mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h1 class="text-2xl font-bold mb-6">Управление пользователями</h1>

                <!-- Статистика -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-sm text-blue-600">Всего</div>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-sm text-yellow-600">Ожидают</div>
                        <div class="text-2xl font-bold">{{ stats.pending }}</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-sm text-green-600">Одобрены</div>
                        <div class="text-2xl font-bold">{{ stats.approved }}</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-sm text-purple-600">Подтверждены</div>
                        <div class="text-2xl font-bold">{{ stats.verified }}</div>
                    </div>
                </div>

                <!-- Фильтры -->
                <div class="mb-6 flex flex-wrap gap-4">
                    <select v-model="filters.status" @change="applyFilters" class="px-3 py-2 border rounded-md">
                        <option value="pending">Ожидают одобрения</option>
                        <option value="approved">Одобренные</option>
                        <option value="all">Все пользователи</option>
                    </select>

                    <input
                        v-model="filters.search"
                        @input="debouncedSearch"
                        type="text"
                        placeholder="Поиск по ФИО, email, никнейму..."
                        class="flex-1 px-3 py-2 border rounded-md"
                    />

                    <button @click="bulkApprove" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                        Одобрить выбранных
                    </button>

                    <button @click="bulkDelete" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
                        Удалить выбранных
                    </button>
                </div>

                <!-- Таблица пользователей -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                        <tr>
                            <th class="px-4 py-2">
                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
                            </th>
                            <th class="px-4 py-2 text-left">ФИО</th>
                            <th class="px-4 py-2 text-left">Никнейм</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Должность</th>
                            <th class="px-4 py-2 text-left">Отдел</th>
                            <th class="px-4 py-2 text-left">Роль</th>
                            <th class="px-4 py-2 text-left">Статус</th>
                            <th class="px-4 py-2 text-left">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="user in users.data" :key="user.id">
                            <td class="px-4 py-2">
                                <input type="checkbox" v-model="selectedUsers" :value="user.id" />
                            </td>
                            <td class="px-4 py-2">{{ user.full_name }}</td>
                            <td class="px-4 py-2">{{ user.nickname }}</td>
                            <td class="px-4 py-2">{{ user.email }}</td>
                            <td class="px-4 py-2">{{ user.position }}</td>
                            <td class="px-4 py-2">{{ user.department }}</td>
                            <td class="px-4 py-2">
                                <select
                                    :value="user.role"
                                    @change="changeRole(user.id, $event.target.value)"
                                    class="text-sm border rounded px-2 py-1"
                                >
                                    <option value="user">Пользователь</option>
                                    <option value="manager">Менеджер</option>
                                    <option value="admin">Администратор</option>
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                    <span v-if="user.approved_at" class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        Одобрен
                                    </span>
                                <span v-else-if="user.email_verified_at" class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                        Ожидает
                                    </span>
                                <span v-else class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                        Email не подтвержден
                                    </span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex gap-2">
                                    <button
                                        v-if="!user.approved_at && user.email_verified_at"
                                        @click="approveUser(user.id)"
                                        class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm"
                                    >
                                        Одобрить
                                    </button>
                                    <button
                                        @click="deleteUser(user.id)"
                                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm"
                                    >
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div class="mt-4">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/UI/Pagination.vue'

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object
})

const selectedUsers = ref([])
const selectAll = computed({
    get: () => selectedUsers.value.length === props.users.data?.length,
    set: (value) => {
        if (value) {
            selectedUsers.value = props.users.data.map(u => u.id)
        } else {
            selectedUsers.value = []
        }
    }
})

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedUsers.value = []
    } else {
        selectedUsers.value = props.users.data.map(u => u.id)
    }
}

const applyFilters = () => {
    router.get(route('admin.users'), props.filters, {
        preserveState: true
    })
}

const debouncedSearch = () => {
    setTimeout(() => {
        applyFilters()
    }, 300)
}

const approveUser = (id) => {
    router.post(route('admin.users.approve', id), {}, {
        preserveScroll: true
    })
}

const deleteUser = (id) => {
    if (confirm('Вы уверены, что хотите удалить этого пользователя?')) {
        router.delete(route('admin.users.destroy', id), {
            preserveScroll: true
        })
    }
}

const changeRole = (id, role) => {
    router.post(route('admin.users.assign-role', id), { role }, {
        preserveScroll: true
    })
}

const bulkApprove = () => {
    if (selectedUsers.value.length === 0) {
        alert('Выберите пользователей')
        return
    }
    if (confirm(`Одобрить ${selectedUsers.value.length} пользователей?`)) {
        router.post(route('admin.users.bulk-approve'), { user_ids: selectedUsers.value }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedUsers.value = []
            }
        })
    }
}

const bulkDelete = () => {
    if (selectedUsers.value.length === 0) {
        alert('Выберите пользователей')
        return
    }
    if (confirm(`Удалить ${selectedUsers.value.length} пользователей? Это действие необратимо.`)) {
        router.delete(route('admin.users.bulk-destroy'), {
            data: { user_ids: selectedUsers.value },
            preserveScroll: true,
            onSuccess: () => {
                selectedUsers.value = []
            }
        })
    }
}
</script>
