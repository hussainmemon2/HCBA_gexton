<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountGroup;

class AccountGroupSeeder extends Seeder
{
    public function run(): void
    {
        // ROOT GROUPS
        $assets = AccountGroup::create([
            'group_name' => 'Assets',
            'parent_id'  => null
        ]);

        $liabilities = AccountGroup::create([
            'group_name' => 'Liabilities',
            'parent_id'  => null
        ]);

        $income = AccountGroup::create([
            'group_name' => 'Income',
            'parent_id'  => null
        ]);

        $expenses = AccountGroup::create([
            'group_name' => 'Expenses',
            'parent_id'  => null
        ]);

        $equity = AccountGroup::create([
            'group_name' => 'Equity',
            'parent_id'  => null
        ]);

        // ASSETS SUB GROUPS
        AccountGroup::insert([
            ['group_name' => 'Cash',        'parent_id' => $assets->id],
            ['group_name' => 'Banks',       'parent_id' => $assets->id],
            ['group_name' => 'Receivables', 'parent_id' => $assets->id],
            ['group_name' => 'Advances',    'parent_id' => $assets->id],
        ]);

        // LIABILITIES SUB GROUPS
        AccountGroup::insert([
            ['group_name' => 'Vendors Payable',    'parent_id' => $liabilities->id],
            ['group_name' => 'Committees Payable', 'parent_id' => $liabilities->id],
            ['group_name' => 'Loans',              'parent_id' => $liabilities->id],
            ['group_name' => 'Accrued Expenses',   'parent_id' => $liabilities->id],
        ]);

        // EXPENSES SUB GROUPS
        AccountGroup::insert([
            ['group_name' => 'Welfare Expenses',    'parent_id' => $expenses->id],
            ['group_name' => 'Committee Expenses', 'parent_id' => $expenses->id],
            ['group_name' => 'Office Expenses',    'parent_id' => $expenses->id],
            ['group_name' => 'Utility Expenses',   'parent_id' => $expenses->id],
        ]);

        // INCOME SUB GROUPS
        AccountGroup::insert([
            ['group_name' => 'Donations',        'parent_id' => $income->id],
            ['group_name' => 'Membership Fees', 'parent_id' => $income->id],
            ['group_name' => 'Other Income',     'parent_id' => $income->id],
        ]);

        // EQUITY SUB GROUPS
        AccountGroup::insert([
            ['group_name' => 'Capital',            'parent_id' => $equity->id],
            ['group_name' => 'Retained Earnings',  'parent_id' => $equity->id],
        ]);
    }
}
