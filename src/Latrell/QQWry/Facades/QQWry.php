<?php
namespace Latrell\QQWry\Facades;

use Illuminate\Support\Facades\Facade;

class QQWry extends Facade
{

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'qqwry';
	}
}