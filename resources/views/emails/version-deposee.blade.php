<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background:#f0f2f5; margin:0; padding:20px; }
        .card { background:#fff; border-radius:12px; padding:32px; max-width:520px; margin:0 auto; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        .header { background:#1a6b3c; color:#fff; padding:20px 24px; border-radius:8px; margin-bottom:24px; }
        .header h2 { margin:0; font-size:1.1rem; }
        .info { background:#f8f9fa; border-radius:8px; padding:16px; margin:16px 0; }
        .info p { margin:6px 0; font-size:0.9rem; color:#333; }
        .badge { display:inline-block; background:#e3f2fd; color:#1565c0; padding:4px 12px; border-radius:20px; font-size:0.82rem; font-weight:600; }
        .footer { margin-top:24px; font-size:0.78rem; color:#aaa; text-align:center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2>📄 Nouveau dépôt de mémoire</h2>
        </div>
        <p>Bonjour,</p>
        <p>Un étudiant a déposé une nouvelle version de son mémoire sur la plateforme.</p>
        <div class="info">
            <p><strong>Étudiant :</strong> {{ $nomEtudiant }}</p>
            <p><strong>Mémoire :</strong> {{ $titreMemoire }}</p>
            <p><strong>Version :</strong> <span class="badge">Version {{ $numeroVersion }}</span></p>
        </div>
        <p>Connectez-vous à la plateforme pour consulter et traiter cette version.</p>
        <div class="footer">Plateforme de Supervision Académique — Notification automatique</div>
    </div>
</body>
</html>