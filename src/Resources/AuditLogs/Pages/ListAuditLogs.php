<?php

namespace JeffersonGoncalves\Filament\CcpaAuditLog\Resources\AuditLogs\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use JeffersonGoncalves\CcpaAuditLog\AuditLogger;
use JeffersonGoncalves\Filament\CcpaAuditLog\CcpaAuditLogPlugin;
use JeffersonGoncalves\Filament\CcpaAuditLog\Resources\AuditLogs\AuditLogResource;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    public function mount(): void
    {
        parent::mount();

        // The list shows who touched whose data: opening it is an access to the log too.
        if (CcpaAuditLogPlugin::get()->shouldLogViews()) {
            app(AuditLogger::class)->record('viewed', purpose: 'Audit log review');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')
                ->label(AuditLogResource::label('verify.label'))
                ->icon('heroicon-o-shield-check')
                ->color('gray')
                ->action(function (): void {
                    $logger = app(AuditLogger::class);
                    $broken = $logger->verify();

                    if (CcpaAuditLogPlugin::get()->shouldLogViews()) {
                        $logger->record('verified', purpose: 'Audit log review', metadata: ['intact' => $broken === null, 'broken_at' => $broken]);
                    }

                    if ($broken === null) {
                        Notification::make()->success()->title(AuditLogResource::label('verify.intact'))->send();

                        return;
                    }

                    Notification::make()
                        ->danger()
                        ->title(__('filament-ccpa-audit-log::filament-ccpa-audit-log.verify.broken', ['id' => $broken]))
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
