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
use Illuminate\Support\Facades\Log;

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
     * Экспорт аудита в PDF с оптимизацией памяти (без Intervention Image)
     */
    public function exportToPdf(Audit $audit)
    {
        // Увеличиваем лимиты
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        // Загружаем связи
        $audit->load([
            'creator:id,last_name,first_name,patronymic,nickname',
            'assignee:id,last_name,first_name,patronymic,nickname',
            'media',
            'comments.user'
        ]);

        // Вычисляемые поля
        $audit->creator_name = $audit->creator ? $audit->creator->full_name : '—';
        $audit->creator_short = $audit->creator ? $audit->creator->short_name : '—';
        $audit->assignee_name = $audit->assignee ? $audit->assignee->full_name : 'Не назначен';
        $audit->assignee_short = $audit->assignee ? $audit->assignee->short_name : 'Не назначен';
        $audit->status_name = $this->getStatusName($audit->status);
        $audit->type_name = $this->getAuditTypeName($audit->audit_type);
        $audit->start_time_formatted = $audit->start_time ? date('H:i', strtotime($audit->start_time)) : null;
        $audit->end_time_formatted = $audit->end_time ? date('H:i', strtotime($audit->end_time)) : null;

        // Обрабатываем фото в Base64
        $photos = [];
        $photoCount = 0;
        $maxPhotos = 30;

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
                // Конвертируем фото в Base64
                $base64 = $this->imageToBase64($originalPath);
                if ($base64) {
                    $photos[] = [
                        'src' => $base64,
                        'original_name' => $media->original_name,
                        'description' => $media->description,
                    ];
                    $photoCount++;
                }
            } catch (\Exception $e) {
                Log::error("Ошибка обработки фото {$media->id}: " . $e->getMessage());
            }
        }

        $data = [
            'audit' => $audit,
            'photos' => $photos,
            'generated_at' => now()->format('d.m.Y H:i:s')
        ];

        // Генерируем PDF с правильными опциями
        $pdf = Pdf::loadView('exports.audit-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        // Для версии barryvdh/laravel-dompdf 3.x опции устанавливаются так:
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,     // не загружать внешние ресурсы
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => false,
            'chroot' => null,               // не нужен при использовании Base64
        ]);

        // Возвращаем PDF для скачивания
        return $pdf;
    }

    /**
     * Конвертирует изображение в Base64 (с ресайзом до 800px по ширине)
     */
    private function imageToBase64(string $sourcePath): ?string
    {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) return null;

        $mime = $imageInfo['mime'];
        $srcImage = null;

        switch ($mime) {
            case 'image/jpeg':
                $srcImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $srcImage = imagecreatefrompng($sourcePath);
                imagealphablending($srcImage, false);
                imagesavealpha($srcImage, true);
                break;
            case 'image/gif':
                $srcImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImage = imagecreatefromwebp($sourcePath);
                } else {
                    return null;
                }
                break;
            default:
                return null;
        }

        if (!$srcImage) return null;

        // Ресайз до ширины 800px
        $origWidth = imagesx($srcImage);
        $origHeight = imagesy($srcImage);
        $maxWidth = 800;

        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($origHeight * ($maxWidth / $origWidth));
            $resized = imagecreatetruecolor($newWidth, $newHeight);

            if ($mime === 'image/png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($srcImage);
            $srcImage = $resized;
        }

        // Сохраняем в буфер
        ob_start();
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($srcImage, null, 75);
                break;
            case 'image/png':
                imagepng($srcImage, null, 8);
                break;
            case 'image/gif':
                imagegif($srcImage);
                break;
            case 'image/webp':
                imagewebp($srcImage, null, 75);
                break;
            default:
                imagedestroy($srcImage);
                return null;
        }
        $imageData = ob_get_clean();
        imagedestroy($srcImage);

        return 'data:' . $mime . ';base64,' . base64_encode($imageData);
    }

    /**
     * Изменяет размер изображения с помощью GD и сохраняет во временный файл.
     *
     * @param string $sourcePath Путь к исходному изображению
     * @param string $tempDir Временная директория
     * @param int $mediaId ID медиа для уникальности имени
     * @return string|null Путь к обработанному файлу или null при ошибке
     */
    private function resizeImageWithGD(string $sourcePath, string $tempDir, int $mediaId): ?string
    {
        // Определяем тип изображения
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return null;
        }

        $mime = $imageInfo['mime'];
        $srcImage = null;

        switch ($mime) {
            case 'image/jpeg':
                $srcImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $srcImage = imagecreatefrompng($sourcePath);
                // Сохраняем прозрачность (альфа-канал)
                imagealphablending($srcImage, false);
                imagesavealpha($srcImage, true);
                break;
            case 'image/gif':
                $srcImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImage = imagecreatefromwebp($sourcePath);
                } else {
                    Log::warning("WebP не поддерживается GD в этой сборке PHP");
                    return null;
                }
                break;
            default:
                Log::warning("Неподдерживаемый тип изображения: {$mime}");
                return null;
        }

        if (!$srcImage) {
            return null;
        }

        // Получаем оригинальные размеры
        $origWidth = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // Вычисляем новые размеры (ширина не более 800px, высота пропорционально)
        $maxWidth = 800;
        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($origHeight * ($maxWidth / $origWidth));
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        // Создаём новое изображение
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Для PNG сохраняем прозрачность
        if ($mime === 'image/png') {
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
            $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
            imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Масштабируем
        imagecopyresampled($resizedImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Сохраняем во временный файл в формате JPEG (качество 75)
        $tempFilename = 'photo_' . $mediaId . '.jpg';
        $tempPath = $tempDir . '/' . $tempFilename;
        imagejpeg($resizedImage, $tempPath, 75);

        // Освобождаем память
        imagedestroy($srcImage);
        imagedestroy($resizedImage);

        return $tempPath;
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

    /**
     * Получить название статуса на русском
     */
    private function getStatusName(string $status): string
    {
        $map = [
            'draft'       => 'Черновик',
            'in_progress' => 'В процессе',
            'completed'   => 'Завершен',
            'cancelled'   => 'Отменен'
        ];
        return $map[$status] ?? $status;
    }

    /**
     * Получить название типа аудита на русском
     */
    private function getAuditTypeName(string $type): string
    {
        $map = [
            'measurement'      => 'Замеры',
            'production_line'  => 'Производственная линия',
            'quality_check'    => 'Проверка качества',
            'consultation'     => 'Консультация',
            'other'            => 'Другое'
        ];
        return $map[$type] ?? $type;
    }
}
