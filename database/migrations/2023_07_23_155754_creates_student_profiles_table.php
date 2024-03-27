<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('student_profiles', function(Blueprint $table){
            $table->ulid('uuid')->unique()->index();
            $table->string('first_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('student_code')->unique();
            $table->string('mat_no')->nullable();
            $table->string('reg_no')->nullable();
            $table->string('program_of_study')->nullable();
            $table->longText('profile_pic')->nullable();
            $table->foreignUlid('class_id')->nullable()->constrained(table: 'classes', column: 'uuid')->onDelete('SET NULL');
            $table->foreignUlid('academic_session_id')->nullable()->constrained(table: 'academic_sessions',column: 'uuid')->onDelete('SET NULL');
            $table->foreignUlid('faculty_id')->nullable()->constrained(table: 'faculties', column: 'uuid')->onDelete('SET NULL');
            $table->foreignUlid('department_id')->nullable()->constrained(table: 'departments', column: 'uuid')->onDelete('SET NULL');
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('student_profiles');
    }
};
