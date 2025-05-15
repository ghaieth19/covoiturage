<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8mb4", "root", "");

// Vérifier si un CIN a été passé
if (isset($_GET['cin'])) {
    $cin_parrain = $_GET['cin'];

    // Récupérer le parrain (si nécessaire)
    $stmt_parrain = $pdo->prepare("SELECT * FROM utilisateurs WHERE cin = ?");
    $stmt_parrain->execute([$cin_parrain]);
    $parrain = $stmt_parrain->fetch(PDO::FETCH_ASSOC);

    // Récupérer les filleuls du parrain
    $stmt_filleuls = $pdo->prepare("SELECT * FROM utilisateurs WHERE parrain_cin = ?");
    $stmt_filleuls->execute([$cin_parrain]);
    $filleuls = $stmt_filleuls->fetchAll(PDO::FETCH_ASSOC);

    // Calculer les points totaux de parrainage et de trajets
    $total_points_parrainage = 0;
    $total_points_traject = 0;
    foreach ($filleuls as $filleul) {
        $total_points_parrainage += $filleul['points_parrainage'];
        $total_points_traject += $filleul['points_traject'];
    }
} else {
    echo "CIN du parrain manquant.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Parrain</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f7a616; margin: 0; padding: 40px 0; }
        .container { max-width: 950px; margin: auto; background-color: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); }
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background-color: #ffbb00; color: #000; }
        tr:hover { background-color: #f2f2f2; }
        .photo { border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body>
<div class="container">
    <h2>Détails du Parrain <?= htmlspecialchars($parrain['prenom']) ?> <?= htmlspecialchars($parrain['nom']) ?></h2>
    


    <h3>Liste des Filleuls :</h3>
    <table>
        <thead>
            <tr>
                <th>Nom & Prénom</th>
                <th>CIN</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Total Points</th> <!-- Colonne pour afficher la somme des points -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filleuls as $f): ?>
                <?php
                // Calculer le total des points pour chaque filleul
                $total_points_filleul = $f['points_parrainage'] + $f['points_traject'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($f['prenom']) ?> <?= htmlspecialchars($f['nom']) ?></td>
                    <td><?= htmlspecialchars($f['cin']) ?></td>
                    <td><?= htmlspecialchars($f['email']) ?></td>
                    <td><?= htmlspecialchars($f['telephone']) ?></td>
                    <td><?= $total_points_filleul ?> pts</td> <!-- Afficher la somme des points -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
