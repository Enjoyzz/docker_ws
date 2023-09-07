<?php

namespace Enjoys\DockerWs;

class Utils
{

    public static function collectNetworksFromServices(array $services): array
    {
        $networks = [];
        foreach ($services as $service) {
            if (!array_key_exists('networks', $service)) {
                continue;
            }
            if (!is_array($service['networks'])) {
                continue;
            }
            foreach ($service['networks'] as $network) {
                $networks[] = $network;
            }
        }
        return array_fill_keys(array_unique($networks), []);
    }
}