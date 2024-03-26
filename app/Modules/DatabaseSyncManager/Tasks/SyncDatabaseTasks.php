<?php

namespace App\Modules\DatabaseSyncManager\Tasks;

use App\Contracts\BaseTasks;
use App\Modules\DatabaseSyncManager\Jobs\SaveLocalDBDataToOnlineJob;
use App\Modules\DatabaseSyncManager\Models\DBSyncModel;
use App\Services\CSVWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\SimpleExcel\SimpleExcelReader;
use Intervention\Image\Facades\Image;

class SyncDatabaseTasks extends BaseTasks{

    protected CSVWriter $writer;

    public function sync()
    {
        set_time_limit(0);

        $this->writer = new CSVWriter();

        if( isset($this->item['tables']) ){

            $tables = collect( $this->item['tables'] );

        }else{

            $unnecssary_tables = $this->item['filter'];

            $tables = collect(Schema::getAllTables())->filter(fn($table) => ! in_array($table->Tables_in_cbt, $unnecssary_tables))->map(fn($table) => $table->Tables_in_cbt);
        }

        $sync_paths = collect();

        // try {
           
            $tables->each(function($table) use($sync_paths){

                $table_sync_paths =  DBSyncModel::where('has_synced', false)->where('table_synced', $table)->get()->map(fn($path) => ['id' => $path->id, 'sync_path' => $path->sync_path ]);
    
                $unsynced_records = DB::table($table)->where('is_synced', false);
    
                if( $unsynced_records->count() > 0 ){
                    
                    DB::table($table)->where('is_synced', false)->cursor()->each(function($record) use($table){
                        
                        $records = (array) $record;
                        
                        $headers = array_keys($records);
                        
                        if($table === 'questions'){
                            $records['options'] = json_decode( $records['options'] );
                        }

                        if( $table === 'student_profiles' && ! ( substr( $records['profile_pic'], 0, 5) === 'data:' ) ){

                            $records['profile_pic'] = (string) Image::make( public_path($records['profile_pic']) )->encode('data-url');
                        }
                        
                        $records = collect($records)->map(fn($value) => is_array($value) ? serialize($value) : $value )->toArray();
                        
                        $this->writer->writeToCSV( $records, "/syncs/$table/", $headers );  
                    });
                    
                    $this->writer->close();

                    $unsynced_records->update(['is_synced' => true]);
                    
                    $question_sync = DBSyncModel::create(['table_synced' => $table, 'sync_path' => $this->writer->getFilePath(), 'last_synced_date' => now()->toDateTimeString() ]);
                    
                    $table_sync_paths->push(['sync_path' => $question_sync->sync_path, 'id' => $question_sync->id ]);
                    
                }  
                
                $sync_paths[$table] = $table_sync_paths;
                
            });
            

            // dd()
            return $sync_paths;


        // } catch (\Throwable $th) {


        //     $this->writer->close();
        // }
    }

    public function save($path, $table)
    {
        SimpleExcelReader::create($path)->getRows()->each(function($row) use($table){

            dispatch( new SaveLocalDBDataToOnlineJob($row, $table) );
            
        });

    }
    
}