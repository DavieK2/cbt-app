<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('section_questions', function (Blueprint $table) {
            $table->foreignId('section_id')->constrained();
            $table->foreignId('question_id')->constrained();
        });
    }

    public function down()
    {
        //
    }
};
