<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Nvm;

class NvmContractIntegrityCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importing:nvm-check {--no-db}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check nvms table for contracts that  does not exist in contract table and deletes them';

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
        if(!$this->option('no-db')) {
            Nvm::whereDoesntHave('memberContract')->delete();
        }else{
            $invalid = Nvm::whereDoesntHave('memberContract')->pluck('id');
            $this->info($invalid);
        }
    }
}
