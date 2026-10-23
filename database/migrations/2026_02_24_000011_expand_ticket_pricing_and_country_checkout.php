<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpandTicketPricingAndCountryCheckout extends Migration
{
    public function up()
    {
        if (Schema::hasTable('event_ticket_plans')) {
            Schema::table('event_ticket_plans', function (Blueprint $table) {
                if (! Schema::hasColumn('event_ticket_plans', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('event_id');
                }
                if (! Schema::hasColumn('event_ticket_plans', 'mrp_inr_paise')) {
                    $table->unsignedBigInteger('mrp_inr_paise')->default(0)->after('price_paise');
                }
                if (! Schema::hasColumn('event_ticket_plans', 'sale_inr_paise')) {
                    $table->unsignedBigInteger('sale_inr_paise')->default(0)->after('mrp_inr_paise');
                }
                if (! Schema::hasColumn('event_ticket_plans', 'mrp_usd_cents')) {
                    $table->unsignedBigInteger('mrp_usd_cents')->default(0)->after('sale_inr_paise');
                }
                if (! Schema::hasColumn('event_ticket_plans', 'sale_usd_cents')) {
                    $table->unsignedBigInteger('sale_usd_cents')->default(0)->after('mrp_usd_cents');
                }
                if (! Schema::hasColumn('event_ticket_plans', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_active');
                }
            });

            try {
                DB::statement('ALTER TABLE `event_ticket_plans` DROP INDEX `event_ticket_plans_event_id_plan_index_unique`');
            } catch (\Throwable $e) {
                // Index may already be dropped in some environments.
            }

            $fxRaw = DB::table('settings')->where('key', 'ticket_fx_usd_to_inr')->value('value');
            $fx = is_numeric($fxRaw) ? (float) $fxRaw : 83.0;
            if ($fx <= 0) {
                $fx = 83.0;
            }

            DB::table('event_ticket_plans')
                ->orderBy('id')
                ->chunkById(200, function ($plans) use ($fx) {
                    foreach ($plans as $plan) {
                        $inrPaise = (int) ($plan->price_paise ?? 0);
                        $inr = $inrPaise / 100;
                        $usdCents = (int) round(($inr / $fx) * 100);
                        if ($usdCents < 0) {
                            $usdCents = 0;
                        }

                        DB::table('event_ticket_plans')
                            ->where('id', $plan->id)
                            ->update([
                                'sort_order' => (int) ($plan->plan_index ?? 0),
                                'mrp_inr_paise' => $inrPaise,
                                'sale_inr_paise' => $inrPaise,
                                'mrp_usd_cents' => $usdCents,
                                'sale_usd_cents' => $usdCents,
                            ]);
                    }
                });
        }

        if (Schema::hasTable('event_bookings')) {
            Schema::table('event_bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('event_bookings', 'billing_country_code')) {
                    $table->string('billing_country_code', 2)->nullable()->after('currency');
                }
                if (! Schema::hasColumn('event_bookings', 'display_currency')) {
                    $table->string('display_currency', 3)->default('INR')->after('billing_country_code');
                }
                if (! Schema::hasColumn('event_bookings', 'display_unit_amount_minor')) {
                    $table->unsignedBigInteger('display_unit_amount_minor')->nullable()->after('display_currency');
                }
                if (! Schema::hasColumn('event_bookings', 'display_total_amount_minor')) {
                    $table->unsignedBigInteger('display_total_amount_minor')->nullable()->after('display_unit_amount_minor');
                }
                if (! Schema::hasColumn('event_bookings', 'fx_rate_usd_to_inr')) {
                    $table->decimal('fx_rate_usd_to_inr', 12, 6)->nullable()->after('display_total_amount_minor');
                }
            });

            DB::table('event_bookings')
                ->whereNull('display_unit_amount_minor')
                ->update([
                    'display_currency' => 'INR',
                    'display_unit_amount_minor' => DB::raw('unit_price_paise'),
                    'display_total_amount_minor' => DB::raw('total_amount_paise'),
                ]);
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'ticket_fx_usd_to_inr'],
                ['value' => '83.000000']
            );
            DB::table('settings')->updateOrInsert(
                ['key' => 'ticket_fx_last_updated_at'],
                ['value' => now()->format('Y-m-d H:i:s')]
            );
        }
    }

    public function down()
    {
        if (Schema::hasTable('event_bookings')) {
            Schema::table('event_bookings', function (Blueprint $table) {
                foreach ([
                    'billing_country_code',
                    'display_currency',
                    'display_unit_amount_minor',
                    'display_total_amount_minor',
                    'fx_rate_usd_to_inr',
                ] as $column) {
                    if (Schema::hasColumn('event_bookings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('event_ticket_plans')) {
            Schema::table('event_ticket_plans', function (Blueprint $table) {
                foreach ([
                    'sort_order',
                    'mrp_inr_paise',
                    'sale_inr_paise',
                    'mrp_usd_cents',
                    'sale_usd_cents',
                    'is_featured',
                ] as $column) {
                    if (Schema::hasColumn('event_ticket_plans', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });

            try {
                DB::statement('ALTER TABLE `event_ticket_plans` ADD UNIQUE `event_ticket_plans_event_id_plan_index_unique` (`event_id`,`plan_index`)');
            } catch (\Throwable $e) {
                // Ignore if uniqueness cannot be restored automatically.
            }
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('key', [
                'ticket_fx_usd_to_inr',
                'ticket_fx_last_updated_at',
            ])->delete();
        }
    }
}

