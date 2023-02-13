<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class SwedbankXmlParser extends AbstractXmlParser
{

    protected $itemKeys = [
        'transaction_id' => 'NtryRef',
        'amount' => 'Amt',
        'transaction_type' => 'CdtDbtInd',
        'payment_at' => 'BookgDt.Dt',
        'payer' => 'NtryDtls.TxDtls.RltdPties.Dbtr.Nm',
        'receiver' => 'NtryDtls.TxDtls.RltdPties.Cdtr.Nm',
        'iban' => [
            'NtryDtls.TxDtls.RltdPties.DbtrAcct.Id.IBAN',
            'NtryDtls.TxDtls.RltdPties.CdtrAcct.Id.Othr.Id'
        ],
        'note' => 'NtryDtls.TxDtls.RmtInf.Ustrd'
    ];

    protected $rules = [
        'transaction_id' => 'required',
        'amount' => 'required',
        'transaction_type' => 'required',
        'payment_at' => 'required',
        'iban' => 'required',
        'bank' => 'required',
    ];

    protected $types = [
            'CRDT' => Payment::TRANSACTION_TYPE_K,
            'DBIT' => Payment::TRANSACTION_TYPE_D,
    ];

    protected function parseItem(array $data, $index)
    {
        $item = [];

        foreach ($this->itemKeys as $key => $value) {
            if (is_array($value)) {
                $default =  Arr::get($data, $value[0]) ?? Arr::get($data, $value[1]);
                $item = Arr::add($item, $key, $default);
            } else {
                $item = Arr::add($item, $key, Arr::get($data, $value));
            }

        }

        $item['transaction_type'] = Arr::get($this->types, $item['transaction_type'], $item['transaction_type']);

        $item = Arr::add($item, 'type', Payment::TYPE_IMPORTED);
        $item = Arr::add($item, 'bank', Payment::BANK_SWEDBANK);

        if ($this->validateItem($item, $index)) {

            $data = Carbon::now();
            $item['payment_at'] = new Carbon($item['payment_at']);
            $item['created_at'] = $data;
            $item['updated_at'] = $data;
            $item['amount'] = abs($item['amount']);
            $item['user_id'] =  backpack_user()->id;

            array_push($this->items, $item);
        }

    }


}
