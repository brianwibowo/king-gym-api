<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we modify the enum column.
        // For SQLite (testing), enums are just check constraints or ignored, usually just varchar.

        // Use raw statement to ensure Enum values are updated
        DB::statement("ALTER TABLE members MODIFY COLUMN status ENUM('active', 'inactive', 'pending') DEFAULT 'inactive'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        // Warning: 'pending' values might cause issues if not handled.
        // We assume this won't be rolled back with pending data in production easily.
        DB::statement("ALTER TABLE members MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'inactive'");
    }
};
