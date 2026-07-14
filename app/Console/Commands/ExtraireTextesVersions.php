<?php

namespace App\Console\Commands;

use App\Models\VersionMemoire as Version;
use App\Services\PlagiatService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ExtraireTextesVersions extends Command
{
    protected $signature = 'plagiat:extraire-existants';
    protected $description = 'Extrait le texte des PDF des versions existantes pour la détection de plagiat';

    public function handle()
    {
        $service = new PlagiatService();

        $versions = Version::whereNull('texte_extrait')
            ->whereNotNull('url_fichier')
            ->get();

        $this->info("Trouvé {$versions->count()} version(s) sans texte extrait.");

        foreach ($versions as $version) {
            $this->line("Traitement version #{$version->id_version} ({$version->url_fichier})...");

            try {
                $response = Http::timeout(60)->get($version->url_fichier);

                if (!$response->successful()) {
                    $this->warn("  → Échec téléchargement (HTTP {$response->status()})");
                    continue;
                }

                $tmpPath = storage_path('app/tmp_' . $version->id_version . '.pdf');
                file_put_contents($tmpPath, $response->body());

                $texte = $service->extraireTexte($tmpPath);

                if (!empty($texte)) {
                    $version->update(['texte_extrait' => $texte]);
                    $this->info("  → Texte extrait (" . strlen($texte) . " caractères).");
                } else {
                    $this->warn("  → Aucun texte extrait (PDF scanné/image ?).");
                }

                @unlink($tmpPath);

            } catch (\Exception $e) {
                $this->error("  → Erreur : " . $e->getMessage());
            }
        }

        $this->info('Terminé.');
    }
}