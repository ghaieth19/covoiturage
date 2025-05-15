<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer l'ID de la publication de la session
$publication_id = $_SESSION['publication_id'];

// Récupérer les informations de la publication
$stmt = $pdo->prepare("SELECT p.*, u.nom, u.prenom, u.telephone, u.photo_pdp, u.photo_voiture
                        FROM publications p
                        JOIN utilisateurs u ON p.utilisateur_cin = u.cin
                        WHERE p.id = ?");
$stmt->execute([$publication_id]);
$trajet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trajet) {
    $_SESSION['message'] = "❌ Trajet non trouvé.";
    $_SESSION['error'] = true;
    header("Location: recherche.php");
    exit();
}

// Prix initial
$prix = $trajet['prix_par_passager'];

// Vérifier la remise (si présente)
$remise = isset($_SESSION['user_points']) ? $_SESSION['user_points'] : 0; // Vous pouvez ajuster cette logique

// Appliquer la remise
$prix_final = $prix - ($prix * $remise / 100);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation Confirmée - Facture</title>
    <style>
        :root {
            --bg-color: #f8e05a;
            --text-color: #333;
            --card-bg: #fff;
            --highlight: #FFC107;
        }
        body.dark-mode {
            --bg-color: #121212;
            --text-color: #f1f1f1;
            --card-bg: #1e1e1e;
            --highlight: #bb86fc;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            transition: background-color 0.3s ease;
        }
        .container {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
            text-align: center;
        }
        h2 {
            margin-bottom: 30px;
            color: var(--highlight);
        }
        .facture {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            background-color: #f1f1f1;
        }
        .facture .ligne {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .facture .ligne strong {
            width: 150px;
            display: inline-block;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
        .option {
            display: block;
            background-color: var(--highlight);
            color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
            text-decoration: none;
            font-size: 18px;
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .option:hover {
            background-color: #e0b200;
            transform: scale(1.02);
        }
        .payer-maintenant {
            background-color: #28a745; /* Vert */
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 18px;
            margin-top: 20px;
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .payer-maintenant:hover {
            background-color: #218838; /* Vert foncé */
            transform: scale(1.05);
        }
        .dark-toggle {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: 2px solid var(--highlight);
            padding: 5px 10px;
            border-radius: 20px;
            cursor: pointer;
            color: var(--highlight);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Facture de Réservation</h2>

    <div class="facture">
        <div class="ligne"><strong>Nom du conducteur :</strong> <?= htmlspecialchars($trajet['prenom']) ?> <?= htmlspecialchars($trajet['nom']) ?></div>
        <div class="ligne"><strong>Départ :</strong> <?= htmlspecialchars($trajet['depart']) ?></div>
        <div class="ligne"><strong>Destination :</strong> <?= htmlspecialchars($trajet['destination']) ?></div>
        <div class="ligne"><strong>Date :</strong> <?= htmlspecialchars($trajet['date']) ?> à <?= htmlspecialchars($trajet['heure']) ?></div>
        <div class="ligne"><strong>Voiture :</strong> <?= htmlspecialchars($trajet['marque_voiture']) ?> (<?= htmlspecialchars($trajet['matricule_voiture']) ?>)</div>
        <div class="ligne"><strong>Prix initial :</strong> <?= number_format($prix, 2) ?> TND</div>
        <div class="ligne"><strong>Remise :</strong> <?= $remise ?>%</div>
        <div class="total">Total à payer : <?= number_format($prix_final, 2) ?> TND</div>
    </div>

    <!-- Bouton "Payer maintenant" -->
    <a href="paiement_en_ligne.php?type=trajet&prix=<?= $prix_final ?>" class="payer-maintenant">Payer maintenant</a>

    <button class="dark-toggle" onclick="toggleDarkMode()">🌙</button>
</div>

<script>
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    }

    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
</script>

</body>
</html>
