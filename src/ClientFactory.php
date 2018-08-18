<?php

declare(strict_types=1);

namespace JDecool\ClamAV;

use Exception;
use JDecool\ClamAV\Exception\ConnectionError;
use JDecool\ClamAV\Socket\PhpSocket;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Socket\Raw\Factory;

class ClientFactory
{
    public const DEFAULT_TIMEOUT = 5; // in seconds

    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;
    }

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
            $this->logger->error('ClamAV connection error', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw new ConnectionError(0, $e);
        }

        return new Client(new PhpSocket($phpSocket), $this->logger);
    }
}
