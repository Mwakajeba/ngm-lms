<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BotAgentBankingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        $banksTz = [
            'CRDB BANK PLC',
            'NMB BANK PLC',
            'NATIONAL BANK OF COMMERCE (NBC) LIMITED',
            'ABSA BANK TANZANIA LIMITED',
            'STANDARD CHARTERED BANK TANZANIA LIMITED',
            'STANBIC BANK TANZANIA LIMITED',
            'EXIM BANK (TANZANIA) LIMITED',
            'DIAMOND TRUST BANK TANZANIA LIMITED (DTB)',
            'I&M BANK (T) LIMITED',
            'KCB BANK TANZANIA LIMITED',
            'EQUITY BANK TANZANIA LIMITED',
            'AZANIA BANK LIMITED',
            'TPB BANK PLC',
            'TIB CORPORATE BANK LIMITED',
            'UNITED BANK FOR AFRICA (UBA) TANZANIA LIMITED',
            'BANK OF BARODA (TANZANIA) LIMITED',
            'BANK OF INDIA (TANZANIA) LIMITED',
            'PEOPLE\'S BANK OF ZANZIBAR (PBZ) PLC',
            'DCB COMMERCIAL BANK PLC',
            'MKOMBOZI COMMERCIAL BANK PLC',
            'AMANA BANK LIMITED',
            'DIB BANK TANZANIA PLC',
            'ACCESS BANK TANZANIA LIMITED',
            'CITIBANK TANZANIA LIMITED'
        ];

        $mfsp = [
            'NATIONAL MICROFINANCE BANK (T) LTD.',
            'MWANGA RURAL COMMUNITY BANK',
            'KILIMANJARO COOPERATIVE BANK LIMITED',
            'MUFINDI COMMUNITY BANK LIMITED',
            'KAGERA COOPERATIVE BANK LIMITED',
            'MWANZA COOPERATIVE BANK LIMITED',
            'ARUSHA COOPERATIVE BANK LIMITED',
            'DODOMA COOPERATIVE BANK LIMITED',
            'TANGA COOPERATIVE BANK LIMITED',
            'MOROGORO COOPERATIVE BANK LIMITED',
            'IRINGA COOPERATIVE BANK LIMITED',
            'SONGEA COOPERATIVE BANK LIMITED',
            'MTWARA COOPERATIVE BANK LIMITED',
            'KIGOMA COOPERATIVE BANK LIMITED',
            'TABORA COOPERATIVE BANK LIMITED',
            'RUKWA COOPERATIVE BANK LIMITED',
            'RUVUMA COOPERATIVE BANK LIMITED',
            'MANYARA COOPERATIVE BANK LIMITED',
            'NJOMBE COOPERATIVE BANK LIMITED',
            'GEITA COOPERATIVE BANK LIMITED',
            'SIMIYU COOPERATIVE BANK LIMITED',
            'KATAVI COOPERATIVE BANK LIMITED',
            'SINGIDA COOPERATIVE BANK LIMITED',
            'PWANI COOPERATIVE BANK LIMITED',
            'DAR ES SALAAM COOPERATIVE BANK LIMITED'
        ];

        $mnos = [
            'MPESA TANZANIA LIMITED',
            'AIRTEL MONEY TANZANIA LIMITED',
            'TIGO PESA TANZANIA LIMITED',
            'HALOPESA TANZANIA LIMITED',
            'TPESA TANZANIA LIMITED',
            'EMOLA TANZANIA LIMITED',
            'T-PESA TANZANIA LIMITED'
        ];

        return view('reports.bot.agent-banking', compact('user', 'asOfDate', 'banksTz', 'mfsp', 'mnos'));
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Agent_Banking_' . $asOfDate . '.xls';
        $fullPath = base_path('resources/views/reports/bot-agent-banking.xls');
        
        if (!file_exists($fullPath)) {
            return response()->streamDownload(function () {
                echo 'Template not found';
            }, $filename);
        }
        
        return response()->streamDownload(function () use ($fullPath) {
            readfile($fullPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel'
        ]);
    }
} 