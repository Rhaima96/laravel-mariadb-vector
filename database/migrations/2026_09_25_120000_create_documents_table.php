<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->text('content');

            // 1536 dimensions: the size of OpenAI's text-embedding-3-small output.
            // MariaDB rejects any vector of a different length at insert time.
            $table->vector('embedding', 1536);

            $table->timestamps();

            // MariaDB: vector index ... M=6 DISTANCE=cosine.
            // Only one vector index per table, and the column must be NOT NULL.
            $table->vectorIndex('embedding');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
