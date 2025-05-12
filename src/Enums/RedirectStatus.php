<?php

namespace Codedor\FilamentRedirects\Enums;

use Filament\Support\Contracts\HasLabel;

enum RedirectStatus: int implements HasLabel
{
    case Permanent = 301;
    case Temporary = 302;
    case Gone = 410;

    public function getLabel(): string
    {
        return match ($this) {
            self::Permanent => __('filament-redirects::admin.301 status'),
            self::Temporary => __('filament-redirects::admin.302 status'),
            self::Gone => __('filament-redirects::admin.410 status'),
        };
    }
}
