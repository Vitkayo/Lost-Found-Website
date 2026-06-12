<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('student_id', 60)->nullable()->after('phone');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('verification_question')->nullable()->after('description');
            $table->string('verification_answer_hash')->nullable()->after('verification_question');
            $table->text('hidden_details')->nullable()->after('verification_answer_hash');
        });

        Schema::table('item_claims', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('item_id')->constrained()->nullOnDelete();
            $table->text('verification_answer')->nullable()->after('message');
            $table->timestamp('reviewed_at')->nullable()->after('verification_answer');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('item_claims', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['verification_answer', 'reviewed_at']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['verification_question', 'verification_answer_hash', 'hidden_details']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'student_id']);
        });
    }
};
