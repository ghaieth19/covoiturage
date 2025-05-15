<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$publication_id = $_GET['id'] ?? '';

// Vérifier si la publication existe
$stmt = $pdo->prepare("SELECT COUNT(*) FROM publications WHERE id = ?");
$stmt->execute([$publication_id]);
if ($stmt->fetchColumn() == 0) {
    $_SESSION['error'] = "❌ Publication introuvable.";
    header("Location: interface.php");
    exit();
}

// Ajouter un commentaire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_commentaire'])) {
    $commentaire = $_POST['commentaire'];
    $utilisateur_cin = $_SESSION['cin'];

    $stmt = $pdo->prepare("INSERT INTO commentaires (publication_id, utilisateur_cin, commentaire) VALUES (?, ?, ?)");
    $stmt->execute([$publication_id, $utilisateur_cin, $commentaire]);

    $_SESSION['message'] = "✅ Commentaire ajouté avec succès.";
    header("Location: commentaire.php?id=" . $publication_id);
    exit();
}

// Modifier un commentaire
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_commentaire'])) {
    $commentaire_id = $_POST['commentaire_id'];
    $nouveau_commentaire = $_POST['nouveau_commentaire'];

    $stmt = $pdo->prepare("UPDATE commentaires SET commentaire = ? WHERE id = ?");
    $stmt->execute([$nouveau_commentaire, $commentaire_id]);

    $_SESSION['message'] = "✅ Commentaire modifié avec succès.";
    header("Location: commentaire.php?id=" . $publication_id);
    exit();
}

// Supprimer un commentaire
if (isset($_GET['supprimer_commentaire'])) {
    $commentaire_id = $_GET['supprimer_commentaire'];

    $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
    $stmt->execute([$commentaire_id]);

    $_SESSION['message'] = "✅ Commentaire supprimé avec succès.";
    header("Location: commentaire.php?id=" . $publication_id);
    exit();
}

