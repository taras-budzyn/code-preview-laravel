<?php

namespace App\Http\Classes;

use Illuminate\Support\Facades\Log;

use App\Models\InvoiceItem;
use App\Services\InvoiceCalculationService;

use App\Http\Classes\InvoiceGeneration;

class BaseContractPrice implements LineItemHandler
{
    // method for handler
    public function handle(InvoiceGeneration $obj, \Closure $next)
    {
        Log::channel('invoiceGeneration')->info('Process line item [' . get_class($this) . ']: ', ['invoiceId' => $obj->invoice->id]);
        Log::channel('invoiceGeneration')->info('Get price for contract - ' . $obj->contract->memberSubscription->price);

        $calcPrice = InvoiceCalculationService::calcFractionalMonth($obj->contract->memberSubscription->price, $obj->calcInvoiceBillingDate(), $obj->billingRange);
        Log::channel('invoiceGeneration')->info(
            'Calc price for item  - ' . $calcPrice
        );

        $this->createLineItem($obj, $calcPrice);

        return $next($obj);
    }

    private function createLineItem($obj, $calcPrice)
    {
        InvoiceItem::create([
            'invoice_id' => $obj->invoice->id,
            'type' => InvoiceItem::TYPE_BASE_PRICE,
            'text' => __('Base contact price'),
            'amount' => 1,
            'price_per_unit' => $calcPrice,
            'status' => invoiceItem::STATUS_AUTO_CREATED,
            'nvs_contract_id' => null,
            'compensations_id' => null,
            'discount_id' => null,
            'pause_id' => null
        ]);
    }
}
