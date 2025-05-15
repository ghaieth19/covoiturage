<?php
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérifier le CIN
if (!isset($_GET['cin'])) {
    die("CIN non spécifié.");
}

$cin = $_GET['cin'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveauxPoints = (int)$_POST['points'];
    
    $sql = "UPDATE utilisateurs SET points = ? WHERE cin = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nouveauxPoints, $cin]);

    header("Location: objectif_admin.php"); // Retour au tableau de bord
    exit;
}

// Récupérer les points actuels
$sql = "SELECT nom, prenom, points FROM utilisateurs WHERE cin = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cin]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier les Points</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f2f2f2;
            padding: 40px;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        input[type=number] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #ffbb00;
            color: white;
            padding: 12px 20px;
            width: 100%;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>✏️ Modifier les Points de <?= htmlspecialchars($user['nom']) ?> <?= htmlspecialchars($user['prenom']) ?></h2>
    <form method="post">
        <label for="points">Nouveaux Points :</label>
        <input type="number" name="points" id="points" value="<?= $user['points'] ?>" required min="0">

        <button type="submit">Enregistrer</button>
    </form>
</div>
</body>
</html>
