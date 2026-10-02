<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['code' => 'bca', 'name' => 'Transfer Bank BCA', 'instructions' => 'Transfer ke rekening BCA lalu simpan bukti pembayaran.', 'sort_order' => 10],
            ['code' => 'dana', 'name' => 'DANA', 'instructions' => 'Kirim pembayaran melalui akun DANA yang tertera.', 'sort_order' => 20],
            ['code' => 'gopay', 'name' => 'GoPay', 'instructions' => 'Kirim pembayaran melalui akun GoPay yang tertera.', 'sort_order' => 30],
        ] as $method) {
            PaymentMethod::query()->firstOrCreate(
                ['code' => $method['code']],
                [...$method, 'account_name' => 'Memoire', 'account_number' => '0000000000', 'is_active' => true],
            );
        }
    }
}
