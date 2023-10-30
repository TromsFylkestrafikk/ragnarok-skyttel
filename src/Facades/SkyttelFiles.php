<?php

namespace Ragnarok\Skyttel\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Skyttel\Services\SkyttelFiles as SFiles;

/**
 * @method static array getRemoteFileList(string $dateFilter)
 * @method static string getRemoteFile(string $filePath)
 * @method static string getSubDir()
 */
class SkyttelFiles extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SFiles::class;
    }
}
