<?php

namespace App\Modules\SchoolManager\Tasks;

use App\Contracts\BaseTasks;
use App\Modules\SchoolManager\Models\AcademicSessionModel;
use App\Modules\SchoolManager\Models\ClassModel;
use App\Modules\SchoolManager\Models\StudentProfileModel;
use App\Modules\SchoolManager\Services\UploadCSVService;
use App\Services\CSVWriter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;
use ZipArchive;
use Intervention\Image\Facades\Image;

class CreateStudentTasks extends BaseTasks{

    protected ?CSVWriter $errorFileWriter = NULL;

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
        $mappings = json_decode($this->item['mappings'], true);
        $importFileKey = Cache::get($this->item['importKey']);

        $errors = [];
        
        SimpleExcelReader::create( storage_path("app/$importFileKey") )->trimHeaderRow()->getRows()->each(function($row) use($mappings) {

            $data = collect($mappings)->flatMap(function($map, $index) use($row){

                return [ $index => $row[$map] ];

            })->toArray();


            $validator = Validator::make($data, [
                'firstName'          =>     'required',
                'lastName'          =>     'nullable',
                'email'             =>     'nullable',
                'phoneNo'           =>     'nullable',
                'studentCode'       =>     'required',
                'programOfStudy'    =>     'nullable',
                'level'             =>     'required',
                'session'           =>     'nullable',
                'passport'          =>     'nullable',
                'studentId'         =>     'nullable',
            ]);


            if( $validator->fails() ){

                $this->errorFileWriter = $this->errorFileWriter ?? new CSVWriter();

                $this->errorFileWriter->writeToCSV([ implode(', ', $validator->errors()->all() ) ], 'imports/question/errors/', [ 'errors' ] );

                return ;
            }

            $data =  $validator->validated();
            
            $class_level = match(true){
                ($data['level'] == 1) => '100 LEVEL',
                ($data['level'] == 2) => '200 LEVEL',
                ($data['level'] == 3) => '300 LEVEL',
                default => $data['level']
            };

            $level = ClassModel::firstWhere('class_name', $class_level) ?? ClassModel::firstWhere('uuid', $class_level);

            $session = AcademicSessionModel::firstWhere('session', $data['session']) ?? AcademicSessionModel::firstWhere('uuid', $data['session']);


            if( isset( $data['passport'] ) && ( substr( $data['passport'], 0, 5) === 'data:' ) ){

                $image = Image::make($data['passport']);

                $image->resize(800, null, fn ($constraint) => $constraint->aspectRatio() );

                $student_code = $data['studentCode'];

                $student_code = str_replace('/', '-', $student_code);

                $pic_name = "profile_pics/".$student_code.".jpg";

                $image->save("$pic_name");

                $data['passport'] = $pic_name;
            }

            else{

                $data['passport'] = "profile_pics/{$data['passport']}";
            }

            StudentProfileModel::updateOrCreate(['academic_session_id' => $session?->uuid, 'class_id' => $level?->uuid, 'student_code' => $data['studentCode'] ], [
                'uuid'                  =>     $data['studentId'] ?? Str::ulid(),
                'first_name'             =>     $data['firstName'],
                'surname'               =>     $data['lastName'] ?? NULL,
                'email'                 =>     $data['email'] ?? NULL,
                'phone_no'              =>     $data['phoneNo'] ?? NULL,
                'student_code'          =>     $data['studentCode'],
                'reg_no'                =>     $data['studentCode'],
                'class_id'              =>     $level?->uuid,
                'academic_session_id'   =>     $session?->uuid,
                'program_of_study'      =>     $data['programOfStudy'] ?? NULL,
                'profile_pic'            =>     $data['passport'] ?? NULL
            ]);


        });

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
}