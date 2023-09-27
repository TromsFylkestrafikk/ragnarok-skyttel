<?php

namespace Ragnarok\Skyttel\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Skyttel\Services\SkyttelFiles as SFiles;

class SkyttelFiles extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SFiles::class;
    }
}
