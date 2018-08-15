<?php

declare(strict_types=1);

namespace JDecool\ClamAV\Exception;

use RuntimeException;
use Throwable;

class ReloadingError extends RuntimeException implements Exception
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct('ClamAV database reload error.', $code, $previous);
    }
}
