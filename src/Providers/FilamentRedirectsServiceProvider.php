<?php

namespace Codedor\FilamentRedirects\Providers;

use Codedor\FilamentRedirects\Models\Redirect;
use Codedor\FilamentRedirects\Observers\RedirectObserver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentRedirectsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-redirects')
            ->setBasePath(__DIR__ . '/../')
            ->hasConfigFile()
            ->hasMigration('create_redirects_table')
            ->runsMigrations()
            ->hasTranslations();
    }

    public function packageBooted()
    {
        parent::packageBooted();

        Redirect::observe(RedirectObserver::class);
    }
}
