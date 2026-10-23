<?php

use App\Support\AdminEmailTemplateSettings;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $raw = \DB::table('settings')->where('key', AdminEmailTemplateSettings::SETTING_KEY)->value('value');

        if (! $raw) {
            return;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return;
        }

        if (! is_array($data)) {
            return;
        }

        $defaults = AdminEmailTemplateSettings::defaults();
        $freshTemplate = $defaults['templates']['sponsor_inquiry'];

        if (isset($data['templates']['sponsor_inquiry'])) {
            $data['templates']['sponsor_inquiry']['subject'] = $freshTemplate['subject'];
            $data['templates']['sponsor_inquiry']['body'] = $freshTemplate['body'];
        }

        \DB::table('settings')
            ->where('key', AdminEmailTemplateSettings::SETTING_KEY)
            ->update(['value' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    public function down(): void {}
};
