<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo')->index();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->index();
            $table->date('due_date')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('tasks'); }
};
