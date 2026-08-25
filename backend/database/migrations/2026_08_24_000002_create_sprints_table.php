<?php

use App\Enums\SprintStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('goal')->nullable();
            $table->date('started_at');
            $table->date('ended_at');
            $table->enum('status', array_column(SprintStatus::cases(), 'value'))
                ->default(SprintStatus::Planning->value);
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sprints');
    }
};
