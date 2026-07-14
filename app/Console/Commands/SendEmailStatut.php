<?php

namespace App\Console\Commands;

use App\Mail\VersionTraitee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEmailStatut extends Command
{
    protected $signature = 'email:send-statut {email} {encadrant} {memoire} {version} {statut}';
    protected $description = 'Envoie email statut version';

    public function handle()
    {
        Mail::to($this->argument('email'))->send(
            new VersionTraitee(
                $this->argument('encadrant'),
                $this->argument('memoire'),
                (int) $this->argument('version'),
                $this->argument('statut')
            )
        );
    }
}