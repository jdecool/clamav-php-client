<?php

declare(strict_types=1);

namespace JDecool\ClamAV;

use JDecool\ClamAV\Analysis\Analysis;
use JDecool\ClamAV\Analysis\AnalysisResult;
use JDecool\ClamAV\Exception\ConnectionError;
use JDecool\ClamAV\Exception\ReloadingError;
use JDecool\ClamAV\Socket\Socket;
use Psr\Log\LoggerInterface;

class Client
{
    private $socket;
    private $logger;

    public function __construct(Socket $socket, LoggerInterface $logger)
    {
        $this->socket = $socket;
        $this->logger = $logger;
    }

    public function ping(): void
    {
        if ('PONG' !== $this->sendCommand('PING')) {
            throw new ConnectionError();
        }
    }

    public function version(): string
    {
        $version = $this->sendCommand('VERSION');
        if ('' === $version) {
            throw new ConnectionError();
        }

        return $version;
    }

    public function reload(): void
    {
        if ('RELOADING' !== $this->sendCommand('RELOAD')) {
            throw new ReloadingError();
        }
    }

    public function shutdown(): void
    {
        $this->sendCommand('SHUTDOWN');
    }

    public function scanBatch(array $paths): Analysis
    {
        return call_user_func_array([$this, 'scan'], $paths);
    }

    public function scan(string ...$paths): Analysis
    {
        $pathsToScan = count($paths);

        if ($pathsToScan > 1) {
            $this->startSession();
        }

        $analysis = new Analysis();
        foreach ($paths as $path) {
            $analysis->addAnalysisResult($this->scanFile($path));
        }

        if ($pathsToScan > 1) {
            $this->endSession();
        }

        return $analysis;
    }

    public function contScan(string $path): Analysis
    {
        $result = $this->sendCommand("CONTSCAN $path");

        return ResponseParser::parse($result);
    }

    public function multiscan(string $path): Analysis
    {
        $result = $this->sendCommand("MULTISCAN $path");

        return ResponseParser::parse($result);
    }

    public function allMatchScan(string $path): Analysis
    {
        $result = $this->sendCommand("ALLMATCHSCAN $path");

        return ResponseParser::parse($result);
    }

    public function stats(): string
    {
        return $this->sendCommand('STATS');
    }

    public function startSession(): void
    {
        $this->writeCommand("IDSESSION");
    }

    public function endSession(): void
    {
        $this->writeCommand("END");
    }

    private function scanFile(string $file): AnalysisResult
    {
        $result = $this->sendCommand("SCAN $file");

        return ResponseParser::parseLine($result);
    }

    private function writeCommand(string $command): void
    {
        $this->logger->debug('Write command to ClamAV deamon', [
            'command' => $command,
        ]);

        $this->socket->write("n$command\n");
    }

    private function sendCommand(string $command): string
    {
        $this->logger->debug('Send command to ClamAV deamon', [
            'command' => $command,
        ]);

        $result = $this->socket->send("n$command\n");

        $this->logger->debug('Receive response from ClamAV deamon', [
            'response' => $result,
        ]);

        return $result;
    }
}
