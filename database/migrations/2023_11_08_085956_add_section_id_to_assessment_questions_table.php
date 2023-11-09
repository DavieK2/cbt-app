<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->foreignId('section_id')->constrained();
        });
    }

  
    public function down()
    {
        //
    }
};
