<?php

namespace Nsv\Util\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'testing:execute-smoketest',
    description: 'Runs smoketests you have defined',
)]
class ExecuteSmoketestCommand extends Command
{
    public function __construct(private iterable $smoketestInstances)
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $className = $io->ask('Provide the name of your smoke test class:');

        foreach ($this->smoketestInstances as $smoketestInstance) {
          //$result = $smoketestInstance->execute();
          $papa = 'rolling stone';
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
