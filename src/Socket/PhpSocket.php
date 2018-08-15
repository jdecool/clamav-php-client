<?php

declare(strict_types=1);

namespace JDecool\ClamAV\Socket;

use Socket\Raw\Socket as RawSocket;

class PhpSocket implements Socket
{
    private $instance;
    private $timeout;

    /**
     * @param int $timeout Connection timeout in seconds
     */
    public function __construct(RawSocket $instance, int $timeout = 30)
    {
        $this->instance = $instance;
        $this->timeout = $timeout;
    }

    public function write(string $command): void
    {
        $this->instance->write($command);
    }

    public function send(string $command): string
    {
        $this->instance->write($command);

        $result = '';
        while ($this->instance->selectRead($this->timeout)) {
            $recv = $this->instance->read(8192);
            if ('' === $recv) {
                return trim($result);
            }

            $result .= $recv;
        }

        return '';
    }
}
