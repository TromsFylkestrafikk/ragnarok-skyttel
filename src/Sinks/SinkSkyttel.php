<?php

namespace Ragnarok\Skyttel\Sinks;

use Illuminate\Support\Carbon;
use Ragnarok\Sink\Models\SinkFile;
use Ragnarok\Sink\Services\ChunkArchive;
use Ragnarok\Sink\Services\ChunkExtractor;
use Ragnarok\Sink\Sinks\SinkBase;
use Ragnarok\Skyttel\Facades\SkyttelFiles;
use Ragnarok\Skyttel\Facades\SkyttelImporter;

class SinkSkyttel extends SinkBase
{
    public static $id = "skyttel";
    public static $title = "Skyttel";

    // Re-fetch entire previous month on the 6th at 04:00
    public $cronRefetch = '0 4 6 * *';

    /**
     * @inheritdoc
     */
    public function destinationTables(): array
    {
        return [
            'skyttel_transaction_batches' => 'Indicates processes status of individual files',
            'skyttel_transactions' => 'Map between transactions and batches',
            'skyttel_transaction_salesplace' => 'Meta about each transaction. Device id, device type, lane, line ...',
            'skyttel_transaction_trips' => 'Meta about vechicle per transaction. Licence plate, ocr confidence, sequence, from-to stops, tour ID, ...',
            'skyttel_transaction_receipts' => 'Payment with reference to trip: amount, VAT, Toll, discount',
        ];
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
        $files = SkyttelFiles::getRemoteFileList($this->dateFilter($id));
        if (!count($files)) {
            return null;
        }
        foreach ($files as $filename) {
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
    public function deleteImport(string $chunkId, SinkFile $file): bool
    {
        $extractor = new ChunkExtractor(static::$id, $file);
        foreach ($extractor->getFiles() as $filepath) {
            SkyttelImporter::deleteImport(basename($filepath));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function filenameToChunkId(string $filename): string|null
    {
        $matches = [];
        $hits = preg_match('|(?P<date>\d{4}-\d{2}-\d{2})\.zip$|', $filename, $matches);
        return $hits ? $matches['date'] : null;
    }

    /**
     * Re-fetch chunks two days behind.
     */
    public function refetchIdRange(): array
    {
        return [
            today()->subMonth()->startOfMonth()->format('Y-m-d'),
            today()->subMonth()->endOfMonth()->format('Y-m-d'),
        ];
    }

    protected function dateFilter(string $chunkId): string
    {
        return '_' . str_replace('-', '', $chunkId);
    }
}
