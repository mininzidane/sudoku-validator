<?php

declare(strict_types=1);

namespace App\Tests\Libs\Sudoku;

use App\Libs\Sudoku\SudokuValidator;
use App\Service\Sudoku\SectorValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SudokuValidatorTest extends KernelTestCase
{
    private SectorValidator $sectorValidator;
    private SectorValidator $mockSectorValidator;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $container = static::getContainer();
        $this->sectorValidator = $container->get(SectorValidator::class);
        $this->mockSectorValidator = $this->getMockBuilder(SectorValidator::class)
            ->onlyMethods(['rowHasUniqueData'])
            ->getMock()
        ;
        $this
            ->mockSectorValidator
            ->method('rowHasUniqueData')
            ->willReturn(true)
        ;
    }

    /**
     * @dataProvider squareSudokuProvider
     */
    public function testSquareSudokuWithAllElementsCorrect(int $size, bool $correct): void
    {
        $validator = new SudokuValidator($this->mockSectorValidator);
        $row = range(1, $size);
        foreach ($row as $ignored) {
            if ($validator->validateRow($row)) {
                if (!$correct) {
                    self::fail('Must be incorrect row');
                }
            } else {
                if ($correct) {
                    self::fail($validator->getLastError());
                } else {
                    echo "\n" . $validator->getLastError();
                    self::assertTrue(true);
                    return;
                }
            }
        }

        $validator->finish();
        if ($validator->getLastError() === null) {
            self::assertTrue(true);
            return;
        }

        self::fail('Sudoku incorrect');
    }

    public function squareSudokuProvider(): \Generator
    {
        yield [4, true]; // 2x2
        yield [5, false];
        yield [9, true]; // 3x3
        yield [10, false];
        yield [16, true]; // 4x4
        yield [17, false];
        yield [25, true]; // 5x5
        yield [26, false];
        yield [81, true]; // 9x9
        yield [25*25, true]; // 25x25
        yield [30*30, true]; // 30x30
//        yield [10000, true]; // 100x100
    }

    /**
     * @dataProvider sudokuIncorrectProvider
     */
    public function testIncorrectSudoku(callable $sudokuGenerator): void
    {
        $validator = new SudokuValidator($this->sectorValidator);
        /** @var \Generator $data */
        $data = call_user_func($sudokuGenerator);
        while ($row = $data->current()) {
            if (!$validator->validateRow($row)) {
                echo "\n" . $validator->getLastError();
                self::assertTrue(true);
                return;
            }
            $data->next();
        }
        $validator->finish();
        if ($validator->getLastError() !== null) {
            echo "\n" . $validator->getLastError();
            self::assertTrue(true);
            return;
        }

        self::fail('Sudoku incorrect');
    }

    public function sudokuIncorrectProvider(): \Generator
    {
        yield [[$this, 'getWeirdSudoku']];
        yield [[$this, 'getWeirdSudoku2']];
        yield [[$this, 'getWeirdSudoku3']];
        yield [[$this, 'getWeirdSudoku4']];
        yield [[$this, 'getSmallIncorrectNumbersSudoku']];
        yield [[$this, 'getSmallIncorrectSudoku']];
        yield [[$this, 'getSudokuWithOnlyVerticalRowsIncorrect']];
        yield [[$this, 'getIncorrectSudokuVerticalSize']];
        yield [[$this, 'getIncorrectSudokuNotSquareSize']];
    }

    private function getWeirdSudoku(): \Generator
    {
        yield [1,2];
    }

    private function getWeirdSudoku2(): \Generator
    {
        yield [1];
    }

    private function getWeirdSudoku3(): \Generator
    {
        yield [];
    }

    private function getWeirdSudoku4(): \Generator
    {
        yield [1,2,3,4];
        yield [];
    }

    private function getSmallIncorrectNumbersSudoku(): \Generator
    {
        yield [1,2,3,4];
        yield [3,4,1,2];
        yield [1,5,3,4]; // wrong number
        yield [2,3,1,4];
    }

    private function getSmallIncorrectSudoku(): \Generator
    {
        yield [1,2,3,4];
        yield [3,4,1,2];
        yield [1,2,3,4]; // incorrect square sections x=1,y=3 (and x=3,y=3)
        yield [2,3,1,4];
    }

    // horizontal and square sections are unique, vertical - not
    private function getSudokuWithOnlyVerticalRowsIncorrect(): \Generator
    {
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
        yield [7,8,9,1,2,3,4,5,6];
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
        yield [7,8,9,1,2,3,4,5,6];
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
        yield [7,8,9,1,2,3,4,5,6];
    }

    // incorrect size: 9x8
    private function getIncorrectSudokuVerticalSize(): \Generator
    {
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
        yield [7,8,9,1,2,3,4,5,6];
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
        yield [7,8,9,1,2,3,4,5,6];
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
    }

    private function getIncorrectSudokuNotSquareSize(): \Generator
    {
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
        yield [7,8,9,1,2,3,4,5,]; // incorrect size: 8 elements
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
        yield [7,8,9,1,2,3,4,5,6];
        yield [1,2,3,4,5,6,7,8,9];
        yield [4,5,6,7,8,9,1,2,3];
    }
}
