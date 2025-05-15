<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$recherche = $_GET['recherche'] ?? '';
$user_cin = $_SESSION['cin'] ?? '';

// Requête de recherche
$query = "
    SELECT p.*, u.nom, u.prenom, u.telephone, u.photo_pdp, u.photo_voiture,
    (SELECT COUNT(*) FROM reservations WHERE publication_id = p.id) AS reservations_count
    FROM publications p
    JOIN utilisateurs u ON p.utilisateur_cin = u.cin
    WHERE p.depart LIKE :rech OR p.destination LIKE :rech
";
$stmt = $pdo->prepare($query);
$stmt->execute(['rech' => "%$recherche%"]);
$publications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les trajets réservés
$res_stmt = $pdo->prepare("SELECT publication_id FROM reservations WHERE utilisateur_cin = ?");
$res_stmt->execute([$user_cin]);
$reserved_ids = $res_stmt->fetchAll(PDO::FETCH_COLUMN);

// Traitement du signalement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signaler'])) {
    $publication_id = $_POST['publication_id'];
    $stmt = $pdo->prepare("INSERT INTO signalements (publication_id, utilisateur_cin) VALUES (?, ?)");
    $stmt->execute([$publication_id, $user_cin]);

    $_SESSION['message'] = "✅ Publication signalée merci pour votre remarque.";
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de trajets</title>
    <style>
        body { font-family: Arial; background-color: #ffcb05; margin: 0; padding: 0; }
        header { background: #333; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; }
        header a { color: white; margin-left: 20px; text-decoration: none; font-weight: bold; }
        .container { max-width: 1000px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 10px; }
        .card { background: #f9f9f9; padding: 20px; margin-bottom: 20px; border-radius: 8px; display: flex; gap: 20px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .user-img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; }
        .info { flex: 1; }
        .reserve-button, .cancel-button, .disabled-button, .invoice-button, .report-button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }
        .reserve-button { background-color: #28a745; color: white; }
        .cancel-button { background-color: #dc3545; color: white; }
        .disabled-button { background-color: #ccc; color: #666; pointer-events: none; cursor: not-allowed; }
        .invoice-button { background-color: #f2b600; color: white; }
        .report-button { background-color: #ff7f00; color: white; }
        .report-button:hover { background-color: #e67e00; }
        .message { text-align: center; font-weight: bold; margin-bottom: 20px; }
        .message.success { color: green; }
        .message.error { color: red; }
    </style>
</head>
<body>

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

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?= isset($_SESSION['error']) ? 'error' : 'success' ?>">
            <?= $_SESSION['message']; unset($_SESSION['message'], $_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="GET" style="text-align:center; margin-bottom: 30px;">
        <input type="text" name="recherche" placeholder="Lieu..." value="<?= htmlspecialchars($recherche) ?>" style="padding:10px; width:300px;">
        <button type="submit" style="padding:10px 15px;">Rechercher</button>
    </form>

    <?php if ($publications): ?>
        <?php foreach ($publications as $pub): ?>
            <div class="card">
                <img src="../uploads/<?= $pub['photo_pdp'] ?: 'default-profile.jpg' ?>" class="user-img" alt="Photo">
                <div class="info">
                    <strong><?= htmlspecialchars($pub['prenom']) ?> <?= htmlspecialchars($pub['nom']) ?></strong><br>
                    📞 <?= htmlspecialchars($pub['telephone']) ?><br>
                    🛫 <strong>Départ:</strong> <?= htmlspecialchars($pub['depart']) ?><br>
                    🛬 <strong>Destination:</strong> <?= htmlspecialchars($pub['destination']) ?><br>
                    📅 <strong>Date:</strong> <?= htmlspecialchars($pub['date']) ?> à <?= htmlspecialchars($pub['heure']) ?><br>
                    🚗 <strong>Voiture:</strong> <?= htmlspecialchars($pub['marque_voiture']) ?> (<?= htmlspecialchars($pub['matricule_voiture']) ?>)<br>
                    💺 <strong>Places restantes:</strong> <?= $pub['places'] ?><br>
                    💰 <strong>Prix:</strong> <?= $pub['prix_par_passager'] ?> TND<br><br>

                    <?php if (in_array($pub['id'], $reserved_ids)): ?>
                        <a href="annuler.php?id=<?= $pub['id'] ?>" class="cancel-button">Annuler</a>
                        <a href="facture.php?id=<?= $pub['id'] ?>" class="invoice-button">Voir la facture</a>
                        <form method="POST" action="" style="display: inline-block;">
                            <input type="hidden" name="publication_id" value="<?= $pub['id'] ?>">
                            <button type="submit" name="signaler" class="report-button">Signaler</button>
                        </form>
                    <?php elseif ($pub['places'] > 0): ?>
                        <a href="reserver.php?id=<?= $pub['id'] ?>" class="reserve-button">Réserver</a>
                    <?php else: ?>
                        <span class="disabled-button">Complet</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="message error">Aucun trajet trouvé.</div>
    <?php endif; ?>

</div>

</body>
</html>
