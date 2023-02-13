<?php

namespace App\Exports;

use App\Models\Contract;
use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class ContractsExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::whereHas('contracts', function($query) {
            $query->whereIn('status', [Contract::STATUS_SIGNED, Contract::STATUS_APPROVED, Contract::STATUS_SUBMITTED]);
        })->get();
    }

    public function map($row) : array {
        return [
            null,
            null,
            $row->name,
            $row->surname,
            $row->email,
            3916
        ];
    }

    public function headings() : array {
        return [
            'user_membership_id',
            'user_id',
            'member_first_name',
            'member_last_name',
            'member_email',
            'membership_plan_id',
        ];
    }
}
