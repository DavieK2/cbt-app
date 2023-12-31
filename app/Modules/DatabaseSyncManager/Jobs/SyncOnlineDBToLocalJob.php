<?php

namespace App\Modules\DatabaseSyncManager\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncOnlineDBToLocalJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
        
            $request = Http::get(env('APP_URL').'/api/sync-to-local');
    
            $responses = $request->json();
    
            if( ! $request->ok() ){
    
                return ;
            }
    
            $jobs = [];

            foreach( $responses as $table => $response ){
    
                $jobs[] = new ProcessSyncOnlineDBToLocalJob($table, $response);
            
            }
            
            $this->batch()->add( $jobs );


        } catch (\Throwable $th) {
            
            Log::info($th);
        }
    }

    public function failed($e)
    {
        Log::info($e);
    }
}
