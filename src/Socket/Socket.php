<?php

namespace JDecool\ClamAV\Socket;

interface Socket
{
    public function write(string $command): void;
    public function send(string $command): string;
}
