<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ManageAdminCommand extends Command
{
    protected $signature = 'user:admin {email : E-mailadres van de gebruiker} {--revoke : Adminrechten intrekken in plaats van toekennen}';

    protected $description = 'Ken adminrechten toe aan een gebruiker of trek ze in';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("Geen gebruiker gevonden met e-mailadres {$this->argument('email')}.");

            return self::FAILURE;
        }

        $user->is_admin = ! $this->option('revoke');
        $user->save();

        $this->info($user->is_admin
            ? "{$user->email} is nu admin."
            : "Adminrechten ingetrokken voor {$user->email}.");

        return self::SUCCESS;
    }
}
