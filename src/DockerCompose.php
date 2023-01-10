<?php

declare(strict_types=1);


namespace Enjoys\DockerWs;


use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Yaml\Yaml;

final class DockerCompose
{
    private static array $version = [
        'version' => '3.7'
    ];

    private static array $networks = [
        'networks' => [
            'backend' => []
        ]
    ];

    /**
     * @var ServiceInterface[]
     */
    private static array $services = [];

    public static function addService(ServiceInterface $service): void
    {
        if ($service instanceof NullService){
            return;
        }
        self::$services[$service::class] = $service;
    }

    public static function build(): string
    {
        try {

            foreach (self::$services as $service) {
                $service->_before();
            }

            return Yaml::dump(
                array_merge(
                    self::$version,
                    self::$networks,
                    ['services' => self::prepareComposeServices(self::$services)]
                ),
                6,
                2,
                Yaml::DUMP_OBJECT_AS_MAP
            );
        } finally {
            foreach (self::$services as $service) {
                $service->_after();
            }
        }
    }

    /**
     * @param ServiceInterface[] $services
     * @return array
     */
    private static function prepareComposeServices(array $services): array
    {
        $result = [];
        foreach ($services as $service) {
            $result[$service->getServiceName()] = $service->getConfiguration();
        }
        return $result;
    }


    /**
     * @param bool $only_keys
     * @return string[]|ServiceInterface[]
     */
    public static function getServices(bool $only_keys = false): array
    {
        return $only_keys ? array_keys(self::$services) : self::$services;
    }

    public static function getServiceByKey(string $key): ?ServiceInterface
    {
        return self::$services[$key] ?? null;
    }

}