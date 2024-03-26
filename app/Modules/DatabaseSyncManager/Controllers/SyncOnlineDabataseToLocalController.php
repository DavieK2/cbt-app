<?php

namespace App\Modules\DatabaseSyncManager\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DatabaseSyncManager\Jobs\SyncLocalDBToOnlineJob;
use App\Modules\DatabaseSyncManager\Jobs\SyncOnlineDBToLocalJob;
use App\Modules\DatabaseSyncManager\Models\DBSyncModel;
use App\Modules\DatabaseSyncManager\Requests\ConfirmDBSyncedToLocalRequest;
use App\Modules\DatabaseSyncManager\Tasks\SyncDatabaseTasks;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SyncOnlineDabataseToLocalController extends Controller
{
    public function __construct(protected SyncDatabaseTasks $tasks){}

    public function syncOnlineDatabaseToLocal()
    {
        $batch = Bus::batch([  new  SyncOnlineDBToLocalJob() ])->dispatch();

        return response()->json(['message' => 'Sync has started', 'id' => $batch->id ]);
    }


    public function sync()
    {        
        // $sync_data = $this->tasks->start(['filter' => ['personal_access_tokens', 'password_reset_tokens', 'migrations', 'failed_jobs', 'syncs', 'student_checkins', 'job_batches', 'jobs', 'assessment_results', 'assessment_sessions'] ])->sync();
        $sync_data = $this->tasks->start(['tables' => ['student_profiles', 'classes', 'academic_sessions'] ])->sync();

        $url = 'https://exams.myunical.online';

        $sync_data = $sync_data->map( fn($data) => $data->map( fn($value) => [ 'id' => $value['id'], 'sync_path' => $url.$value['sync_path']  ] ) );

        return response()->json($sync_data);

    }

    public function confirmSync(ConfirmDBSyncedToLocalRequest $request)
    {
        $data = $request->validated();

        DBSyncModel::find( $data['id'] )->update( ['has_synced' => true ]);

        return response()->json(['message' => 'Success']);
    }
}
