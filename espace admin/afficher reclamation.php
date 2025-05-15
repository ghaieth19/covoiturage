<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Informations de connexion à la base de données
$host = "localhost";
$dbname = "covoiturage";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire de réponse et suppression
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Répondre à un message
    if (isset($_POST['id'], $_POST['reponse'])) {
        $message_id = intval($_POST['id']);
        $reponse = trim($_POST['reponse']);

        if (!empty($reponse)) {
            $stmtUser = $pdo->prepare("SELECT u.email, u.nom, u.prenom FROM message m JOIN utilisateurs u ON m.cin = u.cin WHERE m.id = ?");
            $stmtUser->execute([$message_id]);
            $utilisateur = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur) {
                $email = $utilisateur['email'];
                $nom = $utilisateur['nom'];
                $prenom = $utilisateur['prenom'];

                // Mise à jour de la réponse dans la base de données
                $stmt = $pdo->prepare("UPDATE message SET reponse = ?, date_reponse = NOW() WHERE id = ?");
                $stmt->execute([$reponse, $message_id]);

                // Contenu de l'email
                $sujet = "couvoiturage TN";
                $contenu = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .email-header { text-align: center; margin-bottom: 20px; }
                            .email-body { font-size: 16px; color: #333; }
                            .logo { width: 150px; margin-top: 10px; }
                            .footer { margin-top: 30px; text-align: center; font-size: 14px; color: #777; }
                        </style>
                    </head>
                    <body>
                        <div class='email-header'>
                            <h2>Bonjour $prenom $nom,</h2>
                            <p>Voici la réponse à votre message :</p>
                            <img src='cid:logo' alt='Logo Covoiturage Tunisie' class='logo'>
                        </div>
                        <div class='email-body'>
                            <p>$reponse</p>
                        </div>
                        <div class='footer'>
                            <p>Merci pour votre confiance.<br>Covoiturage Tunisie</p>
                        </div>
                    </body>
                    </html>
                ";

                // Envoi de l'email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'ghaiethbouamor23@gmail.com'; // Remplacer par votre adresse email
                    $mail->Password = 'mykg xsrq rcdv fymm'; // Remplacer par votre mot de passe
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('ghaiethbouamor23@gmail.com', 'Covoiturage Tunisie');
                    $mail->addAddress($email, "$prenom $nom");
                    $mail->Subject = $sujet;
                    $mail->isHTML(true);
                    $mail->Body    = $contenu;

                    // Ajouter l'image du logo
                    $mail->addEmbeddedImage('photo1.png', 'logo');

                    $mail->send();
                    $success = "Réponse envoyée par email à $email.";
                } catch (Exception $e) {
                    $error = "L'envoi a échoué : " . $mail->ErrorInfo;
                }
            }
        } else {
            $error = "Veuillez saisir une réponse.";
        }
    }
    // Suppression d'un message
    elseif (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM message WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success = "Message supprimé avec succès.";
    }
}

// Recherche et filtrage des messages
$search = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : null;
$statut = $_GET['statut'] ?? null;

$sql = "
    SELECT m.id, m.sujet, m.contenu, m.date_envoi, m.reponse, m.date_reponse,
           u.nom, u.prenom, u.email
    FROM message m
    JOIN utilisateurs u ON m.cin = u.cin
    WHERE 1
";

$params = [];

if ($search) {
    $sql .= " AND (
        u.nom LIKE :search OR
        u.prenom LIKE :search OR
        m.sujet LIKE :search OR
        m.contenu LIKE :search
    )";
    $params['search'] = $search;
}

if ($statut === "repondu") {
    $sql .= " AND m.reponse IS NOT NULL";
} elseif ($statut === "nonrepondu") {
    $sql .= " AND m.reponse IS NULL";
}

