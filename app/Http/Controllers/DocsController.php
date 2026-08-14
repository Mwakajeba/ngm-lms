<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Support\NextGenerationUatChecklist;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DocsController extends Controller
{
    /**
     * Product / system feature overview as PDF (for proposals, invoices, customer packs).
     * Uses the signed-in user's company in the header when available.
     */
    public function systemFeaturesPdf(): Response
    {
        $company = auth()->user()->company ?? Company::query()->first();
        $generatedAt = now();

        $pdf = Pdf::loadView('docs.system-features-pdf', compact('company', 'generatedAt'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('System_Features_Overview.pdf');
    }

    /**
     * Interactive UAT checklist (ticks & comments saved in browser localStorage).
     */
    public function uatChecklistHtml(): View
    {
        return view('docs.uat-checklist', [
            'data' => NextGenerationUatChecklist::data(),
            'generatedAt' => now(),
        ]);
    }

    /**
     * Printable UAT checklist PDF (NEXTGENERATION MICROFINANCE branding).
     */
    public function uatChecklistPdf(): Response
    {
        $data = NextGenerationUatChecklist::data();
        $generatedAt = now();

        $pdf = Pdf::loadView('docs.uat-checklist-pdf', compact('data', 'generatedAt'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('NGML_UAT_Feature_Checklist.pdf');
    }
}