// Récupérer les commentaires de la publication
$stmt = $pdo->prepare("SELECT c.*, u.nom, u.prenom FROM commentaires c JOIN utilisateurs u ON c.utilisateur_cin = u.cin WHERE c.publication_id = ?");
$stmt->execute([$publication_id]);
$commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du signalement (il faut ajouter la logique pour enregistrer les signalements)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signaler_commentaire'])) {
    $signaled_comment_id = $_POST['signaled_comment_id'];
    $signalement_message = $_POST['signalement_message'];
    $signaleur_cin = $_SESSION['cin'] ?? null; // Récupérer le CIN depuis la session

    if ($signaleur_cin) {
        $stmt_signalement = $pdo->prepare("UPDATE commentaires
                                          SET est_signale = 1,
                                              motif_signalement = ?,
                                              signale_par_cin = ?
                                          WHERE id = ?");
        $stmt_signalement->execute([$signalement_message, $signaleur_cin, $signaled_comment_id]);

        if ($stmt_signalement->rowCount() > 0) {
            $_SESSION['message'] = "⚠️ Commentaire signalé. Nous examinerons votre rapport.";
        } else {
            $_SESSION['error'] = "❌ Erreur lors du signalement du commentaire.";
        }
    } else {
        $_SESSION['error'] = "❌ Vous devez être connecté pour signaler un commentaire.";
    }
    header("Location: commentaire.php?id=" . $publication_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commentaires</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        header {
            background-color: #000;
            color: white;
            padding: 20px 0;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            margin: 0 auto;
        }

        nav a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        h2, h3 {
            margin-bottom: 20px;
        }

        .input-textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .comment-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .comment-card {
            background: #f1f1f1;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            position: relative; /* Pour positionner le bouton signaler */
        }

        .edit-btn, .delete-btn, .signal-btn {
            padding: 8px 12px;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            border: none;
            margin-top: 5px;
            margin-right: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .edit-btn {
            background-color: #007bff;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .signal-btn {
            background-color: rgb(230, 130, 0); /* Jaune pour attirer l'attention */
            color: #333;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .edit-comment-form {
            display: none;
            margin-top: 10px;
        }

        .report-form {
            display: none;
            margin-top: 10px;
            padding: 15px;
            background-color: #eee;
            border-radius: 8px;
        }

        .report-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .report-form input[type="text"],
        .report-form textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .report-form button {
            background-color: #ff6347; /* Rouge orangé pour signaler */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .report-form button:hover {
            background-color: #e64b3a;
        }
    </style>
    <script>
        function showEditForm(commentaireId) {
            var form = document.getElementById('edit-form-' + commentaireId);
            form.style.display = 'block';
        }

        function showReportForm(commentaireId, reportedNom, reportedPrenom) {
            var form = document.getElementById('report-form-' + commentaireId);
            var nomInput = document.getElementById('reported_nom_' + commentaireId);
            var prenomInput = document.getElementById('reported_prenom_' + commentaireId);

            nomInput.value = reportedNom;
            prenomInput.value = reportedPrenom;
            form.style.display = 'block';
        }

        function validateReportForm(event, commentaireId) {
            const reporterNom = document.querySelector(`#report-form-${commentaireId} input[name="reporter_nom"]`).value.trim();
            const reporterPrenom = document.querySelector(`#report-form-${commentaireId} input[name="reporter_prenom"]`).value.trim();

            const nameRegex = /^[a-zA-Z\s]+$/; // Autorise les lettres et les espaces

            if (!nameRegex.test(reporterNom)) {
                alert("❌ Votre nom ne doit contenir que des caractères.");
                event.preventDefault(); // Empêche la soumission du formulaire
                return false;
            }

            if (!nameRegex.test(reporterPrenom)) {
                alert("❌ Votre prénom ne doit contenir que des caractères.");
                event.preventDefault(); // Empêche la soumission du formulaire
                return false;
            }

            return true; // Le formulaire est valide
        }
    </script>
</head>
<body>

<header>
    <div class="nav-container">
        <div><strong>Covoiturage TN</strong></div>
        <nav>
            <a href="interface.php">Accueil</a>
            <a href="publication.php">Publier</a>
            <a href="contact.php">Support</a>
            <a href="couvoiturage.php">Déconnexion</a>
        </nav>
    </div>
</header>

<div class="container">

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?= isset($_SESSION['error']) ? 'error' : 'success' ?>">
            <?= $_SESSION['message']; unset($_SESSION['message'], $_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <h2>Commentaires sur le trajet</h2>

    <form method="POST">
        <textarea name="commentaire" class="input-textarea" placeholder="Ajouter un commentaire..." required></textarea>
        <button type="submit" name="ajouter_commentaire" class="comment-btn">Ajouter</button>
    </form>

    <h3>Commentaires existants</h3>

    <?php if ($commentaires): ?>
        <?php foreach ($commentaires as $commentaire): ?>
            <div class="comment-card">
                <strong><?= htmlspecialchars($commentaire['prenom']) ?> <?= htmlspecialchars($commentaire['nom']) ?></strong>
                <p><?= htmlspecialchars($commentaire['commentaire']) ?></p>

                <?php if ($commentaire['utilisateur_cin'] == $_SESSION['cin']): ?>
                    <button onclick="showEditForm(<?= $commentaire['id'] ?>)" class="edit-btn">Modifier</button>

                    <div id="edit-form-<?= $commentaire['id'] ?>" class="edit-comment-form">
                        <form method="POST">
                            <input type="hidden" name="commentaire_id" value="<?= $commentaire['id'] ?>">
                            <textarea name="nouveau_commentaire" class="input-textarea" required><?= htmlspecialchars($commentaire['commentaire']) ?></textarea>
                            <button type="submit" name="modifier_commentaire" class="edit-btn">Modifier</button>
                        </form>
                    </div>

                    <a href="commentaire.php?id=<?= $publication_id ?>&supprimer_commentaire=<?= $commentaire['id'] ?>" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">Supprimer</a>
                <?php endif; ?>

                <button onclick="showReportForm(<?= $commentaire['id'] ?>, '<?= htmlspecialchars($commentaire['nom']) ?>', '<?= htmlspecialchars($commentaire['prenom']) ?>')" class="signal-btn">Signaler</button>

                <div id="report-form-<?= $commentaire['id'] ?>" class="report-form">
                    <h4>Signaler ce commentaire</h4>
                    <form method="POST" onsubmit="return validateReportForm(event, <?= $commentaire['id'] ?>);">
                        <input type="hidden" name="signaled_comment_id" value="<?= $commentaire['id'] ?>">
                        <label for="reporter_nom">Votre Nom:</label>
                        <input type="text" name="reporter_nom" value="<?= htmlspecialchars($_SESSION['nom'] ?? '') ?>" required>

                        <label for="reporter_prenom">Votre Prénom:</label>
                        <input type="text" name="reporter_prenom" value="<?= htmlspecialchars($_SESSION['prenom'] ?? '') ?>" required>

                        <label for="reported_nom_<?= $commentaire['id'] ?>">Nom de l'auteur du commentaire:</label>
                        <input type="text" id="reported_nom_<?= $commentaire['id'] ?>" name="reported_nom" value="<?= htmlspecialchars($commentaire['nom']) ?>" readonly>

                        <label for="reported_prenom_<?= $commentaire['id'] ?>">Prénom de l'auteur du commentaire:</label>
                        <input type="text" id="reported_prenom_<?= $commentaire['id'] ?>" name="reported_prenom" value="<?= htmlspecialchars($commentaire['prenom']) ?>" readonly>

                        <label for="signalement_message">Message de signalement:</label>
                        <textarea name="signalement_message" class="input-textarea" placeholder="Décrivez pourquoi vous signalez ce commentaire..." required></textarea>

                        <button type="submit" name="signaler_commentaire">Envoyer le signalement</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun commentaire pour ce trajet.</p>
    <?php endif; ?>
</div>

</body>
</html>
