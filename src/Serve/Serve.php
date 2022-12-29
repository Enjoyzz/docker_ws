<?php

declare(strict_types=1);


namespace Enjoys\DockerWs\Serve;


use Enjoys\Dotenv\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

abstract class Serve extends Command
{
    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $dotenv = new Dotenv((getenv('ROOT_PATH') ?: '.') . '/.docker.env');
        $dotenv->loadEnv();

        try {
            $userName = trim(
                Process::fromShellCommandline('id -un')->mustRun()->getOutput()
            );
            $userId = trim(
                Process::fromShellCommandline('id -u')->mustRun()->getOutput()
            );
        } catch (\Exception) {
            $userName = 'username';
            $userId = 1000;
        }

        $_ENV['TZ'] = $_ENV['TZ'] ?? 'UTC';
        $_ENV['USER_NAME'] = $_ENV['USER_NAME'] ?? $userName;
        $_ENV['USER_ID'] = $_ENV['USER_ID'] ?? $userId;
        $_ENV['WORK_DIR'] = $_ENV['WORK_DIR'] ?? '/var/www';
    }
}
