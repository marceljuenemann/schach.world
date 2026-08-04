<?php

namespace Nsv\Dwz\Command;


use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;

#[AsCommand(
  name: 'dwz:update',
  description: 'Fetches the latest player and club database from the DSB'
)]
class DwzUpdateCommand extends Command
{
  function __construct(private string $projectDir) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln('Downloading latest DWZ database...');

    $tmp = tmpfile();
    $tmpPath = stream_get_meta_data($tmp)['uri'];

    if (file_put_contents($tmpPath, fopen($_ENV['DWZ_DATABASE_URL'], 'r')) === false) {
      $output->writeln('<error>Failed to download DWZ database.</error>');
      fclose($tmp);
      return Command::FAILURE;
    }

    $zip = new ZipArchive();
    if ($zip->open($tmpPath) !== true) {
      $output->writeln('<error>Failed to open DWZ database archive.</error>');
      fclose($tmp);
      return Command::FAILURE;
    }

    $clubsStream = $zip->getStream('vereine.csv');
    if ($clubsStream === false) {
      $output->writeln('<error>vereine.csv not found in DWZ database archive.</error>');
      $zip->close();
      fclose($tmp);
      return Command::FAILURE;
    }
    $this->updateClubs($clubsStream, $output);
    fclose($clubsStream);

    $zip->close();
    fclose($tmp);

    $output->writeln('DWZ database update complete.');
    return Command::SUCCESS;
  }

  private function updateClubs($stream, OutputInterface $output): void {
    $header = fgetcsv($stream, separator: ';');

    $count = 0;
    while (($row = fgetcsv($stream, separator: ';')) !== false) {
      $count++;
    }

    $output->writeln(sprintf('Found %d clubs.', $count));
  }
}
