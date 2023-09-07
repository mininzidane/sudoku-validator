<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Sudoku\SudokuCsvFileReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'sudoku:check-csv-file')]
class CheckSudokuCSVFileCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly SudokuCsvFileReader $sudokuCsvFileReader,
    )
    {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Parse CSV from public/csv folder')
            ->addArgument('filename', InputArgument::REQUIRED, 'Filename of the CSV in public/csv')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->sudokuCsvFileReader->validateFile($input->getArgument('filename'));

        if ($result !== true) {
            $this->io->writeln(sprintf('Sudoku file is invalid: %s', $result));
            return Command::FAILURE;
        }

        $this->io->writeln('Sudoku file is correct');
        return Command::SUCCESS;
    }
}
