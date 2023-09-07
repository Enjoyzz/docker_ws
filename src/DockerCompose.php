<?php

declare(strict_types=1);


namespace Enjoys\DockerWs;


use Enjoys\DockerWs\Services\NullService;
use Enjoys\DockerWs\Services\ServiceInterface;
use Symfony\Component\Yaml\Yaml;

final class DockerCompose
{
    private static string $version = '3.7';

    /**
     * @var ServiceInterface[]
     */
    private static array $services = [];

    public static function addService(?ServiceInterface $service): void
    {
        if ($service::class === NullService::class || $service === null) {
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

            $services = self::prepareComposeServices(self::$services);

            return Yaml::dump(
                array_merge(
                    ['version' => self::$version],
                    ['networks' => Utils::collectNetworksFromServices($services)],
                    ['services' => $services]
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