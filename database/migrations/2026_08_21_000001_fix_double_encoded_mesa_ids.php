<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rows written before the double-encoding fix store mesa_ids as a JSON
     * string ('"[1]"') instead of a JSON array ([1]), which broke the
     * per-table availability checks. Unwrap those values in place.
     */
    public function up(): void
    {
        DB::statement(
            "UPDATE reservas SET mesa_ids = JSON_UNQUOTE(mesa_ids) WHERE JSON_TYPE(mesa_ids) = 'STRING'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "UPDATE reservas SET mesa_ids = JSON_QUOTE(mesa_ids) WHERE JSON_TYPE(mesa_ids) = 'ARRAY'"
        );
    }
};
