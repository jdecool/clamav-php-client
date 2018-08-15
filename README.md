ClamAV deamon PHP client
========================

This library is a PHP client for [ClamAV](https://www.clamav.net) deamon.

## Installation

It can be install through Composer.

```bash
$ composer require jdecool/clamav-client
```

## Usage

```php
$clientFactory = new JDecool\ClamAV\ClientFactory();

$client = $clientFactory->create('127.0.0.1', 3310);
$client->ping(); // throw an ConnectionError exception if error occured
```

Scan a file

```php
$clientFactory = new JDecool\ClamAV\ClientFactory();

$client = $clientFactory->create('127.0.0.1', 3310);
$analysis = $client->scan('/path/to/file');

$analysis->count(); // = 1
$analysis->isInfected(); // true or false
$analysis->getMessage(); // if file is infected, it contains malware name
```

## Available méthods

* `JDecool\ClamAV\Client::ping(): void`
* `JDecool\ClamAV\Client::version(): string`
* `JDecool\ClamAV\Client::reload(): void`
* `JDecool\ClamAV\Client::shutdown(): void`
* `JDecool\ClamAV\Client::scanBatch(array $paths): JDecool\ClamAV\Analysis\Analysis`
* `JDecool\ClamAV\Client::scan(string ...$paths): JDecool\ClamAV\Analysis\Analysis`
* `JDecool\ClamAV\Client::contScan(string $path): JDecool\ClamAV\Analysis\Analysis`
* `JDecool\ClamAV\Client::multiscan(string $path): JDecool\ClamAV\Analysis\Analysis`
* `JDecool\ClamAV\Client::allMatchScan(string $path): JDecool\ClamAV\Analysis\Analysis`
* `JDecool\ClamAV\Client::stats(string $path): string`
* `JDecool\ClamAV\Client::startSession(): void`
* `JDecool\ClamAV\Client::endSession(): void`
