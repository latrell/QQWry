# QQWry

纯真 IP 库 Laravel 版 。

数据库版本：2017-04-15

## 纯真IP库

官网：[http://www.cz88.net](http://www.cz88.net)  
在官网右上角有个IP数据库下载

## 安装

```
composer require latrell/qqwry dev-master
```

更新你的依赖包 ```composer update``` 或者全新安装 ```composer install```。

## 使用

要使用本服务提供者，你必须自己注册服务提供者到Laravel服务提供者列表中。
基本上有两种方法可以做到这一点。

打开配置文件 `config/app.php`。

找到key为 `providers` 的数组，在数组中添加服务提供者。

```php
    'providers' => [
        // ...
        Latrell\QQWry\QQWryServiceProvider::class,
    ]
```

找到key为 `aliases` 的数组，在数组中注册Facades。

```php
    'aliases' => [
        // ...
        'QQWry' => Latrell\QQWry\Facades\QQWry::class,
    ]
```

## 例子

### Facades用法
```php
	$ip = mt_rand(); // 取一个随机IP。
	$ip = QQWry::ntoa($ip); // 将IP转换成文本型格式。
	$record = QQWry::query($ip); // 取出IP对应的地址。
	echo "\n", $ip, "\t", $record['country'], "\t", $record['area']; // 输出结果。
```

### 在视图中
```php
	@inject('qqwry', 'qqwry')
	
	{{ $qqwry->query('127.0.0.1')->implode(' ') }}
```
