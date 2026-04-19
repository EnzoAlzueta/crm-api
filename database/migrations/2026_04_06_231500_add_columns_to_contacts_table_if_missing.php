<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fixes databases where `contacts` was created before the full schema
     * was defined (e.g. only id + timestamps).
     */
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'client_id')) {
                $table->foreignId('client_id')->after('id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('contacts', 'name')) {
                $table->string('name')->after('client_id');
            }
            if (! Schema::hasColumn('contacts', 'email')) {
                $table->string('email')->nullable()->after('name');
            }
            if (! Schema::hasColumn('contacts', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('contacts', 'position')) {
                $table->string('position')->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            $columns = ['position', 'phone', 'email', 'name', 'client_id'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('contacts', $column)) {
                    if ($column === 'client_id') {
                        $table->dropForeign(['client_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
