<?php

namespace Nsv\Dwz\Command;


use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Entity\Club;
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
  function __construct(private string $projectDir, private EntityManagerInterface $em) {
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
    $output->writeln(sprintf('Updating clubs...'));

    $header = fgetcsv($stream);
    $zpsIndex = array_search('ZPS-Nummer', $header);
    $parentIndex = array_search('UebergeordneterVerband', $header);
    $nameIndex = array_search('Vereinsname', $header);
    if ($zpsIndex === false || $parentIndex === false || $nameIndex === false) {
      throw new \RuntimeException('vereine.csv is missing expected columns');
    }

    $table = $this->em->getClassMetadata(Club::class)->getTableName();
    $this->em->getConnection()->executeStatement("TRUNCATE TABLE $table");

    $count = 0;
    while (($row = fgetcsv($stream)) !== false) {
      $club = new Club();
      $club->zps = $row[$zpsIndex];
      $club->name = mb_convert_encoding($row[$nameIndex], 'UTF-8', 'ISO-8859-1');

      if (str_pad($row[$parentIndex], 5, '0', STR_PAD_RIGHT) == $club->zps || $club->zps === '70001') {
        $output->writeln(sprintf('Skipping %s as it is not a club', $club->name));
        continue;
      }

      $this->em->persist($club);
      $count++;
    }
    $this->em->flush();

    $output->writeln(sprintf('Imported %d clubs.', $count));
  }
}
