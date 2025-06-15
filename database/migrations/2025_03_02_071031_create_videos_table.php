<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Relasi ke user
            $table->string('raw_video_path'); // Path ke video mentah
            $table->string('processed_video_path'); // Path ke video dengan bounding box
            $table->enum('status', ['assigned', 'unassigned'])->default('unassigned'); // Status video
            $table->string('hasil_diagnosis')->default('normal');
            $table->longText('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
