<?php

namespace Enjoys\DockerWs\Services\Db\SQLite;

use Enjoys\DockerWs\Services\NullService;

class SQlite extends NullService
{
    public function __construct(string $serviceName = 'Sqlite', string $type = 'sqlite')
    {
        parent::__construct($serviceName, $type);
    }

}