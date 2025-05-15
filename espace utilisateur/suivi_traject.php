<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$successMessage = "";

// Récupérer les publications du conducteur connecté
$stmt = $pdo->prepare("
    SELECT p.*, u.email 
    FROM publications p
    JOIN utilisateurs u ON p.utilisateur_cin = u.cin
    WHERE u.email = :email
");
$stmt->execute(['email' => $_SESSION['email']]);
$publications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les points de trajet de l'utilisateur connecté
$stmtPoints = $pdo->prepare("
    SELECT SUM(r.status != 'annulée') * 30 AS points_traject 
    FROM reservations r
    JOIN publications p ON r.publication_id = p.id
    WHERE p.utilisateur_cin = (SELECT cin FROM utilisateurs WHERE email = :email)
");
$stmtPoints->execute(['email' => $_SESSION['email']]);
$points = $stmtPoints->fetch(PDO::FETCH_ASSOC);
$pointsTraject = $points['points_traject'] ?? 0;

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['valider'])) {
        $id = $_POST['publication_id'];

        // Ajouter 30 points aux passagers non annulés
        $stmt = $pdo->prepare("
            SELECT r.utilisateur_cin 
            FROM reservations r 
            WHERE r.publication_id = ? AND r.status != 'annulée'
        ");
        $stmt->execute([$id]);
        $passagers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($passagers as $passager) {
            $pdo->prepare("UPDATE utilisateurs SET points_traject = points_traject + 30 WHERE cin = ?")
                ->execute([$passager['utilisateur_cin']]);
        }

        $pdo->prepare("UPDATE publications SET status = 'terminé' WHERE id = ?")->execute([$id]);

        $successMessage = "✅ Trajet validé et 30 points attribués aux passagers.";
        header("Location: suivi_traject.php?success=" . urlencode($successMessage));
        exit();
    }

    if (isset($_POST['completer'])) {
        $id = $_POST['publication_id'];
        $pdo->prepare("UPDATE publications SET status = 'complet' WHERE id = ?")->execute([$id]);
        header("Location: suivi_traject.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des trajets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: rgb(255, 255, 255);
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #222;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        .card {
            background: #fafafa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        .passager {
            display: flex;
            align-items: center;
            margin-top: 10px;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .passager.annule {
            background-color: #ffe6e6;
        }

        .passager.actif {
            background-color: #eef;
        }

        .passager img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        button {
            background-color:rgb(0, 0, 0);
            border: none;
            color: white;
            padding: 10px 20px;
            margin: 10px 5px 0 0;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .places {
            font-weight: bold;
            color: green;
        }

        h3 {
            margin-bottom: 5px;
        }

        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f44336;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .progress-bar {
            width: 100%;
            background-color: #f3f3f3;
            height: 30px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .progress-bar span {
            display: block;
            height: 100%;
            background-color:rgb(24, 24, 24);
            border-radius: 5px;
        }
    </style>
</head>
<body>

<header>
    <h1>Suivi des trajets</h1>
    <a href="publication.php" style="color: white; text-decoration: none;">← Retour à la publication</a>
</header>

<div class="container">

    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <div class="progress-bar">
        <span style="width: <?= min(100, $pointsTraject / 1000 * 100) ?>%"></span>
    </div>
    <p><strong>Points actuels : </strong><?= $pointsTraject ?> pts</p>
    <p>Progression vers 1000 points</p>

    <?php foreach ($publications as $pub): ?>
        <?php
            $stmt = $pdo->prepare("
                SELECT r.*, u.nom, u.prenom, u.telephone, u.photo_pdp
                FROM reservations r
                JOIN utilisateurs u ON r.utilisateur_cin = u.cin
                WHERE r.publication_id = ?
            ");
            $stmt->execute([$pub['id']]);
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $placesReservees = 0;
            foreach ($reservations as $res) {
                if ($res['status'] !== 'annulée') {
                    $placesReservees += (int)$res['places'];
                }
            }

            $placesRestantes = (int)$pub['places'] - $placesReservees;
        ?>

        <div class="card">
            <h3><?= htmlspecialchars($pub['depart']) ?> ➜ <?= htmlspecialchars($pub['destination']) ?></h3>
            <p><strong>Date:</strong> <?= $pub['date'] ?> à <?= $pub['heure'] ?></p>
            <p><strong>Voiture:</strong> <?= $pub['marque_voiture'] ?></p>
            <p><strong>Prix:</strong> <?= $pub['prix_par_passager'] ?> TND</p>
            <p class="places">Places restantes : <?= $placesRestantes ?> / <?= $pub['places'] ?></p>

            <form method="get" action="modiferpublication.php">
                <input type="hidden" name="id" value="<?= $pub['id'] ?>">
                <button type="submit">Modifier</button>
            </form>

            <?php if ($reservations): ?>
                <h4>Passagers réservés :</h4>
                <?php foreach ($reservations as $res): ?>
                    <div class="passager <?= $res['status'] === 'annulée' ? 'annule' : 'actif' ?>">
                        <div>
                            <?= htmlspecialchars($res['prenom']) ?> <?= htmlspecialchars($res['nom']) ?>
                            <?php if ($res['status'] === 'annulée'): ?>
                                <span style="color: red;">(Trajet annulé)</span>
                            <?php endif; ?>
                            <br>📞 <?= htmlspecialchars($res['telephone']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune réservation pour l’instant.</p>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="publication_id" value="<?= $pub['id'] ?>">
                <?php if ($placesRestantes == 0): ?>
                    <button type="submit" name="completer">Marquer comme Complet</button>
                <?php endif; ?>
                <button type="submit" name="valider">Valider et attribuer 30 points</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
