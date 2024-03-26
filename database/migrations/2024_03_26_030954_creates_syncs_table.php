<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
    {
        Schema::create('syncs', function(Blueprint $table){
            $table->id();
            $table->string('table_synced');
            $table->string('sync_path');
            $table->boolean('has_synced')->default(false);
            $table->timestamp('last_synced_date')->nullable();
            $table->timestamps();
        });
    }

   
    public function down()
    {
        Schema::dropIfExists('syncs');
    }
};