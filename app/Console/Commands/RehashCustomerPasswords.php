<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class RehashCustomerPasswords extends Command
{
    protected $signature = 'customers:rehash-passwords';
    protected $description = 'Re-hash all customer passwords using bcrypt if not already hashed.';

    public function handle()
    {
        $count = 0;
        foreach (Customer::all() as $customer) {
            if (!Hash::needsRehash($customer->password)) {
                continue;
            }
            $customer->password = Hash::make($customer->password);
            $customer->save();
            $count++;
        }
        $this->info("Re-hashed $count customer passwords.");
    }
}
