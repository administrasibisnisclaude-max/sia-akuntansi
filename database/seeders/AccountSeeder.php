<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Assets
            ['1000', 'Aset',                                    'Asset',     'debit',  null],
            ['1100', 'Aset Lancar',                             'Asset',     'debit',  '1000'],
            ['1101', 'Kas',                                     'Asset',     'debit',  '1100'],
            ['1102', 'Bank BCA',                                'Asset',     'debit',  '1100'],
            ['1103', 'Bank Mandiri',                            'Asset',     'debit',  '1100'],
            ['1200', 'Piutang Usaha',                           'Asset',     'debit',  '1100'],
            ['1300', 'Persediaan Barang',                       'Asset',     'debit',  '1100'],
            ['1400', 'Biaya Dibayar Dimuka',                    'Asset',     'debit',  '1100'],
            ['1500', 'Aset Tetap',                              'Asset',     'debit',  '1000'],
            ['1510', 'Peralatan',                               'Asset',     'debit',  '1500'],
            ['1511', 'Akumulasi Penyusutan Peralatan',          'Asset',     'credit', '1500'],
            ['1520', 'Kendaraan',                               'Asset',     'debit',  '1500'],
            ['1521', 'Akumulasi Penyusutan Kendaraan',          'Asset',     'credit', '1500'],
            // Liabilities
            ['2000', 'Kewajiban',                               'Liability', 'credit', null],
            ['2100', 'Utang Usaha',                             'Liability', 'credit', '2000'],
            ['2200', 'Utang Pajak',                             'Liability', 'credit', '2000'],
            ['2300', 'Pendapatan Diterima Dimuka',              'Liability', 'credit', '2000'],
            ['2400', 'Utang Jangka Panjang',                    'Liability', 'credit', '2000'],
            // Equity
            ['3000', 'Ekuitas',                                 'Equity',    'credit', null],
            ['3100', 'Modal Pemilik',                           'Equity',    'credit', '3000'],
            ['3900', 'Laba Ditahan',                            'Equity',    'credit', '3000'],
            // Revenue
            ['4000', 'Pendapatan',                              'Revenue',   'credit', null],
            ['4100', 'Penjualan',                               'Revenue',   'credit', '4000'],
            ['4200', 'Pendapatan Jasa',                         'Revenue',   'credit', '4000'],
            ['4900', 'Pendapatan Lain-lain',                    'Revenue',   'credit', '4000'],
            // Expenses
            ['5000', 'Beban',                                   'Expense',   'debit',  null],
            ['5100', 'Harga Pokok Penjualan',                   'Expense',   'debit',  '5000'],
            ['5200', 'Beban Operasional',                       'Expense',   'debit',  '5000'],
            ['5210', 'Beban Gaji',                              'Expense',   'debit',  '5200'],
            ['5220', 'Beban Sewa',                              'Expense',   'debit',  '5200'],
            ['5230', 'Beban Utilitas',                          'Expense',   'debit',  '5200'],
            ['5240', 'Beban Penyusutan',                        'Expense',   'debit',  '5200'],
            ['5250', 'Beban Pemasaran',                         'Expense',   'debit',  '5200'],
            ['5900', 'Beban Lain-lain',                         'Expense',   'debit',  '5000'],
        ];

        $idMap = [];
        foreach ($accounts as [$code, $name, $type, $normalBalance, $parentCode]) {
            $parentId = $parentCode ? ($idMap[$parentCode] ?? null) : null;
            $account = Account::create([
                'account_code'   => $code,
                'name'           => $name,
                'type'           => $type,
                'normal_balance' => $normalBalance,
                'parent_id'      => $parentId,
                'is_active'      => true,
            ]);
            $idMap[$code] = $account->id;
        }
    }
}
