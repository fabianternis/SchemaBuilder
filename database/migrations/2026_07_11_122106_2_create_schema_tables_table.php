<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schema_tables', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('database_id')->constrained('schema_databases')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schema_tables');
    }
};
