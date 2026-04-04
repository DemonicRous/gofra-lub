<?php
// app/Services/AuditService.php

namespace App\Services;

use App\Models\Audit;
use App\Models\User;
use App\Models\AuditComment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AuditService
{

    /**
     * Создание аудита
     */
    public function createAudit(array $data, User $creator): Audit
    {
        return DB::transaction(function () use ($data, $creator) {
            $data['uuid'] = (string) Str::uuid();
            $data['created_by'] = $creator->id;

            // Статус по умолчанию - "В процессе", а не "Черновик"
            $data['status'] = Audit::STATUS_IN_PROGRESS;

            // Если не указан ответственный, назначаем создателя
            if (empty($data['assigned_to'])) {
                $data['assigned_to'] = $creator->id;
            }

            // Парсим время
            if (!empty($data['start_time'])) {
                $data['start_time'] = date('H:i:s', strtotime($data['start_time']));
            }

            if (!empty($data['end_time'])) {
                $data['end_time'] = date('H:i:s', strtotime($data['end_time']));
            }

            return Audit::create($data);
        });
    }

    /**
     * Обновление аудита
     */
    public function updateAudit(Audit $audit, array $data, User $updater): Audit
    {
        return DB::transaction(function () use ($audit, $data, $updater) {
            // Парсим время
            if (isset($data['start_time']) && !empty($data['start_time'])) {
                $data['start_time'] = date('H:i:s', strtotime($data['start_time']));
            }

            if (isset($data['end_time']) && !empty($data['end_time'])) {
                $data['end_time'] = date('H:i:s', strtotime($data['end_time']));
            }

            $audit->update($data);

            return $audit->fresh();
        });
    }

    /**
     * Завершение аудита с подписью
     */
    public function completeAudit(Audit $audit, array $data, User $user): Audit
    {
        return DB::transaction(function () use ($audit, $data, $user) {
            $audit->update([
                'status' => Audit::STATUS_COMPLETED,
                'completed_at' => now(),
                'findings' => $data['findings'] ?? $audit->findings,
                'recommendations' => $data['recommendations'] ?? $audit->recommendations,
                'signature_data' => $data['signature_data'] ?? null,
                'signed_at' => now()
            ]);

            return $audit;
        });
    }

    /**
     * Добавление комментария с вложениями
     */
    public function addComment(Audit $audit, User $user, string $content, array $attachments = []): AuditComment
    {
        return DB::transaction(function () use ($audit, $user, $content, $attachments) {
            $comment = $audit->comments()->create([
                'content' => $content,
                'user_id' => $user->id,
                'attachments' => $attachments
            ]);

            if (!empty($attachments)) {
                $comment->media()->attach($attachments);
            }

            return $comment;
        });
    }

    /**
     * Экспорт аудита в PDF с оптимизацией памяти
     */
    public function exportToPdf(Audit $audit)
    {
        // Увеличиваем лимиты для обработки больших изображений
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        // Загружаем все необходимые связи
        $audit->load([
            'creator:id,last_name,first_name,patronymic,nickname',
            'assignee:id,last_name,first_name,patronymic,nickname',
            'media',
            'comments.user'
        ]);

        // Добавляем вычисляемые поля
        $audit->creator_name = $audit->creator ? $audit->creator->full_name : '—';
        $audit->creator_short = $audit->creator ? $audit->creator->short_name : '—';
        $audit->assignee_name = $audit->assignee ? $audit->assignee->full_name : 'Не назначен';
        $audit->assignee_short = $audit->assignee ? $audit->assignee->short_name : 'Не назначен';
        $audit->status_name = $this->getStatusName($audit->status);
        $audit->type_name = $this->getAuditTypeName($audit->audit_type);

        // Форматируем время
        $audit->start_time_formatted = $audit->start_time ? date('H:i', strtotime($audit->start_time)) : null;
        $audit->end_time_formatted = $audit->end_time ? date('H:i', strtotime($audit->end_time)) : null;

        // Создаём временную директорию для изображений
        $tempDir = storage_path('app/temp/pdf_' . uniqid());
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $imageManager = new ImageManager(new Driver());
        $photos = [];
        $photoCount = 0;
        $maxPhotos = 30; // Максимум 30 фото в PDF

        foreach ($audit->media->where('media_type', 'photo') as $media) {
            if ($photoCount >= $maxPhotos) {
                Log::warning("PDF аудита #{$audit->id}: превышено максимальное количество фото ({$maxPhotos}), остальные пропущены");
                break;
            }

            $originalPath = Storage::disk($media->disk)->path($media->path);
            if (!file_exists($originalPath)) {
                Log::warning("Файл не найден: {$originalPath}");
                continue;
            }

            try {
                // Создаём уменьшенную копию (ширина 800px, качество 75%)
                $img = $imageManager->read($originalPath);
                $img->scale(width: 800);

                $tempFilename = 'photo_' . $media->id . '.jpg';
                $tempPath = $tempDir . '/' . $tempFilename;
                $img->toJpeg(75)->save($tempPath);

                $photos[] = [
                    'src' => $tempPath,          // путь к временному файлу
                    'original_name' => $media->original_name,
                    'description' => $media->description,
                    'width' => $img->width(),
                    'height' => $img->height(),
                ];
                $photoCount++;
            } catch (\Exception $e) {
                Log::error("Ошибка обработки фото {$media->id}: " . $e->getMessage());
            }
        }

        $data = [
            'audit' => $audit,
            'photos' => $photos,
            'generated_at' => now()->format('d.m.Y H:i:s')
        ];

        // Создаём PDF с настройками для работы с локальными файлами
        $pdf = Pdf::loadView('exports.audit-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        // Важные настройки dompdf
        $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
        $pdf->getDomPDF()->set_option('isRemoteEnabled', false); // отключаем удалённые ресурсы
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('chroot', $tempDir);      // разрешаем доступ к временной папке

        // Генерируем содержимое
        $pdfContent = $pdf->output();

        // Удаляем временные файлы
        $this->cleanupTempFiles($tempDir);

        // Возвращаем сам PDF объект для дальнейшего скачивания
        return $pdf;
    }

    /**
     * Очистка временной директории
     */
    private function cleanupTempFiles(string $dir): void
    {
        if (!is_dir($dir)) return;

        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}
