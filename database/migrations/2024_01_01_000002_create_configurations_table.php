<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['env','yaml','json','ini','xml','toml','properties'])->default('env');
            $table->enum('environment', ['production','staging','development','testing'])->default('development');
            $table->longText('content');
            $table->enum('status', ['draft','active','archived'])->default('draft');
            $table->string('version')->default('1.0.0');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('last_modified_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('config_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('version_number');
            $table->longText('content');
            $table->text('change_reason');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('config_versions');
        Schema::dropIfExists('configurations');
    }
};
