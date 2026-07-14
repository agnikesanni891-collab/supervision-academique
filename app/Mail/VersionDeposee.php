<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VersionDeposee extends Mailable
{
    use Queueable, SerializesModels;

    public string $nomEtudiant;
    public string $titreMemoire;
    public int    $numeroVersion;

    public function __construct(string $nomEtudiant, string $titreMemoire, int $numeroVersion)
    {
        $this->nomEtudiant   = $nomEtudiant;
        $this->titreMemoire  = $titreMemoire;
        $this->numeroVersion = $numeroVersion;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📄 Nouveau dépôt — ' . $this->titreMemoire);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.version-deposee');
    }
}