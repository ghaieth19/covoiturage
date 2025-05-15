<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['cin'])) {
    echo "Vous devez être connecté pour accéder à cette page.";
    exit();
}

$cin = $_SESSION['cin']; // Le CIN de l'utilisateur connecté
$error = "";

// Connexion à la base de données
$pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8mb4", "root", "");

// Récupérer les informations de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE cin = ?");
$stmt->execute([$cin]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe
if (!$utilisateur) {
    $error = "Utilisateur introuvable.";
    exit();
}

// Récupérer les membres de l'équipe (utilisateurs ayant ce CIN comme parrain_cin)
$stmtEquipe = $pdo->prepare("SELECT * FROM utilisateurs WHERE parrain_cin = ?");
$stmtEquipe->execute([$cin]);
$equipe = $stmtEquipe->fetchAll(PDO::FETCH_ASSOC);

// Calculer le total des points (50 points par membre)
$totalPoints = count($equipe) * 50;

// Vérifier si des membres d'équipe existent
if (!$equipe) {
    $equipe = [];
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Équipe</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffbb00, #e0a800);
            margin: 0;
            padding: 30px;
        }

        .container {
            background: white;
            max-width: 900px;
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #ffbb00;
            color: white;
        }

        .formulaire input, .formulaire input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .formulaire button {
            background: #222;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .formulaire button:hover {
            background: #444;
        }

        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }

        .points {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .team-button {
            margin-top: 20px;
            text-align: center;
        }

        .error { color: red; }
    </style>
</head>
<body>

<div class="container">
    <h2>Mon Équipe</h2>

    <h3>Bienvenue, <?= htmlspecialchars($utilisateur['prenom']) ?> <?= htmlspecialchars($utilisateur['nom']) ?></h3>

    <?php if ($error): ?>
        <p class="error"><?= $error; ?></p>
    <?php endif; ?>

    <h3>Total des Points : <?= $totalPoints ?> points</h3>

    <h3>Membres de mon équipe :</h3>

    <?php if (count($equipe) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom et Prénom</th>
                    <th>CIN</th>
                    <th>Téléphone</th>
                    <th>Points</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipe as $membre): ?>
                    <tr>
                        <td>
                            <?php if ($membre['photo_pdp']): ?>
                                <img src="<?= htmlspecialchars($membre['photo_pdp']) ?>" alt="Photo de profil" width="50" height="50" style="border-radius: 50%;">
                            <?php else: ?>
                                <img src="default.jpg" alt="Photo par défaut" width="50" height="50" style="border-radius: 50%;">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($membre['prenom']) ?> <?= htmlspecialchars($membre['nom']) ?></td>
                        <td><?= htmlspecialchars($membre['cin']) ?></td>
                        <td><?= htmlspecialchars($membre['telephone']) ?></td>
                        <td>150</td> <!-- Points pour chaque membre -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun membre dans votre équipe.</p>
    <?php endif; ?>

    <div class="team-button">
        <button onclick="window.location.href='parrainage.php'">🔙 Parrainer un ami</button>
    </div>
</div>

</body>
</html>
