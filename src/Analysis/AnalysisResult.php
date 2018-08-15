<?php

declare(strict_types=1);

namespace JDecool\ClamAV\Analysis;

class AnalysisResult
{
    const OK = 'OK';
    const INFECTED = 'FOUND';
    const ERROR = 'ERROR';

    private $filename;
    private $status;
    private $message;

    public function __construct(string $filename, string $status, string $message = null)
    {
        $this->filename = trim($filename);
        $this->status = trim($status);
        $this->message = $message ? trim($message) : null;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isClean(): bool
    {
        return self::OK === $this->status;
    }

    public function isInfected(): bool
    {
        return self::INFECTED === $this->status;
    }

    public function isError(): bool
    {
        return self::ERROR === $this->status;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
