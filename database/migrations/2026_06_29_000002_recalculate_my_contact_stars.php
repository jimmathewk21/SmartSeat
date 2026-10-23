<?php

use App\Models\MyContact;
use App\Services\MyContactStarService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $service = app(MyContactStarService::class);

        MyContact::query()
            ->select(['id', 'category', 'experience', 'role', 'speaker_tag', 'is_whitelisted'])
            ->orderBy('id')
            ->chunkById(200, function ($contacts) use ($service): void {
                foreach ($contacts as $contact) {
                    DB::table('my_contacts')
                        ->where('id', $contact->id)
                        ->update([
                            'stars' => $service->calculate($contact),
                        ]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
