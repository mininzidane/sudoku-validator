<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\CsvFileType;
use App\Service\FileUploader;
use App\Service\Sudoku\SudokuCsvFileReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route(path: '/validate-sudoku-csv-file', name: 'validate_sudoku_csv_file', methods: ['POST'])]
    public function validateSudokuCsvFile(Request $request, FileUploader $fileUploader, SudokuCsvFileReader $sudokuCsvFileReader): JsonResponse
    {
        $form = $this
            ->createForm(CsvFileType::class)
            ->submit($request->files->all())
        ;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('csvFile')->getData();
            if ($file) {
                $fileName = $fileUploader->upload($file);
                $result = $sudokuCsvFileReader->validateFile($fileName);

                if ($result === true) {
                    return $this->json(['status' => 'OK']);
                } else {
                    return $this->json(['status' => $result], 400);
                }
            }
        }

        return $this->json(['status' => 'Incorrect CSV file']);
    }
}
