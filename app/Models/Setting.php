<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** @return array<string, string> */
    public static function publicValues(): array
    {
        $defaults = [
            'brand_name' => 'Memoire',
            'brand_tagline' => 'A home for moments',
            'whatsapp_number' => '6281372339347',
            'instagram_url' => 'https://www.instagram.com/memoire.___/',
            'chat_greeting' => 'Halo, ada yang bisa kami bantu?',
        ];

        if (! self::query()->getConnection()->getSchemaBuilder()->hasTable('settings')) {
            return $defaults;
        }

        return array_replace($defaults, self::query()->pluck('value', 'key')->all());
    }
}
