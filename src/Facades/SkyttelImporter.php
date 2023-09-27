<?php

namespace Ragnarok\Skyttel\Facades;

use Illuminate\Support\Facades\Facade;
use Ragnarok\Skyttel\Services\SkyttelImporter as SImporter;

class SkyttelImporter extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return SImporter::class;
	}
}
