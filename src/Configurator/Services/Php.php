<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Configurator\Services;

use Enjoys\DockerWs\Configurator\Envs\WorkDir;
use Enjoys\DockerWs\Configurator\ServiceInterface;
use Enjoys\DockerWs\Configurator\Envs\Tz;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;

final class Php implements ServiceInterface
{
    private string $name = 'php';

    private const USED_ENV_KEYS = [
        Tz::class,
        WorkDir::class,
    ];

    protected array $configuration = [
        'build' => [
            'context' => './docker/php',
            'dockerfile' => 'Dockerfile',
            'args' => [
                'TZ' => '${TZ}',
                'WORK_DIR' => '${WORK_DIR}',
                'USER_NAME' => '${USER_NAME}',
                'USER_ID' => '${USER_ID}',
            ]
        ],
        'volumes' => [
            '/.data/mail:/home/mail',
            './:${WORK_DIR}',
        ],
        'ports' => [
            '9006:9000'
        ],
        'networks' => [
            'backend'
        ]
    ];


    public function __construct(private string $phpVersion)
    {
        $this->setPhpVersion($phpVersion);
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV_KEYS;
    }

    /**
     * @param string $phpVersion
     * @return void
     */
    private function setPhpVersion(string $phpVersion): void
    {
        $this->phpVersion = $phpVersion;
        $this->configuration['build']['args']['PHP_IMAGE'] = sprintf('enjoys/php:%s-fpm-alpine', $phpVersion);
    }

    public function after()
    {
        copyDirectoryWithFilesRecursive(
            __DIR__ . '/../../../files/docker/php',
            getenv('ROOT_PATH') . '/docker/php'
        );
//        copy(__DIR__ . '/../../../files/docker/php/alias.sh', getenv('ROOT_PATH') . '/docker/php/alias.sh');
//        copy(__DIR__ . '/../../../files/docker/php/sendmail', getenv('ROOT_PATH') . '/docker/php/sendmail');
    }

    public function before()
    {
    }

}
