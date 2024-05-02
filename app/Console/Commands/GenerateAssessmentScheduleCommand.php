<?php

namespace App\Console\Commands;

use App\Modules\CBT\Models\AssessmentModel;
use App\Modules\SchoolManager\Models\DepartmentModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Str;

class GenerateAssessmentScheduleCommand extends Command
{
    protected $signature = 'generate:schedule';
    protected $description = 'Command description';

    public function handle()
    {
        SimpleExcelReader::create( base_path('GSS_Schedule.xlsx') )->trimHeaderRow()->getRows()->each( function( $row ){

            $department = DepartmentModel::where('department_name', 'like', "%{$row['Department']}%")->first()->uuid;
    
            DB::table('assessment_schedules')->insert([
                'uuid' => Str::ulid(),
                'assessment_id' => AssessmentModel::latest()->first()->uuid,
                'department' => $department,
                'start_time' => $row['Start'],
                'end_time' => $row['End'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });
      
    }
}
