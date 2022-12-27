<?php

namespace Enjoys\DockerWs\Services;

use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Enjoys\DockerWs\Services\Postgres as PostgresSQL;

class Postgres implements Versioned
{

    public function __toString(): string
    {
        return 'PostgreSQL';
    }

    /**
     * @return ServiceInterface|Back
     */
    public function selectVersion(HelperInterface $helper, InputInterface $input, OutputInterface $output)
    {
        $question = new ChoiceQuestion(
            'Select Version of PostgreSQL',
            [
                new Back(),
                new PostgresSQL\v15(),
                new PostgresSQL\v14(),
            ],
            0
        );
        $question->setErrorMessage('Choice %s is invalid.');

        return $helper->ask($input, $output, $question);
    }

}