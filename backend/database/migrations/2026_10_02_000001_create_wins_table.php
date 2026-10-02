<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wins', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->index()->constrained()->restrictOnDelete();
            $table->date('win_date');
            $table->timestamps();

            $table->index(['win_date', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wins');
    }
};
