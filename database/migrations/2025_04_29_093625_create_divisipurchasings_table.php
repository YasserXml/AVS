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
        Schema::create('divisipurchasings', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('date_modified')->nullable();
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreignId('folder_id')->constrained('folders')->onDelete('cascade');
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisipurchasings');
    }
};
