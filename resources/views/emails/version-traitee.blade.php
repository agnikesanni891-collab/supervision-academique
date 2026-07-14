<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background:#f0f2f5; margin:0; padding:20px; }
        .card { background:#fff; border-radius:12px; padding:32px; max-width:520px; margin:0 auto; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .header-ok { background:#1a6b3c; color:#fff; padding:20px 24px; border-radius:8px; margin-bottom:24px; }
        .header-ko { background:#c62828; color:#fff; padding:20px 24px; border-radius:8px; margin-bottom:24px; }
        .header-ok h2, .header-ko h2 { margin:0; font-size:1.1rem; }
        .info { background:#f8f9fa; border-radius:8px; padding:16px; margin:16px 0; }
        .info p { margin:6px 0; font-size:0.9rem; color:#333; }
        .badge-ok { display:inline-block; background:#e8f5e9; color:#1a6b3c; padding:4px 12px; border-radius:20px; font-size:0.82rem; font-weight:600; }
        .badge-ko { display:inline-block; background:#fdecea; color:#c62828; padding:4px 12px; border-radius:20px; font-size:0.82rem; font-weight:600; }
        .footer { margin-top:24px; font-size:0.78rem; color:#aaa; text-align:center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="{{ $statut === 'accepte' ? 'header-ok' : 'header-ko' }}">
            <h2>{{ $statut === 'accepte' ? '✅ Votre version a été acceptée' : '❌ Votre version a été rejetée' }}</h2>
        </div>
        <p>Bonjour,</p>
        @if($statut === 'accepte')
            <p>Bonne nouvelle ! Votre encadrant a <strong>accepté</strong> votre version.</p>
        @else
            <p>Votre encadrant a <strong>rejeté</strong> votre version. Consultez les annotations et soumettez une version corrigée.</p>
        @endif
        <div class="info">
            <p><strong>Encadrant :</strong> {{ $nomEncadrant }}</p>
            <p><strong>Mémoire :</strong> {{ $titreMemoire }}</p>
            <p><strong>Version :</strong>
                <span class="{{ $statut === 'accepte' ? 'badge-ok' : 'badge-ko' }}">
                    Version {{ $numeroVersion }} — {{ $statut === 'accepte' ? 'Acceptée' : 'Rejetée' }}
                </span>
            </p>
        </div>
        <p>Connectez-vous à la plateforme pour voir les détails.</p>
        <div class="footer">Plateforme de Supervision Académique — Notification automatique</div>
    </div>
</body>
</html>