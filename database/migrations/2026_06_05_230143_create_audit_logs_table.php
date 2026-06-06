<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("audit_logs", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->nullable()
                ->constrained("users")
                ->onDelete("set null");
            $table->string("action"); // create, update, delete, verify
            $table->string("model"); // Transaksi, Obat, BatchStok
            $table->unsignedBigInteger("model_id")->nullable();
            $table->json("changes")->nullable(); // before/after data
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("audit_logs");
    }
};
