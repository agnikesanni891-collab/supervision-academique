<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // GET /api/notifications
    // Retourne les notifications de l'utilisateur connecté
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Notification::where('id_user', $user->id_user)
            ->orderBy('date_envoi', 'desc');

        // Filtre optionnel : non lues seulement
        if ($request->query('non_lues')) {
            $query->where('est_lue', false);
        }

        $notifications = $query->paginate(20);

        return response()->json($notifications);
    }

    // PATCH /api/notifications/{id}/lire
    // Marquer une notification comme lue
    public function marquerLue($id)
    {
        $user         = Auth::user();
        $notification = Notification::where('id_user', $user->id_user)->findOrFail($id);

        $notification->update(['est_lue' => true]);

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    // PATCH /api/notifications/lire-tout
    // Marquer toutes les notifications comme lues
    public function marquerToutLu()
    {
        $user = Auth::user();

        Notification::where('id_user', $user->id_user)
            ->where('est_lue', false)
            ->update(['est_lue' => true]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }

    // GET /api/notifications/compteur
    // Nombre de notifications non lues (pour le badge UI)
    public function compteur()
    {
        $user  = Auth::user();
        $count = Notification::where('id_user', $user->id_user)
            ->where('est_lue', false)
            ->count();

        return response()->json(['non_lues' => $count]);
    }
}