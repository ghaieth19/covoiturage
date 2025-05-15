<?php
$host = "localhost";
$dbname = "covoiturage";
$username = "root";
$password = "";

$message = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'supprimer' && isset($_POST['id'])) {
            $stmt = $conn->prepare("DELETE FROM publications WHERE id = :id");
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->execute();
            $message = "Publication supprimée avec succès.";
        }

        if ($action === 'ajouter') {
            $stmt = $conn->prepare("INSERT INTO publications (utilisateur_cin, depart, destination, heure, date, places, marque_voiture, matricule_voiture, prix_par_passager, status, date_publication)
                VALUES (:cin, :depart, :destination, :heure, :date, :places, :marque, :matricule, :prix, 'en attente', NOW())");
            $stmt->execute([
                ':cin' => $_POST['cin'],
                ':depart' => $_POST['depart'],
                ':destination' => $_POST['destination'],
                ':heure' => $_POST['heure'],
                ':date' => $_POST['date'],
                ':places' => $_POST['places'],
                ':marque' => $_POST['marque_voiture'],
                ':matricule' => $_POST['matricule_voiture'],
                ':prix' => $_POST['prix_par_passager']
            ]);
            $message = "Publication ajoutée avec succès.";
        }

        if ($action === 'modifier') {
            $stmt = $conn->prepare("UPDATE publications SET depart = :depart, destination = :destination, heure = :heure, date = :date, places = :places, marque_voiture = :marque, matricule_voiture = :matricule, prix_par_passager = :prix WHERE id = :id");
            $stmt->execute([
                ':depart' => $_POST['depart'],
                ':destination' => $_POST['destination'],
                ':heure' => $_POST['heure'],
                ':date' => $_POST['date'],
                ':places' => $_POST['places'],
                ':marque' => $_POST['marque_voiture'],
                ':matricule' => $_POST['matricule_voiture'],
                ':prix' => $_POST['prix_par_passager'],
                ':id' => $_POST['id']
            ]);
            $message = "Publication modifiée avec succès.";
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
        exit();
    }

    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message']);
    }

    $stmt = $conn->query("SELECT p.*, u.nom, u.prenom, u.telephone, u.photo_pdp
                          FROM publications p 
                          JOIN utilisateurs u ON p.utilisateur_cin = u.cin 
                          WHERE p.status = 'en attente' 
                          ORDER BY p.date_publication DESC");
    $publications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Publications</title>
    <style>
        body {
            font-family: Arial;
            background: rgb(245, 188, 0);
            padding: 20px;
        }
        .form-section, .publication {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px #ccc;
        }
        h2 {
            margin-top: 0;
        }
        input, select {
            margin: 5px 0;
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-ajouter { background: #28a745; color: white; }
        .btn-supprimer { background: #dc3545; color: white; }
        .btn-modifier { background: #ffc107; color: black; }
        .btn-enregistrer { background: #007bff; color: white; }
        .actions form { display: inline; }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-inline input { width: auto; margin-right: 10px; }

        /* Photo de profil */
        .profile {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid #ccc;
        }
    </style>
</head>
<body>

<?php if (!empty($message)) : ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<div class="form-section">
    <h2>Ajouter une publication</h2>
    <form method="post">
        <input type="hidden" name="action" value="ajouter">
        <input type="text" name="cin" placeholder="CIN utilisateur" required>
        <input type="text" name="depart" placeholder="Départ" required>
        <input type="text" name="destination" placeholder="Destination" required>
        <input type="time" name="heure" required>
        <input type="date" name="date" required>
        <input type="number" name="places" placeholder="Places" required>
        <input type="text" name="marque_voiture" placeholder="Marque voiture" required>
        <input type="text" name="matricule_voiture" placeholder="Matricule voiture" required>
        <input type="number" name="prix_par_passager" placeholder="Prix par passager" required>
        <button type="submit" class="btn btn-ajouter">Ajouter</button>
    </form>
</div>

<?php foreach ($publications as $pub): ?>
    <div class="publication">
        <div class="profile">
            <img src="<?= htmlspecialchars($pub['photo_pdp']) ?>" alt="Photo de profil">
            <h3><?= htmlspecialchars($pub['prenom'] . ' ' . $pub['nom']) ?></h3>
        </div>
        <p><strong>Départ :</strong> <?= htmlspecialchars($pub['depart']) ?></p>
        <p><strong>Destination :</strong> <?= htmlspecialchars($pub['destination']) ?></p>
        <p><strong>Date :</strong> <?= htmlspecialchars($pub['date']) ?> à <?= htmlspecialchars($pub['heure']) ?></p>
        <p><strong>Places :</strong> <?= htmlspecialchars($pub['places']) ?></p>
        <p><strong>Voiture :</strong> <?= htmlspecialchars($pub['marque_voiture']) ?> (<?= htmlspecialchars($pub['matricule_voiture']) ?>)</p>
        <p><strong>Prix :</strong> <?= htmlspecialchars($pub['prix_par_passager']) ?> DT</p>
        <p><strong>Téléphone :</strong> <?= htmlspecialchars($pub['telephone']) ?></p>

        <div class="actions">
            <form method="post" onsubmit="return confirm('Confirmer la suppression ?');">
                <input type="hidden" name="id" value="<?= $pub['id'] ?>">
                <input type="hidden" name="action" value="supprimer">
                <button type="submit" class="btn btn-supprimer">Supprimer</button>
            </form>
            <button class="btn btn-modifier" onclick="document.getElementById('modif-<?= $pub['id'] ?>').style.display='block'">Modifier</button>
        </div>

        <form method="post" id="modif-<?= $pub['id'] ?>" style="display:none; margin-top:15px;" class="form-inline">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id" value="<?= $pub['id'] ?>">
            <input type="text" name="depart" value="<?= htmlspecialchars($pub['depart']) ?>" required>
            <input type="text" name="destination" value="<?= htmlspecialchars($pub['destination']) ?>" required>
            <input type="time" name="heure" value="<?= htmlspecialchars($pub['heure']) ?>" required>
            <input type="date" name="date" value="<?= htmlspecialchars($pub['date']) ?>" required>
            <input type="number" name="places" value="<?= htmlspecialchars($pub['places']) ?>" required>
            <input type="text" name="marque_voiture" value="<?= htmlspecialchars($pub['marque_voiture']) ?>" required>
            <input type="text" name="matricule_voiture" value="<?= htmlspecialchars($pub['matricule_voiture']) ?>" required>
            <input type="number" name="prix_par_passager" value="<?= htmlspecialchars($pub['prix_par_passager']) ?>" required>
            <button type="submit" class="btn btn-enregistrer">Enregistrer</button>
        </form>
    </div>
<?php endforeach; ?>

</body>
</html>
