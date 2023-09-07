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

final class PostgreSQL12 extends PostgreSQL
{

    public function getConfiguration(): array
    {
        $configuration = array_reverse($this->configuration, true);
        $configuration['volumes'] = [
            './.data/postgres/12/data:/var/lib/postgresql/data',
        ];
        $configuration['image'] = 'postgres:12.16-alpine';

        return array_reverse($configuration, true);
    }

    public function __toString(): string
    {
        return 'v12 (12.16)';
    }

    public function _after()
    {
        createDirectory(getenv('DOCKER_PATH') . '/.data/postgres/12/data');
    }

}
