<?php

declare(strict_types=1);

namespace JDecool\ClamAV\Exception;

use RuntimeException;
use Throwable;

class InvalidDeamonResponse extends RuntimeException implements Exception
{
    private $content;
    private $errors;

    public static function createMultilineInvalidResponse(string $response, array $errors): self
    {
        $instance = new self($response);
        $instance->errors = $errors;

        return $instance;
    }

    public function __construct(string $content, int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Couldn't parse ClamAV deamon response", $code, $previous);

        $this->content = $content;
        $this->errors = [];
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
