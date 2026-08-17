<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('member_type_id')->nullable()->after('address')->constrained('member_types')->nullOnDelete();
            $table->decimal('total_spend', 15, 2)->default(0)->after('loyalty_points');
        });

        // Map existing member_type string to member_type_id
        $types = DB::table('member_types')->get();
        foreach ($types as $type) {
            DB::table('customers')
                ->where('member_type', strtolower($type->name))
                ->update(['member_type_id' => $type->id]);
        }

        // Assign default (Regular) to those without match
        $regular = DB::table('member_types')->where('name', 'Regular')->first();
        if ($regular) {
            DB::table('customers')
                ->whereNull('member_type_id')
                ->update(['member_type_id' => $regular->id]);
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('member_type');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('member_type', ['regular', 'silver', 'gold', 'platinum'])->default('regular')->after('address');
        });

        $types = DB::table('member_types')->get();
        foreach ($types as $type) {
            DB::table('customers')
                ->where('member_type_id', $type->id)
                ->update(['member_type' => strtolower($type->name)]);
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['member_type_id']);
            $table->dropColumn(['member_type_id', 'total_spend']);
        });
    }
};
