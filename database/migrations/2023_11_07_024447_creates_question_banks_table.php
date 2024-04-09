<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
    {
        Schema::create('question_banks', function(Blueprint $table){
            $table->ulid('uuid')->unique()->index();
            $table->foreignUlid('user_id')->constrained( table: 'users', column: 'uuid' );
            $table->foreignUlid('assessment_id')->constrained( table: 'assessments', column: 'uuid' );
            $table->foreignUlid('subject_id')->nullable()->constrained( table: 'subjects', column: 'uuid' );
            $table->string('title')->nullable();
            $table->json('classes')->nullable();
            $table->json('section_ids')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('question_banks');
    }
};
