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
        Schema::create('penanganans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_penanganan');
            $table->text('keluhan');
            $table->text('riwayat_penyakit')->nullable();
            $table->text('diagnosis_manual');
            $table->enum('telinga_terkena', ['kiri', 'kanan', 'keduanya']);
            $table->text('tindakan');
            $table->enum('status', ['pending', 'selesai', 'dibatalkan'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penanganans');
    }
};
