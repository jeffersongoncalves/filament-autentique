<?php

namespace JeffersonGoncalves\Filament\Autentique;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AutentiqueServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-autentique')
            ->hasTranslations();
    }
}
