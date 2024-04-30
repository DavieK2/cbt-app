<?php

namespace App\Modules\SchoolManager\Tasks;

use App\Contracts\BaseTasks;
use App\Modules\SchoolManager\Models\AcademicSessionModel;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\DepartmentModel;
use App\Modules\SchoolManager\Models\FacultyModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Models\SubjectModel;
use App\Modules\SchoolManager\Services\UploadCSVService;
use App\Services\CSVWriter;
use EllGreen\LaravelLoadFile\Laravel\Facades\LoadFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;
use ZipArchive;
use Intervention\Image\Facades\Image;

class CreateStudentTasks extends BaseTasks{

    protected ?CSVWriter $errorFileWriter = NULL;
    protected ?CSVWriter $studentFileWriter = NULL;
    protected ?CSVWriter $studentSubjectsFileWriter = NULL;

    protected array $insert_columns = [];

    protected string $error_path = 'imports/students/failures/';

    protected string $student_file_path = 'imports/students/profile/';

    protected string $student_subjects_file_path = 'imports/students/subjects/';

    protected $validated_rows = 0;

    protected $mappings = [];

    protected $errors = [];

    public function createStudent()
    {
        if( $this->item['studentCode'] ){

            $student_code = $this->item['studentCode'];

        }else{
            
            $count = StudentProfileModel::count() + 1;
            $student_code = 'JEPH-'.str_pad(strval($count), 4, '0', STR_PAD_LEFT).'-'.Str::random(4);
        }
      

        $student = StudentProfileModel::create([
            'uuid'          =>  Str::ulid(),
            'first_name'     =>  $this->item['firstName'],
            'surname'       =>  $this->item['surname'],
            'class_id'      =>  $this->item['classId'],
            'profile_pic'    =>  $this->item['profilePic'],
            'student_code'  =>  strtoupper($student_code)
        ]);

        return new static( [ ...$this->item, 'student' => $student ]);
    }

    public function uploadCSV()
    {
        $upload = ( new UploadCSVService() )->saveFileToLocalDiskAndReturnFirstRowWithPath( 'imports/students', $this->item['file'] );

        $key = Str::random(6);

        Cache::put( $key, $upload['key'], now()->addMinutes(60) );

        return new static( ['key' => $key, 'headings' => $upload['headings'] ]);

    }

