<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_profiles', function(Blueprint $table){
            $table->longText('profile_pic')->change()->nullable();
        });
    }

   
    public function down()
    {
        //
    }
};
