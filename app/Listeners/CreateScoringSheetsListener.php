<?php
// app/Listeners/CreateScoringSheetsListener.php

namespace App\Listeners;

use App\Events\MonthEnd;
use App\Services\Scoring\SheetService;

class CreateScoringSheetsListener
{
    protected $sheetService;

    public function __construct(SheetService $sheetService)
    {
        $this->sheetService = $sheetService;
    }

    public function handle(MonthEnd $event)
    {
        $this->sheetService->createSheetsForNextMonth();
    }
}
