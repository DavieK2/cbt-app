<?php

namespace App\Modules\SchoolManager\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class UploadCSVService {

    public function saveFileToLocalDiskAndReturnFirstRowWithPath($path, $file)
    {
        $extension =  $file->getClientOriginalExtension();

        $upload = Storage::disk('local')->putFileAs($path, $file, Str::random().".$extension");

        $rows = SimpleExcelReader::create(storage_path("app/".$upload))->take(1)->getRows()->toArray();
        
        return  ['key' => $upload, 'headings' => $rows[0] ];
    }
}