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

final class PostgreSQL15 extends PostgreSQL
{

    public function __toString(): string
    {
        return 'v15 (15.4)';
    }

    public function getConfiguration(): array
    {
        $configuration = array_reverse($this->configuration, true);
        $configuration['volumes'] = [
            './.data/postgres/15/data:/var/lib/postgresql/data',
        ];
        $configuration['image'] = 'postgres:15.4-alpine';

        return array_reverse($configuration, true);
    }

    /**
     * @throws \Exception
     */
    public function _after()
    {
        createDirectory(getenv('DOCKER_PATH') . '/.data/postgres/15/data');
    }

}