    public function importStudentData()
    {
        set_time_limit(0);
        

        // try {
           

        // } catch (\Throwable $th) {
            
        //     $this->closeFileStream();

        //     Log::error( $th );

        //     throw $th;
        // }

        $mappings = json_decode($this->item['mappings'], true);
        $importFileKey = Cache::get($this->item['importKey']);

        // dd(  SimpleExcelReader::create( storage_path("app/$importFileKey") )->trimHeaderRow()->getRows()->count() );
        
        SimpleExcelReader::create( storage_path("app/$importFileKey") )->trimHeaderRow()->getRows()->each(function($row) use($mappings) {

            $data = collect($mappings)->flatMap(function($map, $index) use($row){

                return [ $index => $row[$map] ];

            })->toArray();

            $this->mappings = $mappings;


            // $has_errors = $this->validateRow($data);

            // if( $has_errors ){

            //     dd('error');
            //     // $this->writeErrorsToCSVFile($row);

            //     return ;
            // }
            
            $class_level = match(true){
                ($data['level'] == 100) => '100 LEVEL',
                ($data['level'] == 200) => '200 LEVEL',
                ($data['level'] == 300) => '300 LEVEL',
                ($data['level'] == 400) => '400 LEVEL',
                ($data['level'] == 500) => '500 LEVEL',
                ($data['level'] == 600) => '600 LEVEL', 
                ($data['level'] == '200 (Direct Entry)') => '200 (DIRECT ENTRY)', 
                default => $data['level']
            };

            if( isset($data['passport'] ) ){

                $data['passport'] = "profile_pics/{$data['passport']}";
            }

            if( isset( $data['passport'] ) && ( substr( $data['passport'], 0, 5) === 'data:' ) ){

                $image = Image::make($data['passport']);

                $image->resize(800, null, fn ($constraint) => $constraint->aspectRatio() );

                $student_code = $data['studentCode'];

                $student_code = str_replace('/', '-', $student_code);

                $pic_name = "profile_pics/".$student_code.".jpg";

                $image->save( public_path("$pic_name") );

                $validated_data['passport'] = $pic_name;
               
            }

            $validated_data['class_id'] = ( ( ClassModel::firstWhere('class_name', $class_level) ?? ClassModel::firstWhere('uuid', $class_level) )?->uuid ) ?? "";
            $validated_data['academic_session_id'] = ( ( AcademicSessionModel::firstWhere('session', $data['session'] ?? null ) ?? AcademicSessionModel::firstWhere('uuid', $data['session'] ?? null ) )?->uuid ) ?? "";
            $validated_data['department_id'] =  ( ( DepartmentModel::firstWhere('department_name', $data['department'] ?? null ) ?? DepartmentModel::firstWhere('uuid', $data['department'] ?? null ) )?->uuid ) ?? "";
            $validated_data['faculty_id'] = ( ( FacultyModel::firstWhere('faculty_name', $data['faculty'] ?? null ) ?? FacultyModel::firstWhere('uuid', $data['faculty'] ?? null ) )?->uuid ) ?? "";
            
            $validated_data['uuid'] = $data['studentId'] ?? Str::ulid();
            $validated_data['first_name'] = $data['firstName'] ?? "";
            $validated_data['surname'] = $data['lastName'] ?? "";
            $validated_data['email'] = $data['email'] ?? "";
            $validated_data['phone_no'] = $data['phoneNo'] ?? "";
            $validated_data['student_code'] = $data['studentCode'];
            $validated_data['reg_no'] = $data['studentCode'] ?? "";
            $validated_data['program_type'] = $data['programType'] ?? "";
            $validated_data['profile_pic'] = $data['passport'] ?? "";
            $validated_data['program_of_study'] = $data['programOfStudy'] ?? "";
            $validated_data['is_synced'] = 0;
            $validated_data['mat_no'] = "";
            $validated_data['created_at'] = now()->toDateTimeString();
            $validated_data['updated_at'] = now()->toDateTimeString();

            $this->writeStudentDataToCSV($validated_data);

            if( isset($data['courses'] ) ){

                $courses = explode(', ', $data['courses']);

                $subjects = SubjectModel::whereIn('subject_code', $courses)->select('uuid')->get()->pluck('uuid')->toArray();

                foreach( $subjects as $subject ){

                    if( DB::table('student_subjects')->where('subject_id', $subject)->where('student_profile_id', $validated_data['uuid'])->first() ) continue;

                    $student_course = ['uuid' => Str::ulid(), 'subject_id' => $subject, 'student_profile_id' => $validated_data['uuid'], 'is_synced' => 0 ];

                    $this->writeStudentSubjectDataToCSV($student_course);
                }
                
            }
        });

        $this->saveToDatabase();

        $this->closeFileStream();

        if( isset( $this->item['file'] ) ){

            $filePath = Storage::disk('local')->putFile( 'imports/zip_files/students', $this->item['file'] );

            $filePath = storage_path("app/$filePath");
            
            $zip = new ZipArchive();

            $res = $zip->open( $filePath );

            if( $res === TRUE ){
                
                for($i = 0; $i < $zip->numFiles; $i++) {
                     
                    $entry = $zip->getNameIndex($i);
                                    
                    if ( substr( $entry, -1 ) == '/' ) continue;  
                    
                    $dest = public_path('profile_pics');
                    $dest_basename = pathinfo($entry, PATHINFO_BASENAME);
                    $ext = pathinfo($entry, PATHINFO_EXTENSION);

                    if ( ! preg_match('/(?:jpg|png|jpeg)/i', $ext) ) continue;

                    copy("zip://{$filePath}#{$entry}", "$dest/{$dest_basename}");
                }
                
                $zip->close();
            }
            else{ }

            if( Storage::disk('local')->exists($filePath) ) Storage::disk('local')->delete($filePath);
           
        }
       
        return new static( [ ...$this->item ]);
    }   


