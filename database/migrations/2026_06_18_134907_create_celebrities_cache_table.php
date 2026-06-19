<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up(): void
    {


        Schema::create('celebrities_cache', function (Blueprint $table) {


            $table->id();


            $table->unsignedBigInteger('remote_id')
            ->nullable();


            $table->string('name');


            $table->date('birthday')
            ->nullable();


            $table->string('profession')
            ->nullable();


            $table->timestamps();


        });


    }




    public function down(): void
    {

        Schema::dropIfExists('celebrities_cache');

    }


};