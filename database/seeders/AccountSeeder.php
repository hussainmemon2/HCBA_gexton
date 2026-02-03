<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            // Assets
            [
                'account_group_id' => 6, // Cash
                'account_code' => '1001',
                'account_name' => 'Cash in Hand',
                'account_type' => 'asset',
                'opening_balance' => 50000,
                'current_balance' => 50000,
                'status' => 'active',
            ],
            [
                'account_group_id' => 7, // Banks
                'account_code' => '1010',
                'account_name' => 'Bank Al Habib',
                'account_type' => 'asset',
                'opening_balance' => 200000,
                'current_balance' => 200000,
                'status' => 'active',
            ],
            [
                'account_group_id' => 8, // Receivables
                'account_code' => '1020',
                'account_name' => 'Accounts Receivable',
                'account_type' => 'asset',
                'opening_balance' => 150000,
                'current_balance' => 150000,
                'status' => 'active',
            ],

            // Liabilities
            [
                'account_group_id' => 10, // Vendors Payable
                'account_code' => '2001',
                'account_name' => 'Payable to Vendors',
                'account_type' => 'liability',
                'opening_balance' => 100000,
                'current_balance' => 100000,
                'status' => 'active',
            ],
            [
                'account_group_id' => 11, // Committees Payable
                'account_code' => '2010',
                'account_name' => 'Payable to Committees',
                'account_type' => 'liability',
                'opening_balance' => 50000,
                'current_balance' => 50000,
                'status' => 'active',
            ],

            // Income
            [
                'account_group_id' => 18, // Donations
                'account_code' => '3001',
                'account_name' => 'Donations Received',
                'account_type' => 'income',
                'opening_balance' => 0,
                'current_balance' => 0,
                'status' => 'active',
            ],
            [
                'account_group_id' => 19, // Membership Fees
                'account_code' => '3010',
                'account_name' => 'Membership Fees',
                'account_type' => 'income',
                'opening_balance' => 0,
                'current_balance' => 0,
                'status' => 'active',
            ],

            // Expenses
            [
                'account_group_id' => 14, // Welfare Expenses
                'account_code' => '4001',
                'account_name' => 'Welfare Payments',
                'account_type' => 'expense',
                'opening_balance' => 0,
                'current_balance' => 0,
                'status' => 'active',
            ],
            [
                'account_group_id' => 16, // Office Expenses
                'account_code' => '4020',
                'account_name' => 'Office Supplies',
                'account_type' => 'expense',
                'opening_balance' => 0,
                'current_balance' => 0,
                'status' => 'active',
            ],

            // Equity
            [
                'account_group_id' => 21, // Capital
                'account_code' => '5001',
                'account_name' => 'Capital Investment',
                'account_type' => 'equity',
                'opening_balance' => 500000,
                'current_balance' => 500000,
                'status' => 'active',
            ],
            [
                'account_group_id' => 22, // Retained Earnings
                'account_code' => '5010',
                'account_name' => 'Retained Earnings',
                'account_type' => 'equity',
                'opening_balance' => 0,
                'current_balance' => 0,
                'status' => 'active',
            ],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }
    }
}
