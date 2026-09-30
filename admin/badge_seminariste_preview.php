<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Aperçu Badge Séminariste (HTML/CSS)</title>
    <!-- Inclusion de votre feuille de style -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Polices -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
    </style>
</head>
<body>

    <div class="badge-carte-seminariste">
        <!-- La forme orange courbée -->
        <div class="badge-courbe-orange"></div>

        <!-- Le reste du contenu (z-index supérieur) -->
        <div class="badge-content">
            
            <!-- Logos et Jospia -->
            <div class="badge-header-logos">
                <!-- Remplacez ces images par vos vrais chemins -->
                <p style="color: #333; font-weight: 700; margin: 10px 0;">[ Espace pour Logos / Titre Jospia 2026 ]</p>
            </div>

            <!-- Photo -->
            <div class="badge-photo-container">
                <div class="badge-photo">
                    <img src="uploads/user_photo.jpg" alt="Photo Participant" onerror="this.src='https://via.placeholder.com/160/cccccc/ffffff?text=PHOTO'">
                </div>
            </div>

            <!-- Nom -->
            <div class="badge-nom">CHEICK OMER DIARRA</div>
            
            <!-- Séparateur décoratif -->
            <div class="badge-divider"></div>
            
            <!-- Détails -->
            <div class="badge-details">
                <div class="badge-details-row">
                    <div class="badge-details-label">DORTOIR :</div>
                    <div class="badge-details-value">Amir Mamadou Kone 2</div>
                </div>
                <div class="badge-details-row">
                    <div class="badge-details-label">NIVEAU :</div>
                    <div class="badge-details-value">Universitaire</div>
                </div>
                <div class="badge-details-row">
                    <div class="badge-details-label">MATRICULE :</div>
                    <div class="badge-details-value">JOS202614</div>
                </div>
            </div>

            <!-- Dates -->
            <div class="badge-dates">
                <div class="badge-date-box">
                    22
                    <span>DÉCEMBRE 2026</span>
                </div>
                <div class="badge-date-arrow">▶</div>
                <div class="badge-date-box">
                    28
                    <span>DÉCEMBRE 2026</span>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
