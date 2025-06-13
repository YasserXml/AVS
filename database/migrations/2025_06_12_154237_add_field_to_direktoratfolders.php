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
        Schema::table('direktoratfolders', function (Blueprint $table) {
            $table->string('collection')->nullable()->change();
            
            // Tambahkan index untuk performa
           
            $table->index(['model_type']);
            $table->index(['parent_id', 'model_type']);
            
            // Set default values
            $table->string('icon')->nullable()->default('heroicon-o-folder')->change();
            $table->string('color')->nullable()->default('#ffab09')->change();
            $table->boolean('is_protected')->nullable()->default(false)->change();
            $table->boolean('is_hidden')->nullable()->default(false)->change();
            $table->boolean('is_public')->nullable()->default(false)->change();
            $table->boolean('has_user_access')->nullable()->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direktoratfolders', function (Blueprint $table) {
            //
        });
    }
};
