<?php

namespace App\Console\Commands;

use App\Mail\VersionDeposee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmailDepot extends Command
{
    protected $signature = 'email:send-depot {email} {etudiant} {memoire} {version}';
    protected $description = 'Envoie email dépôt version';

    public function handle()
    {
        Mail::to($this->argument('email'))->send(
            new VersionDeposee(
                $this->argument('etudiant'),
                $this->argument('memoire'),
                (int) $this->argument('version')
            )
        );
    }
}