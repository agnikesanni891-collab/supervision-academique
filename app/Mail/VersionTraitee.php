<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VersionTraitee extends Mailable
{
    use Queueable, SerializesModels;

    public string $nomEncadrant;
    public string $titreMemoire;
    public int    $numeroVersion;
    public string $statut;

    public function __construct(string $nomEncadrant, string $titreMemoire, int $numeroVersion, string $statut)
    {
        $this->nomEncadrant  = $nomEncadrant;
        $this->titreMemoire  = $titreMemoire;
        $this->numeroVersion = $numeroVersion;
        $this->statut        = $statut;
    }

    public function envelope(): Envelope
    {
        $sujet = $this->statut === 'accepte'
            ? '✅ Version acceptée — ' . $this->titreMemoire
            : '❌ Version rejetée — ' . $this->titreMemoire;
        return new Envelope(subject: $sujet);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.version-traitee');
    }
}