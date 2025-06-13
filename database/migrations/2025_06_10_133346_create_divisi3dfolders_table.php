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
        Schema::create('divisi3dfolders', function (Blueprint $table) {
            $table->id();

            //Morph
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();

            //Folder
            $table->string('name')->index();
            $table->string('collection')->nullable()->index();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            //Options
            $table->boolean('is_protected')->default(false)->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_hidden')->default(false)->nullable();
            $table->boolean('is_favorite')->default(false)->nullable();
            $table->foreignId('user_id')->constrained('users')->nullable();
            $table->boolean('is_public')->default(false)->nullable();
            $table->boolean('has_user_access')->default(false)->nullable();
            $table->string('user_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('3dfolders');
    }
};
