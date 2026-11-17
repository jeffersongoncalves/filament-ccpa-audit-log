<?php

namespace JeffersonGoncalves\Filament\CcpaAuditLog\Resources\AuditLogs\Pages;

use Filament\Resources\Pages\ViewRecord;
use JeffersonGoncalves\CcpaAuditLog\AuditLogger;
use JeffersonGoncalves\Filament\CcpaAuditLog\CcpaAuditLogPlugin;
use JeffersonGoncalves\Filament\CcpaAuditLog\Resources\AuditLogs\AuditLogResource;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Opening an entry shows IP address, user agent and metadata: audit who reads the log too.
        if (CcpaAuditLogPlugin::get()->shouldLogViews()) {
            app(AuditLogger::class)->record('viewed', $this->getRecord(), purpose: 'Audit log review');
        }
    }
}
