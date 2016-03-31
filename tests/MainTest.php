<?php
use Latrell\QQWry\QQWry;

class MainTest extends \PHPUnit_Framework_TestCase
{

	protected $qqwry;

	public function __construct()
	{
		$file = realpath(__DIR__ . '/../database/qqwry.dat');
		$this->qqwry = new QQWry($file);
	}

	public function testQuery()
	{
		for ($i = 1; $i <= 100; $i ++) {
			$ip = mt_rand();
			$ip = $this->qqwry->ntoa($ip);
			$record = $this->qqwry->query($ip);
			echo "\n", $i, "\t", $ip, "\t", $record->get('country'), "\t", $record->get('area');
		}
	}
}
