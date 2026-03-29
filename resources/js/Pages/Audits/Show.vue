<!-- resources/js/Pages/Audits/Show.vue -->
<template>
    <AppLayout>
        <Head :title="audit.title" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Шапка -->
            <div class="sticky top-0 z-10 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 shadow-lg">
                <div class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <Link :href="route('audits.index')" class="text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </Link>
                        <h1 class="text-xl font-bold text-white flex-1 truncate">{{ audit.title }}</h1>
                        <button v-if="audit.can_be_edited" @click="openEditModal"
                                class="bg-white/20 hover:bg-white/30 text-white p-2 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Статусная полоска -->
            <div class="h-2" :class="getStatusBarClass(audit.status)"></div>

            <div class="px-4 py-4 space-y-4 pb-8">
                <!-- Статус и тип -->
                <div class="flex flex-wrap gap-2">
                    <span :class="getStatusBadgeClass(audit.status)" class="px-3 py-1 rounded-full text-sm">
                        {{ audit.status_name }}
                    </span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm text-gray-700 dark:text-gray-300">
                        {{ audit.type_name }}
                    </span>
                </div>

                <!-- Основная информация -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Создатель:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ audit.creator_short || audit.creator_name }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Ответственный:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ audit.assignee_short || audit.assignee_name }}</span>
                    </div>
                    <div v-if="audit.audit_date" class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Дата аудита:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ formatFullDate(audit.audit_date) }}</span>
                    </div>
                    <div v-if="audit.start_time || audit.end_time" class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Время:</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ formatTime(audit.start_time) }} - {{ formatTime(audit.end_time) }}
                        </span>
                    </div>
                </div>

                <!-- Информация о клиенте -->
                <div v-if="audit.client_name || audit.address || audit.client_contact || audit.object_name"
                     class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Информация о клиенте
                    </h3>
                    <div class="space-y-2 text-sm">
                        <p v-if="audit.client_name"><span class="text-gray-500">Клиент:</span> {{ audit.client_name }}</p>
                        <p v-if="audit.client_contact"><span class="text-gray-500">Контакты:</span> {{ audit.client_contact }}</p>
                        <p v-if="audit.address"><span class="text-gray-500">Адрес:</span> {{ audit.address }}</p>
                        <p v-if="audit.object_name"><span class="text-gray-500">Объект:</span> {{ audit.object_name }}</p>
                    </div>
                </div>

                <!-- Описание -->
                <div v-if="audit.description" class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Описание</h3>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ audit.description }}</p>
                </div>

                <!-- Выявленные проблемы -->
                <div v-if="audit.findings" class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 border-l-4 border-yellow-500">
                    <h3 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Выявленные проблемы
                    </h3>
                    <p class="text-yellow-800 dark:text-yellow-200 whitespace-pre-wrap">{{ audit.findings }}</p>
                </div>

                <!-- Рекомендации -->
                <div v-if="audit.recommendations" class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 border-l-4 border-green-500">
                    <h3 class="font-semibold text-green-800 dark:text-green-300 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Рекомендации
                    </h3>
                    <p class="text-green-800 dark:text-green-200 whitespace-pre-wrap">{{ audit.recommendations }}</p>
                </div>

                <!-- Файлы и документы -->
                <div v-if="audit.media?.length" class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Вложения ({{ audit.media.length }})
                        </h3>
                        <button v-if="audit.can_be_edited" @click="openMediaUpload" class="text-blue-600 text-sm">
                            + Добавить
                        </button>
                    </div>

                    <!-- Фотографии -->
                    <div v-if="photos.length > 0" class="mb-4">
                        <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">📸 Фотографии ({{ photos.length }})</h4>
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="media in photos" :key="media.id" class="relative group">
                                <img :src="media.url || `/storage/${media.path}`"
                                     class="w-full h-32 object-cover rounded-lg cursor-pointer"
                                     :alt="media.original_name"
                                     @click="previewFile(media)"
                                     @error="(e) => { e.target.src = '/images/image-placeholder.png' }">
                                <button v-if="audit.can_be_edited" @click.stop="deleteMedia(media.id)"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div class="text-xs text-gray-500 mt-1 truncate">{{ media.original_name }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Документы -->
                    <div v-if="documents.length > 0">
                        <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">📄 Документы ({{ documents.length }})</h4>
                        <div class="space-y-2">
                            <div v-for="media in documents" :key="media.id"
                                 class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                 @click="previewFile(media)">
                                <div class="w-10 h-10 flex items-center justify-center">
                                    <FileIcons :type="getFileType(media.mime_type, media.original_name)" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ media.original_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ media.size_for_humans || formatSize(media.size) }} • {{ formatDate(media.created_at) }}
                                    </div>
                                </div>
                                <button v-if="audit.can_be_edited" @click.stop="deleteMedia(media.id)"
                                        class="p-1 text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                <button @click.stop="downloadFile(media)" class="p-1 text-blue-500 hover:text-blue-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Комментарии -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Комментарии ({{ audit.comments?.length || 0 }})
                    </h3>

                    <!-- Форма добавления комментария -->
                    <form @submit.prevent="addComment" class="mb-4">
                        <div class="space-y-3">
                            <textarea v-model="commentForm.content" rows="2"
                                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none"
                                      placeholder="Написать комментарий..."></textarea>

                            <!-- Предпросмотр вложений -->
                            <div v-if="commentAttachments.length > 0" class="flex flex-wrap gap-2">
                                <div v-for="(file, idx) in commentAttachments" :key="idx"
                                     class="relative bg-gray-100 dark:bg-gray-700 rounded-lg p-2 flex items-center gap-2">
                                    <FileIcons :type="getFileType(file.type, file.name)" class="w-4 h-4" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ file.name }}</span>
                                    <button type="button" @click="removeAttachment(idx)" class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" @click="triggerFileUpload"
                                        class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    Прикрепить файл
                                </button>
                                <input type="file" ref="commentFileInput" @change="uploadCommentMedia"
                                       multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx" class="hidden">
                                <button type="submit"
                                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition disabled:opacity-50"
                                        :disabled="commentForm.processing">
                                    {{ commentForm.processing ? 'Отправка...' : 'Отправить' }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Список комментариев -->
                    <div class="space-y-3">
                        <div v-for="comment in audit.comments" :key="comment.id" class="border-b border-gray-100 dark:border-gray-700 pb-3">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 text-sm font-medium">
                                    {{ comment.user_short?.charAt(0) || comment.user_name?.charAt(0) || '?' }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ comment.user_name || comment.user?.full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDateTime(comment.created_at) }}</p>
                                </div>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 text-sm ml-10 whitespace-pre-wrap">{{ comment.content }}</p>

                            <!-- Вложения комментария -->
                            <div v-if="comment.media?.length" class="ml-10 mt-2 flex flex-wrap gap-2">
                                <div v-for="media in comment.media" :key="media.id"
                                     @click="previewFile(media)"
                                     class="cursor-pointer">
                                    <img v-if="media.media_type === 'photo'" :src="media.url"
                                         class="w-16 h-16 object-cover rounded-lg">
                                    <div v-else class="flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                        <FileIcons :type="getFileType(media.mime_type, media.original_name)" class="w-5 h-5" />
                                        <span class="text-xs">{{ media.original_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!audit.comments?.length" class="text-center text-gray-500 text-sm py-4">
                            Нет комментариев
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий (в контенте, не в фиксированной панели) -->
                <div class="flex flex-col gap-3 pt-4">
                    <!-- Кнопка экспорта PDF (всегда видна) -->
                    <a :href="route('audits.export.pdf', audit.id)"
                       target="_blank"
                       class="w-full bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-xl font-medium transition text-center flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Экспорт в PDF
                    </a>

                    <!-- Кнопки управления аудитом -->
                    <button v-if="audit.can_be_edited && audit.status === 'draft'"
                            @click="startAudit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-medium transition">
                        Начать аудит
                    </button>
                    <button v-if="audit.can_be_edited && audit.status === 'in_progress'"
                            @click="openCompleteModal"
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-medium transition">
                        Завершить аудит
                    </button>

                    <!-- Кнопка удаления (только для создателя) -->
                    <button v-if="audit.can_be_edited"
                            @click="deleteAudit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-medium transition">
                        Удалить аудит
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно редактирования -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeEditModal">
            <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Редактирование аудита</h2>
                </div>
                <form @submit.prevent="updateAudit" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Название <span class="text-red-500">*</span>
                        </label>
                        <input v-model="editForm.title" type="text"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Описание
                        </label>
                        <textarea v-model="editForm.description" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Дата аудита
                            </label>
                            <input v-model="editForm.audit_date" type="date"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Тип аудита
                            </label>
                            <select v-model="editForm.audit_type"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="measurement">Замеры</option>
                                <option value="production_line">Производственная линия</option>
                                <option value="quality_check">Проверка качества</option>
                                <option value="consultation">Консультация</option>
                                <option value="other">Другое</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeEditModal"
                                class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg">
                            Отмена
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg disabled:opacity-50"
                                :disabled="editForm.processing">
                            {{ editForm.processing ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно завершения аудита -->
        <div v-if="showCompleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showCompleteModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Завершение аудита</h2>
                </div>
                <form @submit.prevent="completeAudit" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Выявленные проблемы / замечания
                        </label>
                        <textarea v-model="completeForm.findings" rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                  placeholder="Опишите что было выявлено в ходе аудита..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Рекомендации
                        </label>
                        <textarea v-model="completeForm.recommendations" rows="3"
                                  class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                  placeholder="Дайте рекомендации по улучшению..."></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showCompleteModal = false"
                                class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg">
                            Отмена
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg disabled:opacity-50"
                                :disabled="completeForm.processing">
                            {{ completeForm.processing ? 'Завершение...' : 'Завершить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно загрузки фото -->
        <div v-if="showMediaUpload" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showMediaUpload = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Добавить файл</h2>
                </div>
                <form @submit.prevent="uploadMedia" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Выберите файл
                        </label>
                        <input type="file" ref="fileInput" @change="handleFileSelect"
                               accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                               class="w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Тип файла
                        </label>
                        <select v-model="mediaForm.media_type"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="photo">Фотография</option>
                            <option value="document">Документ</option>
                            <option value="drawing">Чертеж</option>
                            <option value="other">Другое</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Описание
                        </label>
                        <input v-model="mediaForm.description" type="text"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               placeholder="Краткое описание">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showMediaUpload = false"
                                class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg">
                            Отмена
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg disabled:opacity-50"
                                :disabled="mediaForm.processing">
                            {{ mediaForm.processing ? 'Загрузка...' : 'Загрузить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно просмотра файла -->
        <div v-if="previewFileData" class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center" @click="closePreview">
            <div class="relative max-w-full max-h-full p-4">
                <button @click="closePreview" class="absolute top-4 right-4 text-white bg-black/50 rounded-full p-2 hover:bg-black/70 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <img v-if="previewFileData.media_type === 'photo' || previewFileData.mime_type?.startsWith('image/')"
                     :src="previewFileData.url"
                     class="max-w-full max-h-full object-contain"
                     @error="(e) => { e.target.src = '/images/image-placeholder.png' }">

                <div v-else class="bg-white dark:bg-gray-800 rounded-xl p-8 text-center max-w-md">
                    <div class="w-24 h-24 mx-auto mb-4">
                        <FileIcons :type="getFileType(previewFileData.mime_type, previewFileData.original_name)" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ previewFileData.original_name }}</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Размер: {{ previewFileData.size_for_humans || formatSize(previewFileData.size) }}</p>
                    <a :href="previewFileData.url" download
                       class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Скачать файл
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FileIcons from '@/Components/Icons/FileIcons.vue'

const props = defineProps({
    audit: {
        type: Object,
        required: true
    }
})

// Состояние
const showEditModal = ref(false)
const showCompleteModal = ref(false)
const showMediaUpload = ref(false)
const previewFileData = ref(null)
const fileInput = ref(null)
const commentFileInput = ref(null)
const selectedFile = ref(null)
const commentAttachments = ref([])
const uploadedMediaIds = ref([])

// Формы
const commentForm = useForm({ content: '' })
const completeForm = useForm({ findings: '', recommendations: '' })
const mediaForm = useForm({ description: '', media_type: 'photo' })
const editForm = useForm({
    title: props.audit.title,
    description: props.audit.description || '',
    audit_type: props.audit.audit_type,
    audit_date: props.audit.audit_date_formatted || '',
    status: props.audit.status
})

// Вычисляемые свойства
const audit = computed(() => props.audit)
const photos = computed(() => audit.value.media?.filter(m => m.media_type === 'photo') || [])
const documents = computed(() => audit.value.media?.filter(m => m.media_type !== 'photo') || [])

// Определение типа файла для иконки
const getFileType = (mimeType, originalName) => {
    if (mimeType?.startsWith('image/')) return 'image'
    if (mimeType === 'application/pdf') return 'pdf'
    if (mimeType?.includes('word') || mimeType?.includes('document') || originalName?.match(/\.(doc|docx)$/i)) return 'word'
    if (mimeType?.includes('excel') || mimeType?.includes('sheet') || originalName?.match(/\.(xls|xlsx)$/i)) return 'excel'
    return 'file'
}

// Вспомогательные функции
const getStatusBarClass = (status) => {
    const map = { draft: 'bg-gray-400', in_progress: 'bg-yellow-500', completed: 'bg-green-500', cancelled: 'bg-red-500' }
    return map[status] || 'bg-gray-400'
}

const getStatusBadgeClass = (status) => {
    const map = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    }
    return map[status] || ''
}

const formatFullDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

const formatTime = (time) => {
    if (!time) return ''
    return time.substring(0, 5)
}

const formatDateTime = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleString('ru-RU', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

const formatSize = (bytes) => {
    if (!bytes) return '0 B'
    const units = ['B', 'KB', 'MB', 'GB']
    let i = 0
    let size = bytes
    while (size >= 1024 && i < units.length - 1) {
        size /= 1024
        i++
    }
    return `${size.toFixed(2)} ${units[i]}`
}

// Действия с файлами
const getFileUrl = (media) => {
    if (media.url) return media.url
    return `/storage/${media.path}`
}

const previewFile = (media) => {
    const url = getFileUrl(media)

    if (media.media_type === 'photo' || media.mime_type?.startsWith('image/')) {
        previewFileData.value = { ...media, url }
    } else {
        window.open(url, '_blank')
    }
}

const downloadFile = (media) => {
    const url = getFileUrl(media)
    window.open(url, '_blank')
}

const deleteMedia = (mediaId) => {
    if (confirm('Удалить этот файл?')) {
        router.delete(route('audits.media.delete', mediaId), {
            preserveScroll: true,
            onSuccess: () => router.reload()
        })
    }
}

const openMediaUpload = () => {
    showMediaUpload.value = true
}

const handleFileSelect = (event) => {
    selectedFile.value = event.target.files[0]
}

const uploadMedia = () => {
    if (!selectedFile.value) return

    const formData = new FormData()
    formData.append('file', selectedFile.value)
    formData.append('media_type', mediaForm.media_type)
    formData.append('description', mediaForm.description)

    router.post(route('audits.media.upload', audit.value.id), formData, {
        preserveScroll: true,
        onSuccess: () => {
            showMediaUpload.value = false
            mediaForm.reset()
            selectedFile.value = null
            if (fileInput.value) fileInput.value.value = ''
            router.reload()
        },
        onError: (errors) => {
            console.error('Ошибка загрузки:', errors)
        }
    })
}

// Комментарии с вложениями
const triggerFileUpload = () => {
    commentFileInput.value?.click()
}

const uploadCommentMedia = async (event) => {
    const files = Array.from(event.target.files)

    for (const file of files) {
        const formData = new FormData()
        formData.append('file', file)

        try {
            const response = await fetch(route('audits.media.upload.comment', audit.value.id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            })

            const result = await response.json()
            if (result.success) {
                commentAttachments.value.push({
                    id: result.media.id,
                    name: result.media.original_name,
                    type: result.media.mime_type,
                    media_type: result.media.media_type
                })
                uploadedMediaIds.value.push(result.media.id)
            }
        } catch (error) {
            console.error('Ошибка загрузки файла:', error)
        }
    }

    if (commentFileInput.value) {
        commentFileInput.value.value = ''
    }
}

const removeAttachment = (index) => {
    commentAttachments.value.splice(index, 1)
    uploadedMediaIds.value.splice(index, 1)
}

const addComment = () => {
    commentForm.post(route('audits.comments.store', audit.value.id), {
        preserveScroll: true,
        data: {
            content: commentForm.content,
            attachments: uploadedMediaIds.value
        },
        onSuccess: () => {
            commentForm.reset()
            commentAttachments.value = []
            uploadedMediaIds.value = []
            router.reload()
        },
        onError: (errors) => {
            console.error('Ошибка добавления комментария:', errors)
        }
    })
}

// Действия с аудитом
const startAudit = () => {
    router.post(route('audits.start', audit.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => router.reload()
    })
}

const openCompleteModal = () => {
    completeForm.findings = audit.value.findings || ''
    completeForm.recommendations = audit.value.recommendations || ''
    showCompleteModal.value = true
}

const completeAudit = () => {
    completeForm.post(route('audits.complete', audit.value.id), {
        onSuccess: () => {
            showCompleteModal.value = false
            completeForm.reset()
            router.reload()
        }
    })
}

const deleteAudit = () => {
    if (confirm('Вы уверены, что хотите удалить этот аудит? Это действие необратимо.')) {
        router.delete(route('audits.destroy', audit.value.id), {
            onSuccess: () => {
                router.get(route('audits.index'))
            }
        })
    }
}

const openEditModal = () => {
    editForm.title = audit.value.title
    editForm.description = audit.value.description || ''
    editForm.audit_type = audit.value.audit_type
    editForm.audit_date = audit.value.audit_date_formatted || ''
    editForm.status = audit.value.status
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    editForm.reset()
}

const updateAudit = () => {
    editForm.put(route('audits.update', audit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal()
            router.reload()
        }
    })
}

const closePreview = () => {
    previewFileData.value = null
}
</script>

<style scoped>
.group-hover\:opacity-100:hover {
    opacity: 1;
}

.transition {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>
