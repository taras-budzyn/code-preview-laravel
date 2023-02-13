<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\User;
use App\Models\Contract;
use App\Models\Invoice;
use Illuminate\Support\Carbon;

class updateBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:update {csvContracts} {csvBalance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the user balance/invoice amount paid balance based on provided contract balances';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userBalances = [];
        $i = 0;

        $csvContracts = $this->argument('csvContracts');
        $csvBalance = $this->argument('csvBalance');
        $contractIds = array_map(function($el) {return (int) $el;}, explode(',', $csvContracts));
        $balances = array_map(function($el) {return (float) $el;}, explode(',', $csvBalance));

        if (count($contractIds) === count($balances)) {
            //dd($contractIds, $balanceIds);
            $this->info('Count contracts - ' . count($contractIds));

            foreach(Contract::whereIn('id', $contractIds)->orderBy('id', 'asc')->withTrashed()->get() as $k => $contract) {
                //$this->info($contract->user->id . '|' . $contract->id . '|' . $balances[$k]);
                if (!in_array($contract->user->id, array_keys($userBalances))) {
                    $userBalances[$contract->user->id] = ['user_id' => $contract->user->id, 'balance' => $balances[$k], 'contracts' => [$contract->id]];
                } else {
                    $userBalances[$contract->user->id]['balance'] = $userBalances[$contract->user->id]['balance'] + $balances[$k];
                    $userBalances[$contract->user->id]['contracts'][] = $contract->id;
                }
            }
            $this->info('Count users with balance provided - ' . count($userBalances));

            foreach ($userBalances as $user) {
                $i++;
                $this->info('#' . $i . '| Processing user  - ' . $user['user_id'] . '| Balance - ' .  $user['balance']);
                $amountUnpaid = $user['balance'];
                foreach(Invoice::whereIn('contract_id', $user['contracts'])->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PAID])->orderBy('id', 'desc')->get() as $invoice) {
                    if ($amountUnpaid <= 0 || $invoice->total < 0) {
                        $this->info('| Processing negative balance or negative invoice. Make invoice as paid fully #' . $invoice->id);
                        $invoice->amount_paid = $invoice->total;
                        $invoice->status = Invoice::STATUS_PAID;
                        $invoice->paid_at = Carbon::now();
                        $invoice->save();
                    } else {
                        if ($amountUnpaid >= $invoice->total) {
                            $this->info('| Processing positive balance. Invoice #' . $invoice->id . ' with total as [' . $invoice->total . '] as unpaid for full amount');
                            $invoice->amount_paid = 0;
                            $invoice->status = Invoice::STATUS_SENT;
                            $invoice->paid_at = null;
                            $invoice->save();
                        } else {
                            $this->info('| Processing positive balance. Invoice #' . $invoice->id . ' with total as [' . $invoice->total . '] as partly unpaid for ' . $amountUnpaid);
                            $invoice->amount_paid = $invoice->total - $amountUnpaid;
                            $invoice->status = Invoice::STATUS_SENT;
                            $invoice->paid_at = null;
                            $invoice->save();
                        }
                        $amountUnpaid -= $invoice->total;
                    }
                }

                $this->info('Collect all invoices for contracts for that user that left, and wasn\'t processed. Should be marked as paid if already sent.');
                $leftContracts = Contract::whereNotIn('id', $user['contracts'])->where('user_id', $user['user_id'])->get()->pluck('id')->all();
                $this->info('Found ' . count($leftContracts) . ' contracts.');
                foreach (Invoice::whereIn('contract_id', $leftContracts)->whereIn('status', [Invoice::STATUS_SENT])->orderBy('id', 'desc')->get() as $invoice) {
                    $this->info('Fully paid left invoice #' . $invoice->id);
                    $invoice->amount_paid = $invoice->total;
                    $invoice->status = Invoice::STATUS_PAID;
                    $invoice->paid_at = Carbon::now();
                    $invoice->save();
                }

                $this->info('| Overwrite user balance');
                $userRecord = User::findOrFail($user['user_id']);
                $userRecord->balance = ($user['balance'] <= 0) ? $user['balance'] : 0;
                $userRecord->draft_balance = $user['balance'];
                $userRecord->save();
            }

            $this->info('Processed left users.');
            
            $leftContracts = Contract::whereNotIn('user_id', array_keys($userBalances))->get()->pluck('id')->all();
            $this->info('Found ' . count($leftContracts) . ' contracts for users that wasn\'t provided in list.');
            User::whereNotIn('id', array_keys($userBalances))->update(['balance' => 0, 'draft_balance' => 0]);
            
            foreach (Invoice::whereIn('contract_id', $leftContracts)->whereIn('status', [Invoice::STATUS_SENT])->orderBy('id', 'desc')->get() as $invoice) {
                $this->info('Fully paid left invoice #' . $invoice->id);
                $invoice->amount_paid = $invoice->total;
                $invoice->status = Invoice::STATUS_PAID;
                $invoice->paid_at = Carbon::now();
                $invoice->save();
            }
        } else {
            $this->info('Contracts and balances row count should be the same! Skipping!');
        }
    }
}
