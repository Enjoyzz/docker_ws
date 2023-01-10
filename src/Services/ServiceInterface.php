<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


interface ServiceInterface
{

    public function getConfiguration();

    public function _after();

    public function _before();
}