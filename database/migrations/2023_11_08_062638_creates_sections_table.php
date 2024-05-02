<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('sections', function(Blueprint $table){
            $table->ulid('uuid')->unique()->index();
            $table->foreignUlid('question_bank_id')->nullable()->constrained( table: 'question_banks', column: 'uuid' );
            $table->string('section_code')->unique();
            $table->string('title');
            $table->longText('description');
            $table->string('question_type');
            $table->integer('total_questions')->nullable();
            $table->double('section_score')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    
    public function down()
    {
        //
    }
};
