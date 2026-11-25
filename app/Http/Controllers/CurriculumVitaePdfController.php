<?php

namespace App\Http\Controllers;

use App\Support\CurriculumVitae;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CurriculumVitaePdfController extends Controller
{
    /**
     * Download the CV as an A4 PDF built from the same data as the CV page.
     */
    public function __invoke(): Response
    {
        $cv = CurriculumVitae::data();
        $name = $cv['about']['name'] ?? config('app.name');

        return Pdf::loadView('pdf.cv', [...$cv, 'photo' => CurriculumVitae::photo($cv['about']['photo'] ?? null)])
            ->setPaper('a4')
            ->download(Str::slug($name).'-cv.pdf');
    }
}
