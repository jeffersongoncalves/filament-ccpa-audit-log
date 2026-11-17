<?php

namespace JeffersonGoncalves\Filament\CcpaAuditLog\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use JeffersonGoncalves\Filament\CcpaAuditLog\CcpaAuditLogPlugin;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->plugins([
                CcpaAuditLogPlugin::make(),
            ]);
    }
}
