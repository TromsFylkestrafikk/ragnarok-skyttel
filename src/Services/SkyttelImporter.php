<?php

namespace Ragnarok\Skyttel\Services;

use Closure;
use Illuminate\Support\Facades\Schema;
use Ragnarok\Sink\Traits\LogPrintf;
use Ragnarok\Skyttel\Models\Batch;
use Ragnarok\Skyttel\Models\Receipt;
use Ragnarok\Skyttel\Models\SalesPlace;
use Ragnarok\Skyttel\Models\Transaction;
use Ragnarok\Skyttel\Models\Trip;
use Ragnarok\Skyttel\Services\ChristmasTreeParser;
use SimpleXMLElement;

class SkyttelImporter
{
    use LogPrintf;

    /**
     * @var ChristmasTreeParser
     */
    protected $xmlParser = null;

    /**
     * @var string
     */
    protected $xmlFileName;

    /**
     * @var \Ragnarok\Skyttel\Models\Batch
     */
    protected $batch;

    protected $countTransactions = 0;

    public function __construct()
    {
        $this->logPrintfInit('[SkyttelImporter]: ');
        $this->xmlParser = new ChristmasTreeParser();
        $this->batch = null;
    }

    public function import($file)
    {
        $this->xmlFileName = basename($file);
        $this->batch = null;
        $batch = $this->getBatch(true);
        if (!$batch->wasRecentlyCreated) {
            $this->flushTransactions();
        }
        $this->countTransactions = 0;
        $this->xmlParser->open($file);
        $this->xmlParser->addCallback('AutoPASSFerryOBUTransaction', [$this, 'processTransaction'])
            ->addCallback('FileCreatedDate', function (ChristmasTreeParser $xml) {
                $this->batch->FileCreatedDate = $xml->readString();
            })->parse()
            ->close();
        // Update status.
        $batch->succeeded = true;
        $batch->save();
        $this->debug('%s: Imported %d transactions', $this->xmlFileName, $this->countTransactions);
        return $this;
    }

    public function deleteImport($filename)
    {
        $this->xmlFileName = $filename;
        $this->batch = null;
        $this->flushTransactions();
        return $this;
    }

    /**
     * ChristmasTreeParser callback for 'AutoPASSFerryOBUTransaction' elements.
     */
    public function processTransaction($xml)
    {
        $this->countTransactions += 1;
        $transXml = $xml->expandSimpleXml();
        $transaction = Transaction::create(['batch_id' => $this->batch->id]);
        $additional = ['transaction_id' => $transaction->id];
        $this->createModelsFromXml(SalesPlace::class, $transXml->SalesPlace, $additional);
        $this->createModelsFromXml(Trip::class, $transXml->Trip, $additional, function ($trip, $simpleTrip) {
            $this->createModelsFromXml(Receipt::class, $simpleTrip->ReceiptPart, ['trip_id' => $trip->id]);
        });
    }

    public function getTransactionCount(): int
    {
        return $this->countTransactions;
    }

    /**
     * Create models of \SimpleXMLElement's
     *
     * @param string $modelClass  The class used to create models
     * @param \SimpleXMLElement $xml  Make models of all these.
     * @param array $additional  Additional values to add to all models
     * @param \Closure $postCreate(\Illuminate\Database\Eloquent\Model $model, \SimpleXMLElement $element)
     *   Call this after each model has been created.
     */
    protected function createModelsFromXml(
        string $modelClass,
        SimpleXMLElement $xml,
        array $additional = [],
        Closure $postCreate = null
    ) {
        foreach ($xml as $simpleElement) {
            $fill = array_filter(array_map(function ($column) use ($simpleElement) {
                return (string) $simpleElement->{$column};
            }, $this->getColumns($modelClass)));
            if ($additional) {
                $fill = array_merge($fill, $additional);
            }
            $model = $modelClass::create($fill);
            if (is_callable($postCreate)) {
                call_user_func_array($postCreate, [$model, $simpleElement]);
            }
        }
    }

    /**
     * Remove all stored data for the current batch, except for batch entry.
     */
    public function flushTransactions()
    {
        $batch = $this->getBatch();
        if (!$batch) {
            return;
        }
        $batch->succeeded = false;
        $batch->save();
        $transIds = $batch->transactions->pluck('id');
        $tripIds = Trip::whereIn('transaction_id', $transIds)->pluck('id');
        Receipt::whereIn('trip_id', $tripIds)->delete();
        Trip::whereIn('transaction_id', $transIds)->delete();
        SalesPlace::whereIn('transaction_id', $transIds)->delete();
        $batch->transactions()->delete();
    }

    /**
     * Get the batch model that contains our xml file name.
     *
     * @param bool $create Create batch if not found.
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getBatch($create = false)
    {
        if ($this->batch) {
            return $this->batch;
        }
        if (!$create) {
            $this->batch = Batch::where('filename', $this->xmlFileName)->first();
            return $this->batch;
        }
        $this->batch = Batch::firstOrCreate(
            ['filename' => $this->xmlFileName],
            ['filename' => $this->xmlFileName, 'succeeded' => false]
        );
        return $this->batch;
    }

    /**
     * List of columns that may be filled for given model.
     *
     * @param string $className Model class name.
     * @return array
     */
    protected function getColumns($className)
    {
        if (!isset($this->columns[$className])) {
            $this->columns[$className] = $this->getFillable($className);
        }
        return $this->columns[$className];
    }

    /**
     * Get an actual list of existing columns that are fillable.
     *
     * Laravel's Model::getFillable() simply return the previously set $fillable
     * property and does not care about the interaction with $guarded.
     *
     * @param string|\Illuminate\Database\Eloquent\Model
     * @return array
     */
    public static function getFillable($model)
    {
        if (is_string($model)) {
            $model = new $model();
        }
        $columns = Schema::getColumnListing($model->getTable());
        $columns = array_filter($columns, [$model, 'isFillable']);
        return array_combine($columns, $columns);
    }
}
