<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_data', function (Blueprint $table) {
            $table->string('treasurer_name')->nullable()->after('headmaster_name');
            $table->string('treasurer_signature_path')->nullable()->after('treasurer_name');
        });
    }

    public function down(): void
    {
        Schema::table('school_data', function (Blueprint $table) {
            $table->dropColumn(['treasurer_name', 'treasurer_signature_path']);
        });
    }
};
