<?php

namespace App\Console\Commands;

use App\Modules\CBT\Jobs\ImportStudentResultsJob;
use Illuminate\Console\Command;

class ImportResultsFromFileCommand extends Command
{
    protected $signature = 'app:import-results';
    protected $description = 'Command description';

    public function handle()
    {
       
       dispatch( new ImportStudentResultsJob() );
    }
}
