<?php

declare(strict_types=1);

namespace App\Service\Sudoku;

use App\Libs\Sudoku\SudokuValidator;

class SudokuCsvFileReader
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly SectorValidator $sectorValidator,
    ) {}

    public function validateFile(string $fileName): true|string
    {
        $validator = new SudokuValidator($this->sectorValidator);
        $realpath = realpath("{$this->targetDirectory}/{$fileName}");
        if ($realpath === false) {
            return "File {$fileName} does not exist";
        }

        $stream = fopen($realpath, 'r');
        while (($data = fgetcsv($stream)) !== false) {
            if (!$validator->validateRow($data)) {
                return $validator->getLastError();
            }
        }
        fclose($stream);
        $validator->finish();
        if ($validator->getLastError() !== null) {
            return $validator->getLastError();
        }

        return true;
    }
}
