<?php

declare(strict_types=1);

namespace App\Service\Sudoku;

class SectorValidator
{
    public function rowHasUniqueData(array $data): bool
    {
        $dataUnique = array_unique($data);
        return count($dataUnique) === count($data);
    }

    public function validateRowContent(array $data): bool
    {
        // size/2*[1+lastElem]
        sort($data);
        $size = count($data);
        return (int) $data[0] === 1 && (int) $data[$size - 1] === $size && (int) array_sum($data) === (int) ($size / 2 * (1 + $size));
    }

    public function validateRowSize(int $size): bool
    {
        $sectorSize = sqrt($size);
        return (float)(int)$sectorSize === $sectorSize && $sectorSize > 1;
    }
}
