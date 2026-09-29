<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {--email=zeeshanadmin@gmail.com} {--password=123623} {--fname=Zeeshan} {--lname=Admin} {--phone=0000000000} {--role=1}';
    protected $description = 'Create or update an admin user';

    public function handle()
    {
        $email    = $this->option('email');
        $password = $this->option('password');
        $fname    = $this->option('fname');
        $lname    = $this->option('lname');
        $phone    = $this->option('phone');
        $role     = $this->option('role');

        $existing = DB::table('admins')->where('email', $email)->first();

        if ($existing) {
            DB::table('admins')->where('email', $email)->update([
                'password'   => Hash::make($password),
                'updated_at' => now(),
            ]);
            $this->info("Admin [{$email}] already exists. Password has been updated successfully.");
        } else {
            DB::table('admins')->insert([
                'f_name'         => $fname,
                'l_name'         => $lname,
                'phone'          => $phone,
                'email'          => $email,
                'image'          => 'def.png',
                'password'       => Hash::make($password),
                'role_id'        => $role,
                'remember_token' => Str::random(10),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $this->info("Admin created successfully!");
        }

        $this->table(['Field', 'Value'], [
            ['Email',    $email],
            ['Password', $password],
        ]);

        return Command::SUCCESS;
    }
}
