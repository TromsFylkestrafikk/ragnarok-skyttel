<?php

namespace Ragnarok\Skyttel\Sinks;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Ragnarok\Sink\Models\SinkFile;
use Ragnarok\Sink\Services\ChunkArchive;
use Ragnarok\Sink\Services\ChunkExtractor;
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
    public function fetch(string $id): SinkFile|null
    {
        $archive = new ChunkArchive(static::$id, $id);
        foreach (SkyttelFiles::getRemoteFileList($this->dateFilter($id)) as $filename) {
            $content = SkyttelFiles::getRemoteFile($filename);
            $archive->addFromString(basename($filename), $content);
        }
        return $archive->save()->getFile();
    }

    /**
     * @inheritdoc
     */
    public function import(string $id, SinkFile $file): int
    {
        $count = 0;
        $extractor = new ChunkExtractor(static::$id, $file);
        foreach ($extractor->getFiles() as $filepath) {
            $count += SkyttelImporter::import($filepath)->getTransactionCount();
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public function deleteImport(string $chunkId): bool
    {
        foreach ($this->getLocalFiles($chunkId) as $file) {
            SkyttelImporter::deleteImport(basename($file->name));
        }
        return true;
    }

    /**
     * @return Collection<array-key, SinkFile>
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
