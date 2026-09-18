<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin
                            {email : The email address of the user to promote}
                            {--revoke : Revoke admin access instead of granting it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant (or revoke) admin panel access for a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $revoking = (bool) $this->option('revoke');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->components->error("No user found with the email [{$email}].");
            $this->components->info('Users are created on their first WorkOS login, so sign in once before running this.');

            return self::FAILURE;
        }

        if ($user->isAdmin() === ! $revoking) {
            $this->components->warn("[{$email}] is already ".($revoking ? 'a regular user.' : 'an admin.'));

            return self::SUCCESS;
        }

        if ($revoking && User::where('is_admin', true)->count() === 1) {
            $this->components->warn('This is the only admin account left.');

            if (! confirm('Revoke anyway? Nobody will be able to reach the admin panel.', default: false)) {
                return self::FAILURE;
            }
        }

        $user->is_admin = ! $revoking;
        $user->save();

        $this->components->info($revoking
            ? "Admin access revoked for [{$email}]."
            : "[{$email}] can now access the admin panel.");

        return self::SUCCESS;
    }
}
