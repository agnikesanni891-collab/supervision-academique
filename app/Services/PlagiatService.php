<?php

namespace App\Services;

use App\Models\VersionMemoire as Version;
use Smalot\PdfParser\Parser;

class PlagiatService
{

/**
 * Extrait les mots significatifs d'un titre (ignore mots vides).
 */
public function motsClesTitre(string $titre): array
{
    $motsVides = [
        'le','la','les','de','du','des','un','une','et','en','au','aux',
        'par','pour','sur','dans','avec','sans','vers','entre','ou','qui',
        'que','quoi','dont','où','se','sa','son','ses','mon','ton','ma',
        'ta','ce','cet','cette','ces','l','d','à','a','est','sont','the',
        'of','and','in','for','on','with','a','an','to','by',
    ];

    $titre = mb_strtolower($titre);
    $titre = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $titre);
    $mots  = array_filter(
        explode(' ', $titre),
        fn($m) => strlen($m) > 2 && !in_array($m, $motsVides)
    );

    return array_values(array_unique($mots));
}

/**
 * Vérifie si deux titres partagent au moins un mot significatif.
 */
public function memeTitre(string $titreA, string $titreB): bool
{
    $motsA = $this->motsClesTitre($titreA);
    $motsB = $this->motsClesTitre($titreB);

    return !empty(array_intersect($motsA, $motsB));
}
    /**
     * Extrait le texte d'un PDF à partir d'un chemin local.
     */
    public function extraireTexte(string $cheminLocal): string
    {
        \Log::info('[PlagiatService] Tentative extraction', ['chemin' => $cheminLocal]);

        try {
            // Si c'est une URL Cloudinary, télécharger d'abord
            if (str_starts_with($cheminLocal, 'http')) {
                $contenu = file_get_contents($cheminLocal);
                if ($contenu === false) {
                    \Log::error('[PlagiatService] Échec téléchargement URL', ['url' => $cheminLocal]);
                    return '';
                }
                $tmp = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
                file_put_contents($tmp, $contenu);
                $cheminLocal = $tmp;
            }

            $parser = new Parser();
            $pdf    = $parser->parseFile($cheminLocal);
            $texte  = $pdf->getText();

            \Log::info('[PlagiatService] PDF extrait', [
                'longueur' => strlen($texte),
                'apercu'   => mb_substr($texte, 0, 100),
            ]);

            return $this->nettoyer($texte);

        } catch (\Exception $e) {
            \Log::error('[PlagiatService] Erreur extraction PDF', [
                'chemin'  => $cheminLocal,
                'message' => $e->getMessage(),
            ]);
            return '';
        }
    }

    /**
     * Nettoie le texte : minuscules, suppression ponctuation/espaces multiples.
     */
    private function nettoyer(string $texte): string
    {
        $texte = mb_strtolower($texte);
        $texte = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $texte);
        $texte = preg_replace('/\s+/', ' ', $texte);
        return trim($texte);
    }

    /**
     * Découpe le texte en shingles (n-grammes de mots).
     */
    private function shingles(string $texte, int $taille = 5): array
    {
        $mots = explode(' ', $texte);
        $shingles = [];
        $n = count($mots);
        for ($i = 0; $i <= $n - $taille; $i++) {
            $shingles[] = implode(' ', array_slice($mots, $i, $taille));
        }
        return array_unique($shingles);
    }

    /**
     * Calcule la similarité de Jaccard entre deux textes (0 à 100).
     */
    /**
     * hbdjsbdhsbcxjjjjjjjjjjjjjjjjjjjjjjjd.
     */
    public function similarite(string $texteA, string $texteB, int $tailleShingle = 5): float
    {
        $shA = $this->shingles($texteA, $tailleShingle);
        $shB = $this->shingles($texteB, $tailleShingle);

        if (empty($shA) || empty($shB)) return 0.0;

        $setA = array_flip($shA);
        $setB = array_flip($shB);

        $intersection = array_intersect_key($setA, $setB);
        $union        = $setA + $setB;

        if (count($union) === 0) return 0.0;

        return round((count($intersection) / count($union)) * 100, 2);
    }

    /**
     * Retourne les passages communs (shingles partagés) entre deux textes.
     */
    public function passagesCommuns(string $texteA, string $texteB, int $tailleShingle = 8, int $max = 5): array
    {
        $shA = $this->shingles($texteA, $tailleShingle);
        $shB = array_flip($this->shingles($texteB, $tailleShingle));

        $communs = [];
        foreach ($shA as $s) {
            if (isset($shB[$s])) {
                $communs[] = $s;
                if (count($communs) >= $max) break;
            }
        }
        return $communs;
    }

    /**
     * Compare une version donnée avec la dernière version de chaque autre mémoire.
     */
public function comparer(Version $versionCible, $versionsAComparer, float $seuilMinimum = 10.0): array
{
    $texteCible  = $versionCible->texte_extrait;
    $titreCible  = $versionCible->memoire->titre ?? '';

    if (empty($texteCible)) return [];

    // Ne garder que la dernière version de chaque mémoire
    $dernieresVersions = $versionsAComparer
        ->groupBy('id_memoire')
        ->map(fn($versions) => $versions->sortByDesc('numero_version')->first());

    $resultats = [];

    foreach ($dernieresVersions as $autre) {
        if ($autre->id_version === $versionCible->id_version) continue;
        if (empty($autre->texte_extrait)) continue;

        // ✅ Filtrer par thème : titres avec mots en commun seulement
        $titreAutre = $autre->memoire->titre ?? '';
        if (!$this->memeTitre($titreCible, $titreAutre)) continue;

        $score = $this->similarite($texteCible, $autre->texte_extrait);

        if ($score >= $seuilMinimum) {
            $resultats[] = [
                'id_version'     => $autre->id_version,
                'id_memoire'     => $autre->id_memoire,
                'titre_memoire'  => $titreAutre,
                'etudiant'       => $autre->etudiant
                    ? trim(($autre->etudiant->prenom ?? '') . ' ' . ($autre->etudiant->nom ?? ''))
                    : null,
                'numero_version' => $autre->numero_version,
                'score'          => $score,
                'passages'       => $this->passagesCommuns($texteCible, $autre->texte_extrait),
                'mots_communs'   => array_intersect(
                    $this->motsClesTitre($titreCible),
                    $this->motsClesTitre($titreAutre)
                ),
            ];
        }
    }

    usort($resultats, fn($a, $b) => $b['score'] <=> $a['score']);

    return $resultats;
}
}