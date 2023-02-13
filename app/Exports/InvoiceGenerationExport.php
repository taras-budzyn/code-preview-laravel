<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;

class InvoiceGenerationExport implements FromArray, WithHeadings
{
    use Exportable;

    protected $contracts = [];
    protected $skippedContracts = [];
    protected $invoicesReport = [];
    protected $generatedInvoiceIds = [];

    public function __construct($contracts, $generatedInvoiceIds, $skippedContracts)
    {
        $this->contracts = $contracts;
        $this->generatedInvoiceIds = $generatedInvoiceIds;
        $this->skippedContracts = $skippedContracts;
        $this->invoicesReport = $this->prepareInvoices();
    }

    private function prepareInvoices()
    {
        $rows = [];
        foreach ($this->contracts as $i => $contract) {
            $rows[] = [
                $contract->id,
                $this->generatedInvoiceIds[$i]['id'],
                $contract->member->full_name,
                $contract->user->full_name,
                'Success',
                $this->generatedInvoiceIds[$i]['date'],
            ];
        }
        foreach ($this->skippedContracts as $i => $contract) {
            $rows[] = [
                $contract->id,
                '-',
                $contract->member->full_name,
                $contract->user->full_name,
                'Skipped',
                '-',
            ];
        }
        return $rows;
    }

    public function array(): array
    {
        return $this->invoicesReport;
    }

    public function headings() : array {
        return [
            'Contract number',
            'Invoice number',
            'Member name and surname',
            'Client name and surname',
            'Generation status',
            'Generation date and time',
        ];
    }
}
