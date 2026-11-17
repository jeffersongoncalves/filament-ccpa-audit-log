<?php

namespace JeffersonGoncalves\Filament\CcpaAuditLog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CcpaAuditLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-ccpa-audit-log')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
