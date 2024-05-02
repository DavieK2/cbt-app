<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('formatter', function(Blueprint $table){
            $table->string('type')->nullable();
            $table->string('format')->nullable();
            $table->json('value')->nullable();
        });
    }

    
};
