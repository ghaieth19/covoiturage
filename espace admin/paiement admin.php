<?php
session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer toutes les factures
$stmt = $pdo->prepare("SELECT * FROM factures");
$stmt->execute();
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Supprimer une facture
if (isset($_GET['supprimer'])) {
    $facture_id = (int)$_GET['supprimer'];

    // Supprimer la facture de la base de données
    $stmt_delete = $pdo->prepare("DELETE FROM factures WHERE id = ?");
    $stmt_delete->execute([$facture_id]);

    header("Location: admin_factures.php"); // Rediriger après suppression
    exit();
}

// Modifier une facture
if (isset($_GET['modifier'])) {
    $facture_id = (int)$_GET['modifier'];

    // Récupérer les données de la facture à modifier
    $stmt_edit = $pdo->prepare("SELECT * FROM factures WHERE id = ?");
    $stmt_edit->execute([$facture_id]);
    $facture = $stmt_edit->fetch(PDO::FETCH_ASSOC);

    // Si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nouveau_montant = $_POST['montant'];
        $nouvelle_remise = $_POST['remise'];

        // Mettre à jour la facture
        $stmt_update = $pdo->prepare("UPDATE factures SET montant = ?, remise_appliquee = ? WHERE id = ?");
        $stmt_update->execute([$nouveau_montant, $nouvelle_remise, $facture_id]);

        header("Location: admin_factures.php"); // Rediriger après modification
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Factures - Admin</title>
    <style>
        /* Styles similaires à la page utilisateur */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5c60a;
            margin: 0;
            padding: 0;
        }
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
        .facture-summary {
            margin: 20px 0;
            font-size: 16px;
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
        .btn-modifier {
            background-color: #ffc107;
        }
        .btn-supprimer {
            background-color: #dc3545;
        }
        .btn:hover {
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f5c60a;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Gestion des Factures</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>CIN Utilisateur</th>
                <th>Montant</th>
                <th>Remise Appliquée</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($factures as $facture): ?>
                <tr>
                    <td><?= $facture['id'] ?></td>
                    <td><?= htmlspecialchars($facture['cin_utilisateur']) ?></td>
                    <td><?= number_format($facture['montant'], 2) ?> TND</td>
                    <td><?= $facture['remise_appliquee'] ? 'Oui' : 'Non' ?></td>
                    <td>
                        <a class="btn btn-modifier" href="admin_factures.php?modifier=<?= $facture['id'] ?>">Modifier</a>
                        <a class="btn btn-supprimer" href="admin_factures.php?supprimer=<?= $facture['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulaire de modification de facture (si nécessaire) -->
    <?php if (isset($facture)): ?>
    <div class="container">
        <h2>Modifier la Facture ID: <?= $facture['id'] ?></h2>
        <form method="POST">
            <div>
                <label for="montant">Montant:</label>
                <input type="number" name="montant" value="<?= $facture['montant'] ?>" required>
            </div>
            <div>
                <label for="remise">Remise Appliquée:</label>
                <select name="remise" required>
                    <option value="1" <?= $facture['remise_appliquee'] == 1 ? 'selected' : '' ?>>Oui</option>
                    <option value="0" <?= $facture['remise_appliquee'] == 0 ? 'selected' : '' ?>>Non</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-modifier">Mettre à jour</button>
                <a class="btn btn-back" href="admin_factures.php">Retour</a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
