# QQWry

纯真 IP 库 Laravel 版 。

数据库版本：2018-03-15

## 纯真IP库

官网：[http://www.cz88.net](http://www.cz88.net)  
在官网右上角有个IP数据库下载

## 安装

```
composer require latrell/qqwry dev-master
```

更新你的依赖包 ```composer update``` 或者全新安装 ```composer install```。


## Facades用法
#### Laravel 5
```
编辑 config/app.php 文件，更改如下配置：
'providers' => [] 中加入： Latrell\QQWry\QQWryServiceProvider::class,

'aliases' => [] 中加入： 'QQWry' => Latrell\QQWry\Facades\QQWry::class, // Laravel 5.5+ 无需加入该配置
```

控制器中使用时加入命名空间：
```
use QQWry;
```

控制器中的方法中使用：
```
	$ip = mt_rand(); // 取一个随机IP。
	$ip = QQWry::ntoa($ip); // 将IP转换成文本型格式。
	$record = QQWry::query('127.0.0.1'); // 取出IP对应的地址。
	echo "\n", $ip, "\t", $record['country'], "\t", $record['area']; // 输出结果。
```

## 在视图中
```php
	@inject('qqwry', 'qqwry')
	
	{{ $qqwry->query('127.0.0.1')->implode(' ') }}
```
