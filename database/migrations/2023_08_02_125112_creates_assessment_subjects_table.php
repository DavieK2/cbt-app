<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assessment_subjects', function(Blueprint $table){
            $table->ulid('uuid')->unique();
            $table->string('assessment_id');
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('class_id')->constrained();
            $table->integer('assessment_duration');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('is_published')->default(false);
            $table->boolean('has_generated_results')->default(false);
            $table->boolean('is_synced')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessment_subjects');
    }
};
