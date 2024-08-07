<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
class SeedUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:user:create {name=defaultname} {email=default@example.com} {password=defaultpassword}';

	public function __construct()
    {
        parent::__construct();
    }
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->error('Invalid email format.');
				return;
		}
		if (User::where('email', $email)->exists()) {
            $this->error('A user with this email already exists.');
            return;
        }
		if (strlen($password) < 3) {
			$this->error('Name must be at least 3 characters long.');
			return;
		}
		if (strlen($password) < 8) {
			$this->error('Password must be at least 6 characters long.');
			return;
		}
		
        User::create([
            'name' => $name,
            'email' => $email,
			'phone' => null,
            'password' => bcrypt($password),
			'role' => "superadmin",
        ]);

        $this->info('Admin created successfully!');
    }
}
