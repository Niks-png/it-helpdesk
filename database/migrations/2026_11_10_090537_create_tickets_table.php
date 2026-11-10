<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable()->unique(); // IT-2026-0001, set right after insert
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // employee who reported it
            $table->string('category');
            $table->string('title');
            $table->text('description');
            $table->string('screenshot')->nullable(); // path on the private "local" disk
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
