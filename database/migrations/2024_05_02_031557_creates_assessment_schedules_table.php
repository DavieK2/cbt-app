<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('assessment_schedules', function(Blueprint $table){
            $table->ulid('uuid')->unique()->index();
            $table->foreignUlid('assessment_id')->constrained( table: 'assessments', column: 'uuid' );
            $table->foreignUlid('faculty_id')->nullable()->constrained( table: 'faculties', column: 'uuid' );
            $table->foreignUlid('department')->nullable()->constrained( table: 'departments', column: 'uuid' );
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });
    }

};
