<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('event_bookings', 'billing_company_name')) {
                $table->string('billing_company_name')->nullable()->after('display_total_amount_minor');
            }
            if (! Schema::hasColumn('event_bookings', 'billing_address_line_1')) {
                $table->string('billing_address_line_1')->nullable()->after('billing_company_name');
            }
            if (! Schema::hasColumn('event_bookings', 'billing_address_line_2')) {
                $table->string('billing_address_line_2')->nullable()->after('billing_address_line_1');
            }
            if (! Schema::hasColumn('event_bookings', 'gstin')) {
                $table->string('gstin')->nullable()->after('billing_address_line_2');
            }
            if (! Schema::hasColumn('event_bookings', 'promo_code')) {
                $table->string('promo_code', 20)->nullable()->after('gstin');
            }
            if (! Schema::hasColumn('event_bookings', 'promo_discount_minor')) {
                $table->unsignedBigInteger('promo_discount_minor')->default(0)->after('promo_code');
            }
            if (! Schema::hasColumn('event_bookings', 'gateway_charge_minor')) {
                $table->unsignedBigInteger('gateway_charge_minor')->default(0)->after('promo_discount_minor');
            }
            if (! Schema::hasColumn('event_bookings', 'subtotal_display_minor')) {
                $table->unsignedBigInteger('subtotal_display_minor')->default(0)->after('gateway_charge_minor');
            }
            if (! Schema::hasColumn('event_bookings', 'grand_total_display_minor')) {
                $table->unsignedBigInteger('grand_total_display_minor')->default(0)->after('subtotal_display_minor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_bookings', function (Blueprint $table) {
            $dropCols = [
                'billing_company_name',
                'billing_address_line_1',
                'billing_address_line_2',
                'gstin',
                'promo_code',
                'promo_discount_minor',
                'gateway_charge_minor',
                'subtotal_display_minor',
                'grand_total_display_minor',
            ];
            foreach ($dropCols as $col) {
                if (Schema::hasColumn('event_bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

