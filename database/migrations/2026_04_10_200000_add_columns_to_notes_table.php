<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notes')) {
            return;
        }

        Schema::table('notes', function (Blueprint $table) {
            if (! Schema::hasColumn('notes', 'client_id')) {
                $table->foreignId('client_id')->after('id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('notes', 'contact_id')) {
                $table->foreignId('contact_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('notes', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('contact_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('notes', 'body')) {
                $table->text('body')->after('user_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notes')) {
            return;
        }

        Schema::table('notes', function (Blueprint $table) {
            foreach (['body', 'user_id', 'contact_id', 'client_id'] as $column) {
                if (Schema::hasColumn('notes', $column)) {
                    if (in_array($column, ['client_id', 'contact_id', 'user_id'], true)) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
