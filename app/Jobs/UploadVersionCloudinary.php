<?php

namespace App\Jobs;

use App\Models\VersionMemoire as Version;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadVersionCloudinary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $idVersion;
    public $cheminTemp;
    public $nomFichier;
    public $dossier;

    public function __construct($idVersion, $cheminTemp, $nomFichier, $dossier)
    {
        $this->idVersion  = $idVersion;
        $this->cheminTemp = $cheminTemp;
        $this->nomFichier = $nomFichier;
        $this->dossier    = $dossier;
    }

    public function handle(): void
    {
        \Cloudinary\Configuration\Configuration::instance([
            'cloud' => [
                'cloud_name' => 'dosbgexku',
                'api_key'    => '754778153229875',
                'api_secret' => 'Crn6-Iw5A05Opf-Cc1fTcCxUo3A',
            ],
            'url' => ['secure' => true]
        ]);

        try {
            $uploadApi = new \Cloudinary\Api\Upload\UploadApi();

            $result = $uploadApi->upload($this->cheminTemp, [
                'folder'          => $this->dossier,
                'public_id'       => $this->nomFichier,
                'resource_type'   => 'raw',
                'use_filename'    => true,
                'unique_filename' => false,
                'overwrite'       => true,
                'access_mode'     => 'public',
                'type'            => 'upload',
            ]);

            $version = Version::find($this->idVersion);
            if ($version) {
                $version->update([
                    'url_fichier'          => $result['secure_url'],
                    'public_id_cloudinary' => "{$this->dossier}/{$this->nomFichier}",
                ]);
            }

            // ✅ Préchauffe le cache CDN pour éviter la lenteur au premier accès
            try {
                \Illuminate\Support\Facades\Http::timeout(30)->get($result['secure_url']);
            } catch (\Exception $e) {
                \Log::warning('[UploadVersionCloudinary] Warm-up CDN échoué', ['error' => $e->getMessage()]);
            }

        } catch (\Exception $e) {
            \Log::error('[UploadVersionCloudinary] Échec', ['error' => $e->getMessage()]);
        } finally {
            @unlink($this->cheminTemp);
        }
    }
}