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
       Schema::create('charging_sessions', function (Blueprint $table) {
    $table->id();

    $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
    
    $table->dateTime('actual_start_time'); 
    $table->dateTime('actual_end_time')->nullable(); 
    $table->float('energy_delivered')->default(0);
    
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charging_sessions');
    }
};
