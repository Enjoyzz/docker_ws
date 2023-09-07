<?php

declare(strict_types=1);

namespace Enjoys\DockerWs\Services\Db\PostgreSQL\Version;

use Enjoys\DockerWs\Envs\TZ;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_DATABASE;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_PASSWORD;
use Enjoys\DockerWs\Services\Db\Mysql\Env\MYSQL_USER;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_DATABASE;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_PASSWORD;
use Enjoys\DockerWs\Services\Db\PostgreSQL\Env\POSTGRES_USER;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\createDirectory;

final class PostgreSQL13 extends PostgreSQL
{

    public function getConfiguration(): array
    {
        $configuration = array_reverse($this->configuration, true);
        $configuration['volumes'] = [
            './.data/postgres/13/data:/var/lib/postgresql/data',
        ];
        $configuration['image'] = 'postgres:13.12-alpine';

        return array_reverse($configuration, true);
    }

    public function __toString(): string
    {
        return 'v13 (13.12)';
    }

    public function _after()
    {
        createDirectory(getenv('DOCKER_PATH') . '/.data/postgres/13/data');
    }

}
