<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8mb4", "root", "");
$parrains = [];

if (!empty($_GET['recherche'])) {
    $motcle = "%" . $_GET['recherche'] . "%";
    $stmt = $pdo->prepare("
        SELECT utilisateurs.*, 
               utilisateurs.points_parrainage,
               utilisateurs.points_traject
        FROM utilisateurs
        WHERE cin LIKE ? OR nom LIKE ? OR prenom LIKE ?
    ");
    $stmt->execute([$motcle, $motcle, $motcle]);
    $parrains = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔍 Recherche de Parrain</title>
    <style>
        /* Style simplifié pour l'exemple */
        body { font-family: Arial, sans-serif; background-color: #f7a616; margin: 0; padding: 40px 0; }
        .container { max-width: 950px; margin: auto; background-color: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); }
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background-color: #ffbb00; color: #000; }
        tr:hover { background-color: #f2f2f2; }
        .photo { border-radius: 50%; object-fit: cover; }
        .voir { padding: 7px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 6px; }
        .voir:hover { background-color: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h2>🔎 Rechercher un Parrain</h2>
    <form method="GET">
        <input type="text" name="recherche" placeholder="Nom, Prénom ou CIN..." value="<?= htmlspecialchars($_GET['recherche'] ?? '') ?>">
        <button type="submit">Rechercher</button>
    </form>

    <?php if (!empty($parrains)): ?>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom & Prénom</th>
                    <th>CIN</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Points Parrainage</th>
                    <th>Points Trajets</th>
                    <th>Total Points</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parrains as $p): ?>
                    <tr>
                        <td><img src="<?= $p['photo_pdp'] ?: 'default.jpg' ?>" width="50" height="50" class="photo"></td>
                        <td><?= htmlspecialchars($p['prenom']) ?> <?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= htmlspecialchars($p['cin']) ?></td>
                        <td><?= htmlspecialchars($p['email']) ?></td>
                        <td><?= htmlspecialchars($p['telephone']) ?></td>
                        <td><?= htmlspecialchars($p['points_parrainage']) ?> pts</td>
                        <td><?= htmlspecialchars($p['points_traject']) ?> pts</td>
                        <td><?= htmlspecialchars($p['points_parrainage'] + $p['points_traject']) ?> pts</td>
                        <td>
                            <a class="voir" href="details_parrain.php?cin=<?= $p['cin'] ?>">Voir </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($_GET['recherche'])): ?>
        <p>Aucun résultat trouvé.</p>
    <?php endif; ?>
</div>
</body>
</html>
