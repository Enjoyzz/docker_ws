<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Db\Mysql\Version;






use Enjoys\DockerWs\Services\ServiceInterface;

final class Mysql57 implements ServiceInterface
{
    private string $serviceName = 'mysql';

//    private const USED_ENV_KEYS = [
//        DatabaseUser::class,
//        DatabasePass::class,
//        DatabaseName::class,
//        Tz::class
//    ];

    private array $configuration = [
        'image' => 'mysql:5.7',
        'volumes' => [
            './.data/mysql/5.7/data:/var/lib/mysql',
            './.data/mysql/5.7/conf.d:/etc/mysql/conf.d',
            './.data/mysql/5.7/logs:/var/log/mysql',
            './.data/mysql/dump:/dump',
        ],
        'ports' => [
            '4305:3306',
        ],
        'security_opt' => [
            'seccomp:unconfined',
        ],
        'environment' => [
            'MYSQL_USER' => '${DATABASE_USER:-user}',
            'MYSQL_PASSWORD' => '${DATABASE_PASS:-pass}',
            'MYSQL_DATABASE' => '${DATABASE_NAME:-dbname}',
            'MYSQL_RANDOM_ROOT_PASSWORD' => 'yes',
            'TZ' => '${TZ}',
        ],
        'networks' => [
            'backend',
        ],

    ];


    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function __toString(): string
    {
        return  '5.7.*';
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }


    /**
     * @param string $serviceName
     */
    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    public function _after()
    {
        // TODO: Implement _after() method.
    }

    public function _before()
    {
        // TODO: Implement _before() method.
    }
}
