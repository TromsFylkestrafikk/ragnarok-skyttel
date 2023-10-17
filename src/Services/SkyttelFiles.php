<?php

namespace Ragnarok\Skyttel\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Ragnarok\Sink\Traits\LogPrintf;

class SkyttelFiles
{
    use LogPrintf;

    /**
     * Subdir on Skyttel's disk where our transaction XML files are located.
     *
     * @var string
     */
    public const SUBDIR = 'ReceiptFiles';

    /**
     * @var Filesystem
     */
    protected $remoteDisk = null;

    public function __construct()
    {
        $this->logPrintfInit('[SkyttelService]: ');
        $this->remoteDisk = $this->getRemoteDisk();
    }

    public function getRemoteFileList($dateFilter)
    {
        $remoteFiles = [];
        foreach ($this->remoteDisk->files(self::SUBDIR) as $candidate) {
            $filename = basename($candidate);
            $extension = strtolower(substr($candidate, -4));
            if (($extension === '.xml') && (strpos($filename, $dateFilter) !== false)) {
                $remoteFiles[$filename] = $candidate;
            }
        }
        return $remoteFiles;
    }

    public function getRemoteFile($filePath)
    {
        return $this->getRemoteDisk()->get($filePath);
    }

    public function getSubDir()
    {
        return self::SUBDIR;
    }

    protected function getRemoteDisk(): Filesystem
    {
        if ($this->remoteDisk === null) {
            $this->remoteDisk = app('filesystem')->build(config('ragnarok_skyttel.remote_disk'));
        }
        return $this->remoteDisk;
    }
}
