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
        Schema::create('relationships', function (Blueprint $table) {
            $table->comment('Parent-child relationships for family tree');
            $table->id();
            $table->foreignId('parent_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('child_id')->constrained('people')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['parent_id', 'child_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationships');
    }
};
