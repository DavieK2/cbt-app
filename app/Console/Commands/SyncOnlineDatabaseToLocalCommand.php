<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Spatie\SimpleExcel\SimpleExcelReader;

class SyncOnlineDatabaseToLocalCommand extends Command
{
    protected $signature = 'app:sync';
    protected $description = 'Command description';

    public function handle()
    {
        try {

            set_time_limit(0);
        
            $request = Http::get('https://conscal.site/api/sync-to-local');
    
            $responses = $request->json();
    
            if( ! $request->ok() ){
    
                return ;
            }
    
           foreach( $responses as $table => $response ){
    
                $path = "sync_dl/$table";
    
                if( ! file_exists( public_path($path) ) ){
                            
                    mkdir( public_path($path), recursive: true );
                }
    
                $outputPath = public_path("$path/$table".'_'.now()->format('Y_m_d_H_i_s').".csv");
    
                foreach ($response as $data) {
                
                    $output = Process::timeout(1200)->run("curl -o $outputPath ".$data['sync_path'] )->errorOutput();
    
                    $this->info($output);
    
                    if( $output ){

                        Schema::disableForeignKeyConstraints();
    
                        SimpleExcelReader::create($outputPath)->getRows()->each(function($row) use($table){
    
                            $row = collect($row)->map(function($value) {
    
                                $value = @unserialize($value) ? unserialize($value) : $value;

                                if( is_array($value) ){
                                    $value = json_encode($value);
                                }

                                $value = $value == "" ? null : $value;
    
                                return $value;
    
                            })->toArray();
    
                            $row['is_synced'] = true;

                            if( ! DB::table($table)->where('uuid', $row['uuid'] )->first() ){

                                DB::table($table)->insertOrIgnore($row);  
                            }
                            
                        });
                        
                        $request = Http::post('https://conscal.site/api/sync-to-local-confirm', ['id' => $data['id'] ] );
                        
                        $this->info( json_encode( $request->json() ) );
                        
                        Schema::enableForeignKeyConstraints();
                    }
                }
    
            }
    
            $this->info('completed');

        } catch (\Throwable $th) {
            
            $this->info($th->getMessage());
        }
    
    }
}
