<?php

namespace Enjoys\DockerWs\Configurator;

use Enjoys\DockerWs\Configurator\Choice\Back;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Versioned extends \Stringable
{
    /**
     * @return ServiceInterface|Back
     */
    public function selectVersion(
        HelperInterface $helper,
        InputInterface $input,
        OutputInterface $output
    ): Back|ServiceInterface;
}