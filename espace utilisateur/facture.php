<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['email']) || !isset($_SESSION['cin'])) {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$trajet = [];
$prix = 0;
$remise_percent = 0;
$montant_remise = 0;
$prix_final = 0;

// Récupération des informations du trajet
if (isset($_GET['id'])) {
    $publication_id = (int)$_GET['id'];

    // Récupérer les informations du trajet
    $stmt = $pdo->prepare("SELECT * FROM publications WHERE id = ?");
    $stmt->execute([$publication_id]);
    $trajet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trajet) {
        die("Erreur : Trajet non trouvé.");
    }

    $prix = (float) $trajet['prix_par_passager'];

    // Récupérer les points de l'utilisateur
    $stmt_user = $pdo->prepare("SELECT points_parrainage, points_traject FROM utilisateurs WHERE cin = ?");
    $stmt_user->execute([$_SESSION['cin']]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // Calculer les points totaux
    $points_totaux = $user ? (int)$user['points_parrainage'] + (int)$user['points_traject'] : 0;

    // Vérifier le nombre de remises déjà utilisées par l'utilisateur
    $stmt_remise = $pdo->prepare("SELECT COUNT(*) FROM factures WHERE cin_utilisateur = ? AND remise_appliquee = 1");
    $stmt_remise->execute([$_SESSION['cin']]);
    $nb_remises_utilisees = $stmt_remise->fetchColumn();

    // Appliquer la remise seulement si moins de 2 remises ont été utilisées
    if ($nb_remises_utilisees < 2) {
        if ($points_totaux >= 2000) {
            $remise_percent = 50;
        } elseif ($points_totaux >= 1000) {
            $remise_percent = 30;
        }
    }

    // Calculer la remise
    $montant_remise = $prix * ($remise_percent / 100);
    $prix_final = $prix - $montant_remise;
} else {
    die("ID du trajet manquant.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture de Réservation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(255, 255, 255);
            margin: 0;
            padding: 0;
        }
       header { background: #333; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; }
        header a { color: white; margin-left: 20px; text-decoration: none; font-weight: bold; }
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .facture-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .company-info, .user-info {
            width: 48%;
        }
        .facture-details, .facture-summary, .remises {
            margin: 20px 0;
        }
        .facture-summary, .remises div {
            font-size: 16px;
        }
        .remises {
            background-color: #e9f7ef;
            border: 1px solid #28a745;
            border-radius: 5px;
            padding: 10px;
            color: #155724;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            margin-right: 10px;
            font-size: 16px;
        }
        .btn-pay {
            background-color: #28a745;
        }
        .btn-back {
            background-color: #007bff;
        }
        .btn:hover {
            opacity: 0.9;
        }
        
        /* Styles du bouton Remise */
        .btn-remise {
            background-color: #ccc;
            cursor: not-allowed;
            pointer-events: none;
            opacity: 0.6;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-remise.active {
            background-color: #28a745;
            cursor: pointer;
            pointer-events: auto;
        }

        .btn-remise.active:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<!-- Header avec logo, accueil, recherche, publier, support, déconnexion -->
<header>
    <div><strong>Covoiturage TN</strong></div>
    <div>
        <a href="interface.php">Accueil</a>
        <a href="publication.php">Publier</a>
        <a href="contact.php">Support</a>
        <a href="couvoiturage.php">Déconnexion</a>
    </div>
</header>

<div class="container">
    <h1>Facture de Trajet</h1>

    <div class="facture-header">
        <div class="company-info">
            <h3>Covoiturage TN</h3>
            <p>Email: contact@covoiturage.com</p>
            <p>Téléphone: 123-456-789</p>
        </div>
        <div class="user-info">
            <h3>Utilisateur</h3>
            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email']) ?></p>
            <p><strong>CIN:</strong> <?= htmlspecialchars($_SESSION['cin']) ?></p>
        </div>
    </div>

    <div class="facture-details">
        <p><strong>Départ:</strong> <?= htmlspecialchars($trajet['depart']) ?></p>
        <p><strong>Destination:</strong> <?= htmlspecialchars($trajet['destination']) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($trajet['date']) ?> à <?= htmlspecialchars($trajet['heure']) ?></p>
        <p><strong>Voiture:</strong> <?= htmlspecialchars($trajet['marque_voiture']) ?> (<?= htmlspecialchars($trajet['matricule_voiture']) ?>)</p>
    </div>

    <div class="facture-summary">
        <p><strong>Prix initial:</strong> <?= number_format($prix, 2) ?> TND</p>
    </div>

    <?php if ($remise_percent > 0): ?>
    <div class="remises">
        <div><strong>Remise:</strong> <?= $remise_percent ?>%</div>
        <div><strong>Montant remise:</strong> -<?= number_format($montant_remise, 2) ?> TND</div>
        <div><strong>Prix final:</strong> <?= number_format($prix_final, 2) ?> TND</div>
    </div>
    <?php else: ?>
    <div class="remises">
        <div><strong>Aucune remise appliquée</strong></div>
        <div><strong>Prix total:</strong> <?= number_format($prix_final, 2) ?> TND</div>
    </div>
    <?php endif; ?>

    <!-- Bouton Remise -->
    <button class="btn-remise <?php echo $remise_percent > 0 ? 'active' : ''; ?>" 
            <?php echo $remise_percent > 0 ? '' : 'disabled'; ?>>
        Remise de <?= $remise_percent ?>%
    </button>

    <!-- Bouton Paiement -->
    <a class="btn btn-pay" href="paiement_en_ligne.php?id=<?= $publication_id ?>&prix=<?= $prix_final ?>">Payer maintenant</a>
    
    <a class="btn btn-back" href="recherche.php">Retour</a>
</div>

</body>
</html>
