<?php

namespace JeffersonGoncalves\Filament\CcpaAuditLog;

use JeffersonGoncalves\FilamentPluginCore\BasePackageServiceProvider;
use Spatie\LaravelPackageTools\Package;

class CcpaAuditLogServiceProvider extends BasePackageServiceProvider
{
    public static string $name = 'filament-ccpa-audit-log';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasTranslations();
    }
}
