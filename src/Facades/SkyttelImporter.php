<?php

namespace Ragnarok\Skyttel\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Skyttel\Services\SkyttelImporter as SImporter;

/**
 * @method static SImporter import(string $file)
 * @method static SImporter deleteImport(string $filename)
 * @method static int getTransactionCount()
 */
class SkyttelImporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SImporter::class;
    }
}
