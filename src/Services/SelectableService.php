<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Services;


interface SelectableService extends \Stringable
{
    public function getSelectedService(): ?ServiceInterface;
}