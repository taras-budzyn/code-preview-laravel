<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Imports\NvmImport;

class ImportNvm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importing:nvm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports nvm from excel file';

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
        $file = storage_path('app/nvm/nvm_import.xlsx');
        $this->output->title('Starting import');
        (new NvmImport)->withOutput($this->output)->import($file);
        $this->output->success('Import successful');
    }
}
