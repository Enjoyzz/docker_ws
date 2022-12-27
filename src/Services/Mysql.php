<?php

namespace Enjoys\DockerWs\Services;

use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Enjoys\DockerWs\Services\Mysql as Version;

class Mysql implements Versioned
{

    public function __toString(): string
    {
        return 'MySQL';
    }

    /**
     * @return ServiceInterface|Back
     */
    public function selectVersion(HelperInterface $helper, InputInterface $input, OutputInterface $output)
    {
        $question = new ChoiceQuestion(
            'Select Version of MySQL',
            [
                new Back(),
                new Version\Mysql57(),
                new Version\Mysql80()
            ],
            0
        );
        $question->setErrorMessage('Choice %s is invalid.');

        return $helper->ask($input, $output, $question);
    }
}
