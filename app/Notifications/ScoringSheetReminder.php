<?php
// app/Notifications/ScoringSheetReminder.php

namespace App\Notifications;

use App\Models\ScoringSheet;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ScoringSheetReminder extends Notification
{
    use Queueable;

    protected $sheet;

    public function __construct(ScoringSheet $sheet)
    {
        $this->sheet = $sheet;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $url = route('scoring.sheets.show', $this->sheet);

        return (new MailMessage)
            ->subject('Напоминание: необходимо заполнить ведомость')
            ->view('emails.scoring-reminder', [
                'user' => $notifiable,
                'sheet' => $this->sheet,
                'url' => $url,
                'subtitle' => 'Заполните ведомость до конца месяца'
            ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'sheet_id' => $this->sheet->id,
            'period' => $this->sheet->period_date->format('F Y'),
            'message' => 'Необходимо заполнить ведомость за ' . $this->sheet->period_date->format('F Y'),
            'url' => route('scoring.sheets.show', $this->sheet),
        ];
    }
}
