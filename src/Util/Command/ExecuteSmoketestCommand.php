<?php

namespace Nsv\Util\Command;

use Nsv\Util\Message\SmoketestMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'testing:execute-smoketest',
    description: 'Runs smoketests you have defined',
)]
class ExecuteSmoketestCommand extends Command
{
    public function __construct(private MessageBusInterface $messageBus, private iterable $smoketestInstances)
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $classnames = [];
        // Extract the class names from $smoketestInstances
        foreach ($this->smoketestInstances as $smoketestInstance) {
          $namespaceName = get_class($smoketestInstance);
          $nameComponents = explode('\\', $namespaceName);
          $className = end($nameComponents);
          $classNames[] = end($className);
        }

        $selectedClassName = $io->choice('Provide the name of your smoke test class:', $classnames);

        $this->messageBus->dispatch(new SmoketestMessage($selectedClassName));

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
