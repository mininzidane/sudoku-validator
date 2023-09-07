<?php

declare(strict_types=1);

namespace App\Libs\Sudoku;

use App\Service\Sudoku\SectorValidator;

class SudokuValidator
{
    private int $currentLine = -1;
    private ?int $size = null;
    private array $rows = [];
    private ?string $lastError = null;

    public function __construct(
        private readonly SectorValidator $sectorValidator,
    ) {}

    public function validateRow(array $row): bool
    {
        $this->currentLine++;
        $size = count($row);
        if (!$this->sectorValidator->validateRowSize($size)) {
            $this->lastError = sprintf('Row #%s has invalid items count', $this->currentLine + 1);
            return false;
        }

        if ($this->size === null) {
            $this->size = $size;
        }

        if ($this->size !== $size) {
            $this->lastError = sprintf('Row #%s has different size against other rows', $this->currentLine + 1);
            return false;
        }

        if (!$this->sectorValidator->validateRowContent($row)) {
            $this->lastError = sprintf('Horizontal line #%s has incorrect numbers', $this->currentLine + 1);
            return false;
        }

        if (!$this->sectorValidator->rowHasUniqueData($row)) {
            $this->lastError = sprintf('Horizontal line #%s has repeatable elements', $this->currentLine + 1);
            return false;
        }

        $this->rows[$this->currentLine] = $row;

        $sectorSize = (int)sqrt($size);
        if (($this->currentLine + 1) % $sectorSize !== 0) {
            return true;
        }

        $squareSectorData = $this->getSquareSectorData($this->currentLine, $sectorSize);
        foreach ($squareSectorData as $i => $squareSectorDatum) {
            if (!$this->sectorValidator->rowHasUniqueData($squareSectorDatum)) {
                $this->lastError = sprintf('Block x=%s y=%s has repeatable elements', $i + 1, $this->currentLine + 2 - $sectorSize);
                return false;
            }
        }

        return true;
    }

    public function finish(): void
    {
        if (!$this->sectorValidator->validateRowSize($this->currentLine + 1)) {
            $this->lastError = 'Vertical rows have invalid size';
            return;
        }

        // final checking for all vertical rows
        foreach ($this->getVerticalRowsData() as $i => $verticalRowsDatum) {
            if (!$this->sectorValidator->rowHasUniqueData($verticalRowsDatum)) {
                $this->lastError = sprintf('Vertical line #%s has repeatable elements', $i + 1);
                return;
            }
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function getSquareSectorData(int $bottomY, int $sectorSize): \Generator
    {
        $topY = $bottomY - $sectorSize + 1;
        $xLeft = 0;
        while (true) {
            $output = [];
            $currentTopY = $topY;
            while ($currentTopY <= $bottomY) {
                $output[] = array_slice($this->rows[$currentTopY], $xLeft, $sectorSize);
                $currentTopY++;
            }

            yield ($xLeft / $sectorSize) => array_merge(...$output);
            $xLeft += $sectorSize;
            if (count($this->rows[$currentTopY - 1]) === $xLeft) {
                break;
            }
        }
    }

    private function getVerticalRowsData(): \Generator
    {
        $size = count($this->rows[$this->currentLine]);
        for ($i = 0; $i < $size; $i++) {
            $output = [];
            for ($k = 0; $k < $size; $k++) {
                $output[] = $this->rows[$k][$i];
            }
            yield $i => $output;
        }
    }
}
