<?php

namespace JeffersonGoncalves\Filament\CcpaAuditLog;

use Filament\Contracts\Plugin;
use Filament\Panel;

class CcpaAuditLogPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-ccpa-audit-log';
    }

    public function register(Panel $panel): void
    {
    }

    public function boot(Panel $panel): void
    {
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
}
