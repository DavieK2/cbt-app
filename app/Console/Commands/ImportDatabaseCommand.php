<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportDatabaseCommand extends Command
{
    protected $signature = 'app:import-database';
    protected $description = 'Command description';

    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        // DB::statement('DROP DATABASE cbt');
        DB::unprepared(file_get_contents(base_path('cbt.sql')));
    }
}
