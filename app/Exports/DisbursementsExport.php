<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DisbursementsExport implements FromCollection, WithHeadings
{
    protected $disbursements;

    public function __construct($disbursements)
    {
        $this->disbursements = $disbursements;
    }

    public function collection()
    {
        return $this->disbursements->map(function ($loan) {
            return [
                'Disbursement Date' => $loan->disbursed_on,
                'Period (Months)' => $loan->period,
                'Customer Name' => $loan->customer->name ?? 'N/A',
                'Application Date' => $loan->date_applied,
                'Loan Product' => $loan->product->name ?? 'N/A',
                'Disbursed Amount' => $loan->amount,
                'Amount To Pay' => $loan->amount_total,
                'Branch' => $loan->branch->name ?? 'N/A',
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Disbursement Date',
            'Period (Months)',
            'Customer Name',
            'Application Date',
            'Loan Product',
            'Disbursed Amount',
            'Amount To Pay',
            'Branch',
        ];
    }
}
