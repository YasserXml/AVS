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
        Schema::create('softwarefolders', function (Blueprint $table) {
             $table->id();

            //Morph
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
 
            //Folder
            $table->string('name')->index();
            $table->string('slug')->nullable();
            $table->index('slug');
            $table->string('collection')->nullable()->index();
            $table->string('description')->nullable();
            $table->string('icon')->nullable()->default('heroicon-o-folder');
            $table->string('color')->nullable()->default('#ffab09');

            //Options
            $table->boolean('is_protected')->default(false)->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_hidden')->default(false)->nullable();
            $table->boolean('is_favorite')->default(false)->nullable();
            $table->foreignId('user_id')->constrained('users')->nullable();
            $table->boolean('is_public')->default(false)->nullable();
            $table->boolean('has_user_access')->default(false)->nullable();
            $table->string('user_type')->nullable();
            $table->index(['parent_id']);
            $table->foreignId('parent_id')->nullable()->constrained('softwarefolders')->onDelete('cascade');
            $table->index(['model_type', 'model_id']);
            $table->timestamps();
            $table->softDeletes();  
            $table->foreignId('kategori_id')->nullable()->constrained('kategorisoftware')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('softwarefolders');
    }
};
