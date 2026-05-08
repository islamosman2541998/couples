<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name',        'value' => 'Funny Couples ',                    'group' => 'general'],
            ['key' => 'site_description', 'value' => 'موقع ألعاب ترفيهية مميزة للأزواج', 'group' => 'general'],
            ['key' => 'site_logo',        'value' => null,                                 'group' => 'general'],
            ['key' => 'footer_text',      'value' => 'جميع الحقوق محفوظة © 2024',         'group' => 'general'],
            ['key' => 'primary_color',    'value' => '#7c3aed',                            'group' => 'general'],
            ['key' => 'secondary_color',  'value' => '#ec4899',                            'group' => 'general'],

            // Contact
            ['key' => 'contact_phone',    'value' => '0500000000', 'group' => 'contact'],
            ['key' => 'contact_email',    'value' => 'info@example.com', 'group' => 'contact'],
            ['key' => 'contact_whatsapp', 'value' => '0500000000', 'group' => 'contact'],
            ['key' => 'bank_account',     'value' => 'SA00 0000 0000 0000 0000 0000', 'group' => 'contact'],
            ['key' => 'bank_name',        'value' => 'البنك الأهلي السعودي', 'group' => 'contact'],
            ['key' => 'bank_holder',      'value' => 'اسم صاحب الحساب', 'group' => 'contact'],

            // Social
            ['key' => 'social_twitter',   'value' => '', 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => '', 'group' => 'social'],
            ['key' => 'social_snapchat',  'value' => '', 'group' => 'social'],
            ['key' => 'social_tiktok',    'value' => '', 'group' => 'social'],

            // Pages
            ['key' => 'about_text',   'value' => 'نحن موقع متخصص في الألعاب الترفيهية للأزواج، نقدم تجربة فريدة من نوعها لتعزيز الترابط والمتعة المشتركة.', 'group' => 'pages'],
            ['key' => 'privacy_text', 'value' => 'نحن نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية. لن نشارك معلوماتك مع أي طرف ثالث دون إذنك.', 'group' => 'pages'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}