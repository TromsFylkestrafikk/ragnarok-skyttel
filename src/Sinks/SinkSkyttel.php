<?php

namespace Ragnarok\Skyttel\Sinks;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Ragnarok\Sink\Models\RawFile;
use Ragnarok\Sink\Services\LocalFiles;
use Ragnarok\Sink\Sinks\SinkBase;
use Ragnarok\Sink\Traits\LogPrintf;
use Ragnarok\Skyttel\Facades\SkyttelFiles;
use Ragnarok\Skyttel\Facades\SkyttelImporter;

class SinkSkyttel extends SinkBase
{
    use LogPrintf;

    public static $id = "skyttel";
    public static $title = "Skyttel";

    /**
     * @var LocalFiles
     */
    protected $skyttelFiles = null;

    /**
     * @var string[]
     */
    protected $checksums = [];

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
    public function fetch($id): int
    {
        $this->skyttelFiles->setPath($id);
        $filesSize = 0;
        $this->checksums[$id] = [];
        foreach (SkyttelFiles::getRemoteFileList($this->dateFilter($id)) as $filename) {
            $content = SkyttelFiles::getRemoteFile($filename);
            $file = $this->skyttelFiles->toFile(basename($filename), $content);
            if (!$file) {
                return 0;
            }
            $this->checksums[$id][$file->name] = $file->checksum;
            $filesSize += $file->size;
        }
        ksort($this->checksums[$id]);
        return $filesSize;
    }

    /**
     * @inheritdoc
     */
    public function getChunkVersion($chunkId): string
    {
        return $this->getChecksum($chunkId);
    }

    /**
     * @inheritdoc
     */
    public function removeChunk($id): bool
    {
        $this->skyttelFiles->setPath($id);
        foreach ($this->getLocalFileList($this->dateFilter($id)) as $filename) {
            $this->skyttelFiles->rmfile(basename($filename));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function import($id): int
    {
        $count = 0;
        foreach ($this->getLocalFileList($this->dateFilter($id)) as $filename) {
            $filePath = $this->skyttelFiles->getDisk()->path($filename);
            $count += SkyttelImporter::import($filePath)->getTransactionCount();
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public function deleteImport($chunkId): bool
    {
        foreach ($this->getLocalFileList($this->dateFilter($chunkId)) as $filename) {
            SkyttelImporter::deleteImport(basename($filename));
        }
        return true;
    }

    /**
     * Create one checksum for all files for given chunk ID.
     */
    protected function getChecksum(string $chunkId): string
    {
        if (!isset($this->checksums[$chunkId])) {
            $this->checksums[$chunkId] = $this->getLocalFiles($chunkId)->pluck('checksums')->keyBy('name')->toArray();
            ksort($this->checksums[$chunkId]);
        }
        return md5(implode('', $this->checksums[$chunkId]));
    }

    protected function getLocalFileList(string $dateStr): Collection
    {
        return $this->getLocalFiles($dateStr)->pluck('name');
    }

    protected function getLocalFiles(string $dateStr): Collection
    {
        return RawFile::where('name', 'LIKE', sprintf('%%%s%%', $this->dateFilter($dateStr)))->get();
    }

    protected function dateFilter(string $chunkId): string
    {
        return '_' . str_replace('-', '', $chunkId);
    }
}