    protected function validateRow($data)
    {

        if( isset( $data['firstName'] ) && is_null( $data['firstName'] ) ){

            $this->errors['firstName'] = "{$this->mappings['firstName']} is empty";
        }

        if( ! isset( $data['firstName'] ) ){

            $this->errors['firstName'] = "{$this->mappings['firstName']} is missing";
        }

        if( isset( $data['studentCode'] ) && is_null( $data['studentCode'] ) ){

            $this->errors['studentCode'] = "{$this->mappings['studentCode']} is empty";
        }

        if( ! isset( $data['studentCode'] ) ){

            $this->errors['studentCode'] = "Please add a column for student code or reg no";
        }

        if( isset( $data['studentCode'] ) && StudentProfileModel::firstWhere('student_code', $data['studentCode']) ){

            $this->errors['studentCode'] = "{$this->mappings['studentCode']} has already been registered";
        }

        if( isset( $data['level'] ) && is_null( $data['level'] ) ){

            $this->errors['level'] = "{$this->mappings['level']} is empty";
        }

        if( ! isset( $data['level'] ) ){

            $this->errors['level'] = "The add a column for student level";
        }

        return count( $this->errors ) > 0 ? true : false;
    }

    protected function writeErrorsToCSVFile($row)
    {

        $this->errorFileWriter = $this->errorFileWriter ?? new CSVWriter();

        $header_row = collect( array_keys($row) )->flatMap( fn($data) => [$this->mappings[$data] ?? "Found Errors"] )->toArray();
        
        $this->errorFileWriter->writeToCSV($row, 'imports/students/errors/', $header_row);

    }

    protected function writeStudentDataToCSV(array $data) : void
    {
        $this->studentFileWriter = $this->studentFileWriter ?? new CSVWriter();

        $this->studentFileWriter->writeToCSV($data, $this->student_file_path);

    }

    protected function writeStudentSubjectDataToCSV(array $data) : void
    {
        $this->studentSubjectsFileWriter = $this->studentSubjectsFileWriter ?? new CSVWriter();

        $this->studentSubjectsFileWriter->writeToCSV($data, $this->student_subjects_file_path);

    }

    protected function saveToDatabase() : void
    {
        if( $this->studentFileWriter ) $this->loadFileToDatabase($this->studentFileWriter, 'student_profiles');
        
        if( $this->studentSubjectsFileWriter ) $this->loadFileToDatabase($this->studentSubjectsFileWriter, 'student_subjects');
    }

    protected function loadFileToDatabase(CSVWriter $writer, string $table)
    {
        $columns = $writer->getHeaders();

        $file_path = $writer->getFilePath();

        $insert_columns = collect($columns)->flatMap(fn($column) => [ DB::raw("@{$column}") ])->toArray();

        $set_columns = collect($columns)->flatMap(fn($column) => [ $column => DB::raw('NULLIF(@'.$column.',"")') ] )->toArray();

        LoadFile::file(public_path($file_path), $local = true)
                        ->into($table)
                        ->columns($insert_columns)
                        ->set($set_columns)
                        ->fieldsTerminatedBy(',')
                        ->fieldsEscapedBy('\\\\')
                        ->fieldsEnclosedBy('"')
                        ->ignore()
                        ->load();
    }

    protected function closeFileStream() : void
    {
        if( $this->errorFileWriter ) $this->errorFileWriter->close();
        if( $this->studentFileWriter ) $this->studentFileWriter->close();
        if( $this->studentSubjectsFileWriter ) $this->studentSubjectsFileWriter->close();
    }

}