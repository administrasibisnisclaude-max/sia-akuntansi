<?php

namespace Database\Seeders;

use App\Models\{Account, PaymentMethod};
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $kas     = Account::where('account_code', '1101')->first();
        $bca     = Account::where('account_code', '1102')->first();
        $mandiri = Account::where('account_code', '1103')->first();

        $methods = [
            ['name' => 'Kas',          'type' => 'cash', 'account_id' => $kas?->id],
            ['name' => 'Bank BCA',     'type' => 'bank', 'account_id' => $bca?->id],
            ['name' => 'Bank Mandiri', 'type' => 'bank', 'account_id' => $mandiri?->id],
        ];

        foreach ($methods as $m) {
            PaymentMethod::create(array_merge($m, ['is_active' => true]));
        }
    }
}
