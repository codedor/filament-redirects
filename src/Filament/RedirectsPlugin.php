<?php

namespace Codedor\FilamentRedirects\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;

class RedirectsPlugin implements Plugin
{
    protected bool $hasRedirectResource = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-redirects';
    }

    public function register(Panel $panel): void
    {
        if ($this->hasRedirectResource()) {
            $panel->resources([
                RedirectResource::class,
            ]);
        }
    }

    public function boot(Panel $panel): void {}

    public function redirectResource(bool $condition = true): static
    {
        // This is the setter method, where the user's preference is
        // stored in a property on the plugin object.
        $this->hasRedirectResource = $condition;

        // The plugin object is returned from the setter method to
        // allow fluent chaining of configuration options.
        return $this;
    }

    public function hasRedirectResource(): bool
    {
        // This is the getter method, where the user's preference
        // is retrieved from the plugin property.
        return $this->hasRedirectResource;
    }
}
