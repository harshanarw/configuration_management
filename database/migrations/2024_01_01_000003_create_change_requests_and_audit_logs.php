<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')->constrained()->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_reason');
            $table->text('review_notes')->nullable();
            $table->text('approval_notes')->nullable();
            $table->enum('status', ['pending','reviewing','approved','rejected','deployed'])->default('pending');
            $table->boolean('reviewer_approved')->default(false);
            $table->boolean('reviewer_rejected')->default(false);
            $table->boolean('approver_approved')->default(false);
            $table->boolean('approver_rejected')->default(false);
            $table->boolean('mfa_verified')->default(false);
            $table->timestamp('deployed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 50)->default('general');
            $table->string('action');
            $table->string('resource')->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->enum('severity', ['low','medium','high','critical'])->default('low');
            $table->string('log_hash', 64)->nullable();  // SHA-256 hash chain
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type','created_at']);
            $table->index(['user_id','created_at']);
            $table->index('severity');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('change_requests');
    }
};
