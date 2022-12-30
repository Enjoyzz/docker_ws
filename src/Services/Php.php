<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;

use Enjoys\DockerWs\Envs\Tz;

use Enjoys\DockerWs\Envs\WorkDir;

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
        'working_dir' => '${WORK_DIR}',
        'volumes' => [
            './.data/ssh:/home/${USER_NAME}/.ssh',
            './.data/cache:/home/${USER_NAME}/.cache',
            './.data/composer:/home/${USER_NAME}/.composer',
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
    }

    public function after()
    {
        copyDirectoryWithFilesRecursive(
            __DIR__ . '/../../files/docker/php/' . $this->phpVersion,
            getenv('ROOT_PATH') . '/docker/php'
        );
        copy(__DIR__ . '/../../files/docker/php/alias.sh', getenv('ROOT_PATH') . '/docker/php/alias.sh');
        copy(__DIR__ . '/../../files/docker/php/sendmail', getenv('ROOT_PATH') . '/docker/php/sendmail');
    }

    public function before()
    {
    }

}
