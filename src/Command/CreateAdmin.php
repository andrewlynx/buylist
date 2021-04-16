<?php

namespace App\Command;

use App\UseCase\User\RegistrationHandler;
use PHPStan\Command\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CreateAdmin extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:create-admin';

    /**
     * @var RegistrationHandler
     */
    private $registrationHandler;

    /**
     * @param RegistrationHandler $registrationHandler
     */
    public function __construct(RegistrationHandler $registrationHandler)
    {
        parent::__construct(self::$defaultName);
        $this->registrationHandler = $registrationHandler;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this->addArgument(
            'user',
            InputArgument::REQUIRED,
            'User email or nickname'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $username = $input->getArgument('user');
            $user = $this->registrationHandler->makeAdmin(
                is_array($username) ? array_pop($username) : $username
            );
            $output->writeln(sprintf('User %s is admin now', $user->getEmail()));

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln(sprintf('Error: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
