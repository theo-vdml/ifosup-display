<?php

namespace App\Http\Controllers;

use App\Services\SchedulerSheetParser;
use Illuminate\Support\Facades\Storage;

class SchedulerParserController extends Controller
{
    protected SchedulerSheetParser $parser;

    public function __construct(SchedulerSheetParser $parser)
    {
        $this->parser = $parser;
    }

    public function debug()
    {
        $filename = 'planning.xlsx';

        if (!Storage::exists($filename)) {
            return "Le fichier n'existe pas dans storage/app/" . $filename;
        }

        // PhpSpreadsheet a besoin d'un chemin absolu
        $path = storage_path('app/private/' . $filename);

        // On lance le parse avec l'année de départ 2025
        $results = $this->parser->parse($path, 2025);

        return dd($results);
    }
}
