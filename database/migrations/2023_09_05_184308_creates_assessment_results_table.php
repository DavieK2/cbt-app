<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assessment_results', function(Blueprint $table){
            $table->ulid('uuid')->unique()->index();
            $table->foreignUlid('student_profile_id')->constrained( table: 'student_profiles', column: 'uuid' );
            $table->foreignUlid('assessment_id')->constrained( table: 'assessments', column: 'uuid' );
            $table->foreignUlid('subject_id')->nullable()->constrained( table: 'subjects', column: 'uuid' );
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('time_remaining')->nullable();
            $table->boolean('has_started')->default(false);     
            $table->boolean('has_submitted')->default(false);     
            $table->integer('tries')->default(5);     
            $table->json('section_scores')->nullable();
            $table->double('total_score')->nullable();
            $table->string('grade')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
        });
    }
    
   
    public function down()
    {
        Schema::dropIfExists('assessment_results');
    }
};
