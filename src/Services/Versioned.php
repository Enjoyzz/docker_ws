<?php

namespace Enjoys\DockerWs\Services;

use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Versioned extends \Stringable
{
    /**
     * @return ServiceInterface|Back
     */
    public function selectVersion(HelperInterface $helper, InputInterface $input, OutputInterface $output);
}