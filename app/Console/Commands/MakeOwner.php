<?php

namespace App\Console\Commands;

use App\Models\Owner;
use App\Models\User;
use Illuminate\Console\Command;

class MakeOwner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-owner {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create owner account, returns admin details if successful, otherwise returns an error message.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $optionalArgPassword = $this->argument('password');
        $generatedPassword = "";
        
        if (!$optionalArgPassword) {
            $this->info("No password provided, Creating user account with randomized password....");
            $generatedPassword = substr(md5(rand()), 0, 8);
        } else {
            $this->info("Password is provided, Creating user account....");
            $generatedPassword = $optionalArgPassword;
        }

        $generatedUser = User::factory()->create([
            "first_name" => "Admin." . rand(),
            "last_name" => "Admin." . rand(),
            "email" => $this->argument('email') ? $this->argument('email') : "admin." . rand() . "@lakbaycampsite.com",
            "password" => bcrypt($generatedPassword),
        ]);

        Owner::factory()->create([
            "user_id" => $generatedUser->id
        ]);

        $this->info("-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-");
        $this->info("User: $generatedUser->name");
        $this->info("Email: $generatedUser->email");
        $this->info("Password: $generatedPassword");
        $this->info("-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-");
        $this->info("Admin account created successfully!, Please save the details below");
    }
}
