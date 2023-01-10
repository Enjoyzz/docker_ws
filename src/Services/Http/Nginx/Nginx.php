<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http\Nginx;




use Enjoys\DockerWs\Services\Php\PhpService;
use Enjoys\DockerWs\Services\ServiceInterface;

use function Enjoys\FileSystem\copyDirectoryWithFilesRecursive;

final class Nginx implements ServiceInterface
{
    private string $serviceName = 'nginx';

    private const POSSIBLE_DEPEND_SERVICES = [
        PhpService::class
    ];

//    private const USED_ENV_KEYS = [
//        ServerName::class,
//        Tz::class,
//        WorkDir::class,
//        PublicDir::class,
//    ];

    private array $configuration = [
        'image' => 'nginx:1.19-alpine',
        'ports' => [
            '80:80',
            '8000:8000'
        ],
        'volumes' => [
            './docker/nginx/templates:/etc/nginx/templates',
            './.data/logs/nginx:/var/log/nginx',
            './:${WORK_DIR}',
        ],
        'depends_on' => [],
        'environment' => [
            'TZ' => '${TZ}',
            'PUBLIC_DIR' => '${PUBLIC_DIR}',
            'SERVER_NAME' => '${SERVER_NAME:-localhost}',
            'FASTCGI_PASS' => 'php:9000'
        ],
        'networks' => [
            'backend',
        ],

    ];


    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    public function __toString(): string
    {
        return $this->getServiceName();
    }


    /**
     * @return mixed
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getUsedEnvKeys(): array
    {
        return self::USED_ENV_KEYS;
    }

    public function after()
    {
        copyDirectoryWithFilesRecursive(
            __DIR__ . '/../../../../files/docker/nginx',
            getenv('ROOT_PATH') . '/docker/nginx'
        );
    }

    public function before()
    {
        $registeredServices = DockerCompose::getServices(true);

        foreach ($registeredServices as $service) {
            if (in_array($service, self::POSSIBLE_DEPEND_SERVICES, true)) {
                $this->configuration['depends_on'][] = DockerCompose::getServiceByKey($service)->getName();
            }
        }

        $phpService = DockerCompose::getServiceByKey(Services\Php::class);
        $this->configuration['environment']['FASTCGI_PASS'] = sprintf('%s:9000', $phpService->getName());

        if (empty($this->configuration['depends_on'])) {
            unset($this->configuration['depends_on']);
        }
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