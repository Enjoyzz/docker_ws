<?php

namespace Enjoys\DockerWs\Services\Db\SQLite;

use Enjoys\DockerWs\Services\NullService;

class SQLite extends NullService
{
    public function __construct(string $serviceName = 'sqlite', string $type = 'sqlite')
    {
        parent::__construct($serviceName, $type);
    }

    public function __toString(): string
    {
        return 'SQLite';
    }

}