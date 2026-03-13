<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // nom de la station
            $table->string('location'); // localisation (ville ou adresse)

            $table->string('connector_type'); // type de connecteur (Type1, Type2, CCS...)
            $table->integer('power_kw'); // puissance en kW

            $table->boolean('available')->default(true); // disponibilité de la borne

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
