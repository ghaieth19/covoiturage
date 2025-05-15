<?php
session_start();

// Connexion à la base de données
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

$successMessage = "";
$errorMessage = "";
$nom = $prenom = $email = "";
$cin = null;

// Récupération des infos utilisateur
if (isset($_SESSION['cin'])) {
    $cin = $_SESSION['cin'];

    $stmt = $pdo->prepare("SELECT nom, prenom, email FROM utilisateurs WHERE cin = ?");
    $stmt->execute([$cin]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nom = $user['nom'];
        $prenom = $user['prenom'];
        $email = $user['email'];
    }
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST" && $cin !== null) {
    $sujet = trim($_POST['sujet']);
    $message = trim($_POST['message']);

    if (!empty($sujet) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO message (cin, nom, prenom, email, sujet, contenu) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$cin, $nom, $prenom, $email, $sujet, $message])) {
            $successMessage = "Message envoyé avec succès.";
        } else {
            $errorMessage = "Erreur lors de l'envoi du message.";
        }
    } else {
        $errorMessage = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contacter l'administration</title>
    <style>
        /* Header style */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }

        header {
            background-color: #111;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color:rgb(255, 255, 255);
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        /* Contact form styles */
        .contact-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: 50px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group button {
            background-color: #f3ab25;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .form-group button:hover {
            background-color: #e59c12;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">couvoiturage</div>
    <nav>
        <a href="interface.php">Accueil</a>
        <a href="publication.php">Publier</a>
        <a href="recherche.php">Recherche</a>
   
        <a href="couvoiturage.php">Déconnexion</a>
    </nav>
</header>

<div class="contact-form">
    <h2>Contacter l'administration</h2>

    <?php if ($successMessage): ?>
        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
    <?php elseif ($errorMessage): ?>
        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if ($cin !== null): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Nom :</label>
                <input type="text" value="<?= htmlspecialchars($nom) ?>" disabled>
            </div>
            <div class="form-group">
                <label>Prénom :</label>
                <input type="text" value="<?= htmlspecialchars($prenom) ?>" disabled>
            </div>
            <div class="form-group">
                <label>Email :</label>
                <input type="text" value="<?= htmlspecialchars($email) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="sujet">Sujet :</label>
                <input type="text" id="sujet" name="sujet" required>
            </div>
            <div class="form-group">
                <label for="message">Message :</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit">Envoyer</button>
            </div>
        </form>
    <?php else: ?>
        <p class="message error">Veuillez vous connecter pour envoyer un message.</p>
    <?php endif; ?>
</div>

</body>
</html>
