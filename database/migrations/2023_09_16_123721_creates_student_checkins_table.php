<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_checkins', function(Blueprint $table){
            $table->ulid('uuid')->unique()->index();
            $table->foreignUlid('assessment_id')->constrained( table: 'assessments', column: 'uuid' );
            $table->foreignUlid('student_profile_id')->constrained( table: 'student_profiles', column: 'uuid' );
            $table->timestamp('checked_in_at');
            $table->string('checked_in_by')->nullable();
            $table->timestamp('checked_in_expires_at');
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_checkins');
    }
};
