<?php

namespace App\Filament\Resources\AuditLogResource\Pages;

use App\Filament\Resources\AuditLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->activityLabel().' '.$this->getRecord()->subjectLabel();
    }

    public function getHeading(): string
    {
        return $this->getRecord()->summary();
    }
}
