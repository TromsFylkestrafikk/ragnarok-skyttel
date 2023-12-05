<?php

namespace Ragnarok\Skyttel\Sinks;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Ragnarok\Sink\Models\RawFile;
use Ragnarok\Sink\Services\ChunkArchive;
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
        $archive = new ChunkArchive(static::$id, $id);
        foreach (SkyttelFiles::getRemoteFileList($this->dateFilter($id)) as $filename) {
            $content = SkyttelFiles::getRemoteFile($filename);
            $archive->addFromString(basename($filename), $content);
        }
        $file = $archive->save()->getFile();
        return $files->size;
    }

    /**
     * @inheritdoc
     */
    public function removeChunk($id): bool
    {
        $this->skyttelFiles->setPath($id);
        foreach ($this->getLocalFiles($id) as $file) {
            $this->skyttelFiles->rmFile(basename($file->name));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function import($id): int
    {
        $count = 0;
        foreach ($this->getLocalFiles($id) as $file) {
            $filePath = $this->skyttelFiles->getDisk()->path($file->name);
            $count += SkyttelImporter::import($filePath)->getTransactionCount();
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public function deleteImport($chunkId): bool
    {
        foreach ($this->getLocalFiles($chunkId) as $file) {
            SkyttelImporter::deleteImport(basename($file->name));
        }
        return true;
    }

    /**
     * Create one checksum for all files for given chunk ID.
     */
    protected function getChecksum(string $chunkId): string
    {
        if (!isset($this->checksums[$chunkId])) {
            $this->checksums[$chunkId] = $this->getLocalFiles($chunkId)
                ->pluck('checksums')
                ->keyBy('name')
                ->toArray();
            ksort($this->checksums[$chunkId]);
        }
        return md5(implode('', $this->checksums[$chunkId]));
    }

    /**
     * @return Collection<array-key, RawFile>
     */
    protected function getLocalFiles(string $chunkId): Collection
    {
        return $this->skyttelFiles->getFilesLike(sprintf('%%%s%%', $this->dateFilter($chunkId)));
    }

    protected function dateFilter(string $chunkId): string
    {
        return '_' . str_replace('-', '', $chunkId);
    }
}
