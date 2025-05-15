<?php
session_start();

if (!isset($_SESSION['cin'])) {
    echo "Veuillez vous connecter.";
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$cin = $_SESSION['cin'];

// Récupération des informations de l'utilisateur
$stmt = $pdo->prepare("SELECT nom, prenom, points_parrainage, points_traject FROM utilisateurs WHERE cin = ?");
$stmt->execute([$cin]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur introuvable.";
    exit();
}

$pointsParrainage = $user['points_parrainage'];
$pointsTrajets = $user['points_traject'];
$totalPoints = $pointsParrainage + $pointsTrajets;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi Fidélité</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffffff, #ffffff);
            margin: 0;
            padding: 0;
        }

        /* Header */
        header {
            background-color: #111;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        /* Container */
        .container {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 900px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .points-details {
            margin-bottom: 20px;
            text-align: left;
        }

        .points-details p {
            margin: 10px 0;
        }

        .progress-wrapper {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
        }

        .progress-circle {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: conic-gradient(#ffa500 var(--progress), #ddd 0%);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-weight: bold;
            transition: 0.5s ease;
        }

        .progress-circle span {
            font-size: 24px;
            color: #333;
        }

        .recompense {
            margin-top: 20px;
            padding: 15px;
            background: #e0ffe0;
            border-left: 5px solid #28a745;
            animation: fadeIn 0.6s ease forwards;
        }

        .btn-reserver {
            margin-top: 10px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-reserver:hover {
            background: #218838;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-10px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>

<header>
    <div class="logo">couvoiturage</div>
    <nav>
        <a href="interface.php">Accueil</a>
        <a href="publication.php">Publier</a>
        <a href="recherche.php">Recherche</a>
        <a href="couvoiturage.php">Déconnexion</a>
    </nav>
</header>

<div class="container">
    <h2>🎯 Bonjour <?= htmlspecialchars($user['prenom']) ?> <?= htmlspecialchars($user['nom']) ?> !</h2>

    <div class="points-details">
        <p><strong>Points Parrainage :</strong> <?= $pointsParrainage ?> pts</p>
        <p><strong>Points Trajets :</strong> <?= $pointsTrajets ?> pts</p>
        <p><strong>Total :</strong> <?= $totalPoints ?> pts</p>
    </div>

    <div class="progress-wrapper">
        <div>
            <div class="progress-circle" style="--progress: <?= min(100, ($totalPoints / 1000) * 100) ?>%">
                <span><?= min(100, round(($totalPoints / 1000) * 100)) ?>%</span>
            </div>
            <p>Objectif 1 : 1000 pts</p>
        </div>
        <div>
            <div class="progress-circle" style="--progress: <?= min(100, ($totalPoints / 2000) * 100) ?>%">
                <span><?= min(100, round(($totalPoints / 2000) * 100)) ?>%</span>
            </div>
            <p>Objectif 2 : 2000 pts</p>
        </div>
    </div>

    <?php if ($totalPoints >= 1000 && $totalPoints < 2000): ?>
        <div class="recompense" id="cheque1">
            🎁 Félicitations ! Vous avez atteint 1000 points. Vous bénéficiez de 30% de réduction sur un trajet aller-retour pendant 3 jours.
            <form method="post" action="reserver.php">
                <input type="hidden" name="remise" value="30">
                <button class="btn-reserver">Utiliser mon chèque</button>
            </form>
        </div>
    <?php elseif ($totalPoints >= 2000): ?>
        <div class="recompense" id="cheque2">
            🏆 Bravo ! 2000 points atteints ! Profitez de 50% de réduction sur un trajet aller-retour pendant 3 jours.
            <form method="post" action="reserver.php">
                <input type="hidden" name="remise" value="50">
                <button class="btn-reserver">Réserver maintenant</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