$sql .= " ORDER BY m.date_envoi DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réponses aux messages</title>
    <style>
        :root {
    --bg-light: #f5f7fa;
    --text-light: #2c3e50;
    --bg-dark: #1e1e1e;
    --text-dark: #ecf0f1;
    --primary:rgb(219, 197, 52);
    --danger: #e74c3c;
    --success: #2ecc71;
    --warning: #f1c40f;
}

* {
    transition: all 0.3s ease-in-out;
    box-sizing: border-box;
}

body {
    background: url('background1.jpg') no-repeat center center fixed;

    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    color: var(--text-light);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    padding-top: 40px;
}

body.dark {
    background: var(--bg-dark);
    color: var(--text-dark);
}

.container {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    padding: 30px;
    width: 90%;
    max-width: 950px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    animation: fadeIn 0.8s ease-out;
}

body.dark .container {
    background: #2c2c2c;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

.message {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    animation: slideUp 0.4s ease;
}

body.dark .message {
    background: #333;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

input[type="text"], textarea, select {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
}

textarea {
    resize: vertical;
    min-height: 70px;
}

button {
    background: var(--primary);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    transition: background 0.3s ease;
}

button:hover {
    background:rgb(251, 210, 5);
}

.delete-btn {
    background: var(--danger);
    margin-top: 10px;
}

.delete-btn:hover {
    background: #c0392b;
}

.toggle-dark {
    font-size: 22px;
    cursor: pointer;
    float: right;
    margin-top: -10px;
    user-select: none;
}

.success, .error {
    padding: 12px 20px;
    margin: 15px 0;
    border-radius: 10px;
    font-weight: bold;
}

.success {
    background-color: var(--success);
    color: white;
}

.error {
    background-color: var(--danger);
    color: white;
}

.recherche {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    align-items: center;
}

.recherche input[type="text"], .recherche select {
    flex: 1;
    min-width: 150px;
}

.reponse-envoyee {
    background-color: #ecf0f1;
    padding: 10px;
    margin-top: 10px;
    border-left: 5px solid var(--primary);
    border-radius: 6px;
}

body.dark .reponse-envoyee {
    background-color: #444;
}

    </style>
</head>
<body>
<div class="container">
    <span class="toggle-dark" onclick="document.body.classList.toggle('dark')">🌓</span>
    <h2>Messages des utilisateurs</h2>

    <?php if (isset($success)) echo "<div class='success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="get" class="recherche">
        <input type="text" name="q" placeholder="Recherche..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
        <select name="statut">
            <option value="">Tous</option>
            <option value="repondu" <?= (isset($_GET['statut']) && $_GET['statut'] === 'repondu') ? 'selected' : '' ?>>Répondu</option>
            <option value="nonrepondu" <?= (isset($_GET['statut']) && $_GET['statut'] === 'nonrepondu') ? 'selected' : '' ?>>Non répondu</option>
        </select>
        <button type="submit">Rechercher</button>
    </form>

    <?php foreach ($messages as $msg): ?>
        <div class="message">
            <h3><?= htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']) ?> (<?= $msg['email'] ?>)</h3>
            <p><strong>Sujet :</strong> <?= htmlspecialchars($msg['sujet']) ?></p>
            <p><strong>Message :</strong><br><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
            <p><strong>Date :</strong> <?= $msg['date_envoi'] ?></p>

            <?php if ($msg['reponse']): ?>
                <div class="reponse-envoyee">
                    <strong>Réponse :</strong><br>
                    <?= nl2br(htmlspecialchars($msg['reponse'])) ?><br>
                    <em>Le <?= $msg['date_reponse'] ?></em>
                </div>
            <?php else: ?>
                <form method="post">
                    <textarea name="reponse" placeholder="Votre réponse..."></textarea>
                    <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                    <button type="submit">Répondre</button>
                </form>
            <?php endif; ?>
            <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                <input type="hidden" name="delete_id" value="<?= $msg['id'] ?>">
                <button type="submit" class="delete-btn">Supprimer</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
