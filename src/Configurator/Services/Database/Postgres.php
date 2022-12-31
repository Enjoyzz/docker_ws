<?php

namespace Enjoys\DockerWs\Configurator\Services\Database;

use Enjoys\DockerWs\Configurator\Choice\Back;
use Enjoys\DockerWs\Configurator\ServiceInterface;
use Enjoys\DockerWs\Configurator\Services\Database\Postgres as PostgresSQL;
use Enjoys\DockerWs\Configurator\Versioned;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class Postgres implements Versioned
{

    public function __toString(): string
    {
        return 'PostgreSQL';
    }

    /**
     * @return ServiceInterface|Back
     */
    public function selectVersion(
        HelperInterface $helper,
        InputInterface $input,
        OutputInterface $output
    ): Back|ServiceInterface {
        $question = new ChoiceQuestion(
            'Select Version of PostgreSQL',
            [
                new Back(),
                new PostgresSQL\v15(),
            ],
            0
        );
        $question->setErrorMessage('Choice %s is invalid.');

        return $helper->ask($input, $output, $question);
    }

}