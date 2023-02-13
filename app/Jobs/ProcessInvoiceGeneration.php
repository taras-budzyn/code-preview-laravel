<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
// TODO: add it after upgrade Laravel to 8 version
//use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Services\InvoiceGenerationService;

class ProcessInvoiceGeneration implements ShouldQueue //, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $initiatorId, $dateIssued, $billingFromDate, $billingToDate;

    /** @var InvoiceGenerationService */
    private $invoiceGenerationService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($initiatorId, $dateIssued, $billingFromDate, $billingToDate)
    {
        $this->initiatorId = $initiatorId;
        $this->dateIssued = $dateIssued;
        $this->billingFromDate = $billingFromDate;
        $this->billingToDate = $billingToDate;
        $this->invoiceGenerationService = new InvoiceGenerationService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::channel('invoiceGeneration')->info('Data passed to generation', [
                'initiatorId' => $this->initiatorId,
                'dateIssued' => $this->dateIssued,
                'billingFromDate' => $this->billingFromDate,
                'billingToDate' => $this->billingToDate
            ]);
            $this->invoiceGenerationService->startGeneration(
                $this->initiatorId,
                $this->dateIssued,
                $this->billingFromDate,
                $this->billingToDate
            );
        } catch (\Exception $e) {
            Log::channel('invoiceGeneration')->info('Unable to dispatch invoice generation.', [
                'billingFromDate'   => $this->billingFromDate,
                'billingFromDate'    => $this->billingToDate,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ]);
    
            $this->release();
        }
    }
}
