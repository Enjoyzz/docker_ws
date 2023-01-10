<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services\Http\Apache\Version;


use Enjoys\DockerWs\Services\ServiceInterface;

final class Apache_v2_4 implements ServiceInterface, \Stringable
{
    private string $serviceName = 'apache';

    public function __toString(): string
    {
        return 'Apache v2.4';
    }

    private array $configuration = [
        'build' => [
            'context' => './docker/apache',
            'dockerfile' => 'Dockerfile'
        ],
        'environment' => [
            'PUBLIC_DIR' => '${PUBLIC_DIR}',
            'SERVER_NAME' => '${SERVER_NAME:-localhost}',
            'FASTCGI_PASS' => 'php:9000',
            'LISTEN' => 80,
        ],

        'volumes' => [
            './.data/logs/apache/:/var/log/',
            './:${WORK_DIR}',
        ],
        'depends_on' => [],
        'ports' => [
            '80:80',
        ],
        'networks' => [
            'backend',
        ],

    ];

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function _after()
    {
        // TODO: Implement _after() method.
    }

    public function _before()
    {
        // TODO: Implement _before() method.
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     */
    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }


}