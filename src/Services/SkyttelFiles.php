<?php

namespace Ragnarok\Skyttel\Services;

//use Illuminate\Contracts\Filesystem\Filesystem;
//use Illuminate\Support\Carbon;
//use Ragnarok\Sink\Models\RawFile;
//use Ragnarok\Sink\Services\RemoteFiles;
//use Ragnarok\Sink\Services\LocalFiles;
use Ragnarok\Sink\Traits\LogPrintf;

class SkyttelFiles
{
	use LogPrintf;

	public function __construct()
	{
		$this->logPrintfInit('[SkyttelService]: ');
	}
}
