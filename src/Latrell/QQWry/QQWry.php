<?php
namespace Latrell\QQWry;

use Illuminate\Support\Collection;

/**
 * 纯真IP数据库
 *
 * @author Latrell Chan
 */
class QQWry
{

	public $encoding = 'UTF-8';

	protected $file;

	protected $fd;

	protected $total;

	// 索引区
	protected $index_start_offset;

	protected $index_end_offset;

	public function __construct($file)
	{
		if (! file_exists($file) or ! is_readable($file)) {
			throw new QQWryException("{$file} does not exist, or is not readable");
		}
		$this->file = $file;
		$this->fd = fopen($file, 'rb');

		$this->index_start_offset = join('', unpack('L', $this->readOffset(4, 0)));

		$this->index_end_offset = join('', unpack('L', $this->readOffset(4)));

		$this->total = ($this->index_end_offset - $this->index_start_offset) / 7 + 1;
	}

	public function __destruct()
	{
		if ($this->fd) {
			fclose($this->fd);
		}
	}

	/**
	 * 数值型IP转文本型IP
	 *
	 * @param number $nip
	 * @return string
	 */
	public function ntoa($nip)
	{
		$ip = [];
		for ($i = 3; $i > 0; $i --) {
			$ip_seg = intval($nip / pow(256, $i));
			$ip[] = $ip_seg;
			$nip -= $ip_seg * pow(256, $i);
		}
		$ip[] = $nip;
		return join('.', $ip);
	}

	/**
	 * IP查询
	 *
	 * @param string $ip
	 * @throws QQWryException
	 * @return array
	 */
	public function query($ip)
	{
		$ip_split = explode('.', $ip);
		if (count($ip_split) !== 4) {
			throw new QQWryException("{$ip} is not a valid ip address");
		}
		foreach ($ip_split as $v) {
			if ($v > 255) {
				throw new QQWryException("{$ip} is not a valid ip address");
			}
		}
		$ip_num = $ip_split[0] * (256 * 256 * 256) + $ip_split[1] * (256 * 256) + $ip_split[2] * 256 + $ip_split[3];
		$ip_find = $this->find($ip_num, 0, $this->total);
		$ip_offset = $this->index_start_offset + $ip_find * 7 + 4;
		$ip_record_offset = $this->readOffset(3, $ip_offset);
		$ip_record_offset = join('', unpack('L', $ip_record_offset . chr(0)));
		return $this->readRecord($ip_record_offset);
	}

	/**
	 * 读取记录
	 */
	protected function readRecord($offset)
	{
		$record = [
			'country' => '',
			'area' => ''
		];

		$offset = $offset + 4;

		$flag = ord($this->readOffset(1, $offset));

		switch ($flag) {
			case 1:
				$location_offset = $this->readOffset(3, $offset + 1);
				$location_offset = join('', unpack('L', $location_offset . chr(0)));

				$sub_flag = ord($this->readOffset(1, $location_offset));

				if ($sub_flag == 2) {
					// 国家
					$country_offset = $this->readOffset(3, $location_offset + 1);
					$country_offset = join('', unpack('L', $country_offset . chr(0)));
					$record['country'] = $this->readLocation($country_offset);
					// 地区
					$record['area'] = $this->readLocation($location_offset + 4);
				} else {
					$record['country'] = $this->readLocation($location_offset);
					$record['area'] = $this->readLocation($location_offset + strlen($record['country']) + 1);
				}
				break;
			case 2:
				// 地区
				// offset + 1(flag) + 3(country offset)
				$record['area'] = $this->readLocation($offset + 4);

				// offset + 1(flag)
				$country_offset = $this->readOffset(3, $offset + 1);
				$country_offset = join('', unpack('L', $country_offset . chr(0)));
				$record['country'] = $this->readLocation($country_offset);
				break;
			default:
				$record['country'] = $this->readLocation($offset);
				$record['area'] = $this->readLocation($offset + strlen($record['country']) + 1);
		}

		// 转换编码并去除无信息时显示的CZ88.NET
		$record = array_map(function ($item) {
			if (function_exists('mb_convert_encoding')) {
				$item = mb_convert_encoding($item, $this->encoding, 'GBK');
			} else {
				$item = iconv('GBK', $this->encoding . '//IGNORE', $item);
			}
			return preg_replace('/\s*cz88\.net\s*/i', '', $item);
		}, $record);

		return Collection::make($record);
	}

	/**
	 * 读取地区
	 */
	protected function readLocation($offset)
	{
		if ($offset == 0) {
			return '';
		}

		$flag = ord($this->readOffset(1, $offset));

		// 出错
		if ($flag == 0) {
			return '';
		}

		// 仍然为重定向
		if ($flag == 2) {
			$offset = $this->readOffset(3, $offset + 1);
			$offset = join('', unpack('L', $offset . chr(0)));
			return $this->readLocation($offset);
		}

		$location = '';
		$chr = $this->readOffset(1, $offset);
		while (ord($chr) != 0) {
			$location .= $chr;
			$offset ++;
			$chr = $this->readOffset(1, $offset);
		}
		return $location;
	}

	/**
	 * 查找 ip 所在的索引
	 */
	protected function find($ip_num, $l, $r)
	{
		if ($l + 1 >= $r) {
			return $l;
		}
		$m = intval(($l + $r) / 2);

		$find = $this->readOffset(4, $this->index_start_offset + $m * 7);
		$m_ip = join('', unpack('L', $find));

		if ($ip_num < $m_ip) {
			return $this->find($ip_num, $l, $m);
		} else {
			return $this->find($ip_num, $m, $r);
		}
	}

	protected function readOffset($number_of_bytes, $offset = null)
	{
		if (! is_null($offset)) {
			fseek($this->fd, $offset);
		}
		return fread($this->fd, $number_of_bytes);
	}
}
