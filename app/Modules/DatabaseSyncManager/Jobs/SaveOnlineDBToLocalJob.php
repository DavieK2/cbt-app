<?php

namespace App\Modules\DatabaseSyncManager\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaveOnlineDBToLocalJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
 
    public function __construct(protected array $row, protected string $table){}

    public function handle()
    {
        Schema::disableForeignKeyConstraints();

        $row = collect($this->row)->map(function($value) {

            $value = @unserialize($value) ? unserialize($value) : $value;

            if( is_array($value) ){
                $value = json_encode($value);
            }

            $value = $value == "" ? null : $value;

            return $value;

        })->toArray();

        $row['is_synced'] = true;

        if( DB::table($this->table)->where('uuid', $row['uuid'] )->first() ) return;

        DB::table($this->table)->insert($row);  

        Schema::enableForeignKeyConstraints();
    }
}
