<?php

declare(strict_types=1);

namespace JDecool\ClamAV;

use Exception;
use JDecool\ClamAV\Exception\ConnectionError;
use JDecool\ClamAV\Socket\PhpSocket;
use Socket\Raw\Factory;

class ClientFactory
{
    public const DEFAULT_TIMEOUT = 5; // in seconds

    /**
     * @throws ConnectionError
     */
    public function create(string $host, int $port, int $timeout = self::DEFAULT_TIMEOUT): Client
    {
        $dsn = sprintf('tcp://%s:%d', $host, $port);

        try {
            $phpSocketFactory = new Factory();
            $phpSocket = $phpSocketFactory->createClient($dsn, $timeout);
        } catch (Exception $e) {
            throw new ConnectionError(0, $e);
        }

        return new Client(new PhpSocket($phpSocket));
    }
}
