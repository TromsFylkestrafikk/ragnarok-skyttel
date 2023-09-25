<?php

namespace Ragnarok\Skyttel\Sinks;

//use Exception;
use Illuminate\Support\Carbon;
//use Ragnarok\Skyttel\Facades\SkyttelFiles;
//use Ragnarok\Skyttel\Facades\SkyttelImporter;
use Ragnarok\Sink\Sinks\SinkBase;
use Ragnarok\Sink\Traits\LogPrintf;

class SinkSkyttel extends SinkBase
{
	use LogPrintf;

	public $id = "skyttel";
	public $title = "Skyttel";

	public function __construct()
	{
		$this->logPrintfInit('[SinkSkyttel]: ');
	}

    /**
     * @inheritdoc
     */
    public function getFromDate(): Carbon
    {
        return new Carbon('2023-01-01');
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
    	return true;
    }

    /**
     * @inheritdoc
     */
    public function removeChunk($id): bool
    {
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
}
