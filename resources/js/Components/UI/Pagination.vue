<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-between gap-4">
        <div class="text-sm text-gray-700">
            Показано с <span class="font-medium">{{ from }}</span>
            по <span class="font-medium">{{ to }}</span>
            из <span class="font-medium">{{ total }}</span> записей
        </div>

        <div class="flex items-center gap-1">
            <!-- Первая страница -->
            <Link
                v-if="hasPreviousPages"
                :href="firstPageUrl"
                class="px-3 py-2 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 transition-colors"
                title="Первая страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </Link>

            <!-- Предыдущая страница -->
            <Link
                v-if="prevPageUrl"
                :href="prevPageUrl"
                class="px-3 py-2 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 transition-colors"
                title="Предыдущая страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </Link>

            <!-- Номера страниц -->
            <template v-for="page in visiblePages" :key="page">
                <Link
                    v-if="page !== '...'"
                    :href="getPageUrl(page)"
                    :class="[
                        'px-3 py-2 rounded-md text-sm font-medium transition-colors min-w-[40px] text-center',
                        page === currentPage
                            ? 'bg-blue-600 text-white cursor-default'
                            : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                    ]"
                >
                    {{ page }}
                </Link>
                <span
                    v-else
                    class="px-3 py-2 text-gray-500"
                >
                    ...
                </span>
            </template>

            <!-- Следующая страница -->
            <Link
                v-if="nextPageUrl"
                :href="nextPageUrl"
                class="px-3 py-2 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 transition-colors"
                title="Следующая страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </Link>

            <!-- Последняя страница -->
            <Link
                v-if="hasNextPages"
                :href="lastPageUrl"
                class="px-3 py-2 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 transition-colors"
                title="Последняя страница"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                </svg>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    links: {
        type: Array,
        required: true,
        default: () => []
    },
    from: {
        type: Number,
        default: null
    },
    to: {
        type: Number,
        default: null
    },
    total: {
        type: Number,
        default: null
    }
})

// Определяем текущую страницу и общее количество страниц
const currentPage = computed(() => {
    const activeLink = props.links.find(link => link.active)
    if (activeLink && activeLink.label) {
        const match = activeLink.label.match(/\d+/)
        return match ? parseInt(match[0]) : 1
    }
    return 1
})

const lastPage = computed(() => {
    const lastLink = props.links[props.links.length - 2]
    if (lastLink && lastLink.label) {
        const match = lastLink.label.match(/\d+/)
        return match ? parseInt(match[0]) : 1
    }
    return 1
})

// URL для навигации
const prevPageUrl = computed(() => {
    const prevLink = props.links.find(link => link.label === '&laquo; Previous' || link.label === '‹')
    return prevLink?.url || null
})

const nextPageUrl = computed(() => {
    const nextLink = props.links.find(link => link.label === 'Next &raquo;' || link.label === '›')
    return nextLink?.url || null
})

const firstPageUrl = computed(() => {
    const firstLink = props.links[0]
    return firstLink?.url || null
})

const lastPageUrl = computed(() => {
    const lastLink = props.links[props.links.length - 1]
    return lastLink?.url || null
})

const hasPreviousPages = computed(() => {
    return currentPage.value > 1
})

const hasNextPages = computed(() => {
    return currentPage.value < lastPage.value
})

// Формируем видимые страницы (с ...)
const visiblePages = computed(() => {
    const current = currentPage.value
    const last = lastPage.value
    const delta = 2 // Количество страниц с каждой стороны от текущей

    const range = []
    const rangeWithDots = []
    let l

    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
            range.push(i)
        }
    }

    range.forEach((i) => {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1)
            } else if (i - l !== 1) {
                rangeWithDots.push('...')
            }
        }
        rangeWithDots.push(i)
        l = i
    })

    return rangeWithDots
})

// Получаем URL для конкретной страницы
const getPageUrl = (page) => {
    // Ищем ссылку с номером страницы
    const link = props.links.find(l => l.label === String(page))
    return link?.url || null
}
</script>
