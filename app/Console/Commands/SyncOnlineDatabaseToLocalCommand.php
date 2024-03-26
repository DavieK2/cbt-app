<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Spatie\SimpleExcel\SimpleExcelReader;
use Intervention\Image\Facades\Image;

class SyncOnlineDatabaseToLocalCommand extends Command
{
    protected $signature = 'app:sync';
    protected $description = 'Command description';

    public function handle()
    {
        try {

            set_time_limit(0);
        
            $request = Http::get('https://exams.myunical.online/api/sync-to-local');
    
            $responses = $request->json();
    
            $this->info($request->json());
            
            if( ! $request->ok() ){
    
                $this->info($request->json());

                return ;
            }
    
           foreach( $responses as $table => $response ){
    
                $path = "sync_dl/$table";
    
                if( ! file_exists( public_path($path) ) ){
                            
                    mkdir( public_path($path), recursive: true );
                }
    
                $outputPath = public_path("$path/$table".'_'.now()->format('Y_m_d_H_i_s').".csv");
    
                foreach ($response as $data) {
                
                    $output = Process::run("curl -o $outputPath ".$data['sync_path'] )->errorOutput();
    
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
    
                            if( isset( $row['id'] ) ) unset( $row['id'] );

                            $row['is_synced'] = true;

                            if( $table === 'student_profiles' && ( substr( $row['profile_pic'], 0, 5) === 'data:' ) ){

                                $image = Image::make($row['profile_pic']);
                
                                $student_code = $row['student_code'];
                
                                $student_code = str_replace('/', '-', $student_code);
                
                                $pic_name = "profile_pics/".$student_code.".jpg";
                
                                $image->save( public_path("$pic_name") );
                
                                $row['profile_pic'] = $pic_name;
                            }

                            DB::table($table)->updateOrInsert([ 'uuid' => $row['uuid'] ], $row);  
                            
                            
                        });
                        
                        $request = Http::post('https://exams.myunical.online/api/sync-to-local-confirm', ['id' => $data['id'] ] );
                        
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
