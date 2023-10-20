<?php

namespace Ragnarok\Skyttel\Sinks;

//use Exception;
use Illuminate\Support\Carbon;
use Ragnarok\Sink\Models\RawFile;
use Ragnarok\Sink\Services\LocalFiles;
use Ragnarok\Sink\Sinks\SinkBase;
use Ragnarok\Sink\Traits\LogPrintf;
use Ragnarok\Skyttel\Facades\SkyttelFiles;
//use Ragnarok\Skyttel\Facades\SkyttelImporter;

class SinkSkyttel extends SinkBase
{
    use LogPrintf;

    public static $id = "skyttel";
    public static $title = "Skyttel";

    /**
     * @var LocalFiles
     */
    protected $skyttelFiles = null;

    public function __construct()
    {
        $this->logPrintfInit('[SinkSkyttel]: ');
        $this->skyttelFiles = new LocalFiles(static::$id);
    }

    /**
     * @inheritdoc
     */
    public function getFromDate(): Carbon
    {
        return new Carbon('2021-04-21');
    }

    /**
     * @inheritdoc
     */
    public function getToDate(): Carbon
    {
        return today()->subDay();
    }

    /**
     * @inheritdoc
     */
    public function fetch($id): bool
    {
        $this->skyttelFiles->setPath();
        foreach (SkyttelFiles::getRemoteFileList($this->dateFilter($id)) as $filename) {
            $content = SkyttelFiles::getRemoteFile($filename);
            if (!$this->skyttelFiles->toFile($filename, $content)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeChunk($id): bool
    {
        $this->skyttelFiles->setPath(SkyttelFiles::getSubDir());
        foreach ($this->getLocalFileList($this->dateFilter($id)) as $filename) {
            $this->skyttelFiles->rmfile(basename($filename));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function import($id): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteImport($id): bool
    {
        return true;
    }

    protected function getLocalFileList($dateFilter)
    {
        return RawFile::where('name', 'LIKE', '%' . $dateFilter . '%')->pluck('name');
    }

    protected function dateFilter($id)
    {
        return '_' . str_replace('-', '', $id);
    }
}
