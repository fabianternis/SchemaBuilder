<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schema_columns', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('table_id')->constrained('schema_tables')->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_nullable')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->string('default')->nullable();
            $table->boolean('is_unique')->default(false);
            $table->string('on_cascade')->nullable();
            $table->integer('length')->nullable();
            $table->boolean('auto_increment')->default(false);
            $table->foreignUlid('referenced_table_id')->nullable()->constrained('schema_tables')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schema_columns');
    }
};
