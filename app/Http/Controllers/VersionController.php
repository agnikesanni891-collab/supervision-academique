<?php

namespace App\Http\Controllers;
use App\Services\PlagiatService;
use App\Models\Memoire;
use App\Models\VersionMemoire as Version;
use App\Models\Notification;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Etablissement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VersionController extends Controller
{
    public function index($id_memoire)
    {
        $memoire = Memoire::findOrFail($id_memoire);
        $user    = Auth::user();

        if ($user->role === 'etudiant' && !$memoire->etudiants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }
        if ($user->role === 'encadrant' && !$memoire->encadrants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json(
            Version::with('annotations')
                ->where('id_memoire', $id_memoire)
                ->orderBy('numero_version', 'desc')
                ->get()
        );
    }

public function verifierPlagiat($id_memoire, $id_version)
{
    $user    = Auth::user();
    $memoire = Memoire::findOrFail($id_memoire);

    if (!in_array($user->role, ['encadrant', 'admin_ecole', 'admin_plateforme'])) {
        return response()->json(['message' => 'Non autorisé.'], 403);
    }
    if ($user->role === 'encadrant' && !$memoire->encadrants->contains('id_user', $user->id_user)) {
        return response()->json(['message' => 'Vous n\'encadrez pas ce mémoire.'], 403);
    }

    $versionCible = Version::where('id_memoire', $id_memoire)->findOrFail($id_version);

    if (empty($versionCible->texte_extrait)) {
        return response()->json([
            'message' => 'Le texte de cette version n\'a pas pu être extrait (PDF scanné/image ou ancien dépôt sans extraction).',
        ], 422);
    }

    $idsMemoiresFiliere = Memoire::where('id_filiere', $memoire->id_filiere)
        ->where('id_memoire', '!=', $id_memoire)
        ->pluck('id_memoire');

    $versionsAComparer = Version::with(['memoire', 'etudiant'])
        ->whereIn('id_memoire', $idsMemoiresFiliere)
        ->whereNotNull('texte_extrait')
        ->get();

    $plagiatService = new \App\Services\PlagiatService();
    $resultats = $plagiatService->comparer($versionCible, $versionsAComparer);

    return response()->json([
        'version_verifiee' => [
            'id_version'     => $versionCible->id_version,
            'numero_version' => $versionCible->numero_version,
            'titre_memoire'  => $memoire->titre,
            'mots_cles'      => $plagiatService->motsClesTitre($memoire->titre),
        ],
        'resultats'             => $resultats,
        'nb_documents_compares' => $versionsAComparer->groupBy('id_memoire')->count(),
        'nb_themes_similaires'  => count($resultats),
    ]);
}


    public function store(Request $request, $id_memoire)
    {
        $user    = Auth::user();
        $memoire = Memoire::with('encadrants.utilisateur')->findOrFail($id_memoire);

        if ($user->role !== 'etudiant' || !$memoire->etudiants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Seul l\'étudiant du mémoire peut déposer une version.'], 403);
        }

        $request->validate([
            'fichier' => 'required|file|mimes:pdf|max:20480',
        ]);

        $dernierNumero = Version::where('id_memoire', $id_memoire)->max('numero_version') ?? 0;
        $nouveauNumero = $dernierNumero + 1;

        if ($dernierNumero > 0) {
            $versionPrecedente = Version::where('id_memoire', $id_memoire)
                                    ->where('numero_version', $dernierNumero)
                                    ->first();
            if ($versionPrecedente && !in_array($versionPrecedente->statut_version, ['accepte', 'rejete'])) {
                return response()->json([
                    'message' => 'Vous ne pouvez pas déposer une nouvelle version tant que la précédente n\'a pas été traitée. (RG13)'
                ], 422);
            }
        }

        $etudiant      = Etudiant::where('id_user', $user->id_user)->first();
        $filiere       = $etudiant ? Filiere::find($etudiant->id_filiere) : null;
        $etablissement = $filiere ? Etablissement::find($filiere->id_etablissement) : null;

        $nomEtab    = $etablissement
            ? str_replace(' ', '_', $etablissement->nom_etablissement)
            : 'sans_etablissement';

        $nomFiliere = $filiere
            ? str_replace(' ', '_', $filiere->libelle_filiere)
            : 'sans_filiere';

        $nomEtudiant = str_replace(' ', '_', $user->prenom . '_' . $user->nom);
        $fichier     = $request->file('fichier');
        $nomFichier  = "version_{$nouveauNumero}_" . time() . ".pdf";

        // ✅ ÉTAPE 1 : Extraire le texte immédiatement
        $plagiatService = new PlagiatService();
        $texteExtrait   = $plagiatService->extraireTexte($fichier->getRealPath());

        // ✅ ÉTAPE 2 : Créer la version en base AVANT Cloudinary
       $version = Version::create([
    'numero_version'       => $nouveauNumero,
    'url_fichier'          => '',
    'public_id_cloudinary' => null,
    'taille_fichier'       => (int) round($fichier->getSize() / 1024),
    'texte_extrait'        => $texteExtrait,
    'statut_version'       => 'soumis',
    'id_memoire'           => $id_memoire,
    'id_etudiant'          => $user->id_user,
    'date_depot'           => now(),
]);

// ✅ Passer le mémoire en "en_cours" si il était encore "cree"
if ($memoire->statut === 'cree') {
    $memoire->update(['statut' => 'en_cours']);
}

        // ✅ ÉTAPE 3 : Upload Cloudinary en arrière-plan (queue)
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $nomFichier;
        copy($fichier->getRealPath(), $tmpPath);

        \App\Jobs\UploadVersionCloudinary::dispatch(
            $version->id_version,
            $tmpPath,
            $nomFichier,
            "supervision/{$nomEtab}/{$nomFiliere}/{$nomEtudiant}"
        );

        // ✅ ÉTAPE 4 : Notifications
        $ordreVersion = Version::where('id_memoire', $id_memoire)
            ->orderBy('numero_version', 'asc')
            ->pluck('id_version')
            ->search($version->id_version) + 1;

        $nomEtudiantAffichage = $user->prenom . ' ' . $user->nom;

        foreach ($memoire->encadrants as $encadrant) {
            Notification::create([
                'type_evenement' => 'depot',
                'message'        => "L'étudiant {$nomEtudiantAffichage} a déposé la version {$ordreVersion} du mémoire \"{$memoire->titre}\".",
                'id_user'        => $encadrant->id_user,
                'id_version'     => $version->id_version,
            ]);

            $emailEncadrant = $encadrant->utilisateur?->email;
            if ($emailEncadrant) {
                $cmd = sprintf(
                    'php %s email:send-depot %s %s %s %d > NUL 2>&1 &',
                    base_path('artisan'),
                    escapeshellarg($emailEncadrant),
                    escapeshellarg($nomEtudiantAffichage),
                    escapeshellarg($memoire->titre),
                    $ordreVersion
                );
                pclose(popen($cmd, 'r'));
            }
        }

        return response()->json($version, 201);
    }

    public function remplacer(Request $request, $id_memoire, $id_version)
    {
        $user    = Auth::user();
        $memoire = Memoire::findOrFail($id_memoire);

        if ($user->role !== 'etudiant' || !$memoire->etudiants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $version = Version::where('id_memoire', $id_memoire)->findOrFail($id_version);

        if ($version->statut_version !== 'soumis') {
            return response()->json(['message' => 'Impossible de modifier une version déjà traitée.'], 422);
        }

        $request->validate([
            'fichier' => 'required|file|mimes:pdf|max:20480',
        ]);

        $etudiant      = Etudiant::where('id_user', $user->id_user)->first();
        $filiere       = $etudiant ? Filiere::find($etudiant->id_filiere) : null;
        $etablissement = $filiere ? Etablissement::find($filiere->id_etablissement) : null;

        $nomEtab    = $etablissement
            ? str_replace(' ', '_', $etablissement->nom_etablissement)
            : 'sans_etablissement';
        $nomFiliere = $filiere
            ? str_replace(' ', '_', $filiere->libelle_filiere)
            : 'sans_filiere';
        $nomEtudiant = str_replace(' ', '_', $user->prenom . '_' . $user->nom);

        $fichier    = $request->file('fichier');
        $nomFichier = "version_{$version->numero_version}_" . time() . ".pdf";

        // ✅ ÉTAPE 1 : Extraire le texte immédiatement
        $plagiatService = new PlagiatService();
        $texteExtrait   = $plagiatService->extraireTexte($fichier->getRealPath());

        // ✅ ÉTAPE 2 : Sauvegarder le texte en base AVANT Cloudinary
        $version->update([
            'taille_fichier' => (int) round($fichier->getSize() / 1024),
            'texte_extrait'  => $texteExtrait,
            'date_depot'     => now(),
        ]);

        // ✅ ÉTAPE 3 : Upload Cloudinary en arrière-plan (queue)
        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $nomFichier;
        copy($fichier->getRealPath(), $tmpPath);

        \App\Jobs\ReplaceVersionCloudinary::dispatch(
            $version->id_version,
            $tmpPath,
            $nomFichier,
            "supervision/{$nomEtab}/{$nomFiliere}/{$nomEtudiant}",
            $version->public_id_cloudinary
        );

        return response()->json($version);
    }

    public function show($id_memoire, $id_version)
    {
        $user    = Auth::user();
        $memoire = Memoire::findOrFail($id_memoire);

        if ($user->role === 'etudiant' && !$memoire->etudiants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }
        if ($user->role === 'encadrant' && !$memoire->encadrants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json(
            Version::with('annotations')
                ->where('id_memoire', $id_memoire)
                ->findOrFail($id_version)
        );
    }

    public function changerStatut(Request $request, $id_memoire, $id_version)
    {
        $user    = Auth::user();
        $memoire = Memoire::with([
            'etudiants.utilisateur',
            'encadrants.utilisateur',
        ])->findOrFail($id_memoire);

        if (!in_array($user->role, ['encadrant', 'admin_ecole', 'admin_plateforme'])) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($user->role === 'encadrant' && !$memoire->encadrants->contains('id_user', $user->id_user)) {
            return response()->json(['message' => 'Vous n\'encadrez pas ce mémoire.'], 403);
        }

        $version = Version::where('id_memoire', $id_memoire)->findOrFail($id_version);

        $data = $request->validate([
            'statut_version' => 'required|in:accepte,rejete',
        ]);

        $version->update(['statut_version' => $data['statut_version']]);

        $ordreVersion = Version::where('id_memoire', $id_memoire)
            ->orderBy('numero_version', 'asc')
            ->pluck('id_version')
            ->search($version->id_version) + 1;

        $nomEncadrant = $user->prenom . ' ' . $user->nom;

        foreach ($memoire->etudiants as $etudiant) {
            Notification::create([
                'type_evenement' => $data['statut_version'] === 'accepte' ? 'acceptation' : 'rejet',
                'message'        => $data['statut_version'] === 'accepte'
                    ? "Votre version {$ordreVersion} a été acceptée par {$nomEncadrant}."
                    : "Votre version {$ordreVersion} a été rejetée par {$nomEncadrant}.",
                'id_user'    => $etudiant->id_user,
                'id_version' => $version->id_version,
            ]);

            $emailEtudiant = $etudiant->utilisateur?->email;
if ($emailEtudiant) {
    \Illuminate\Support\Facades\Mail::to($emailEtudiant)->send(
        new \App\Mail\VersionTraitee(
            $nomEncadrant,
            $memoire->titre,
            $ordreVersion,
            $data['statut_version']
        )
    );
}
        }

        return response()->json($version);
    }
}