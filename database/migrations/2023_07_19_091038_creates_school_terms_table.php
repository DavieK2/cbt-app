<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::create('school_terms', function(Blueprint $table){
            $table->id();
            $table->ulid('uuid')->unique();
            $table->string('term');
            $table->boolean('is_synced')->default(false);
            $table->timestamps();
       });
    }

    public function down()
    {
        Schema::dropIfExists('school_terms');
    }
};
