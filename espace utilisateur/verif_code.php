<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8mb4", "root", "");
$verificationError = "";

// Vérifier si le code de vérification existe en session
if (!isset($_SESSION['verification_code'])) {
    header("Location: login.php"); // Si l'utilisateur n'est pas dans le processus de vérification, rediriger vers la connexion
    exit();
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codeSaisi = $_POST['code_2fa'] ?? '';

    // Vérifier si le code saisi est correct
    if ($codeSaisi == $_SESSION['verification_code']) {
        // Valider l'utilisateur
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("UPDATE utilisateurs SET is_verified = 1 WHERE id = ?");
        $stmt->execute([$userId]);

        // Récupérer les informations de l'utilisateur pour la session
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$userId]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['cin'] = $utilisateur['cin'];
        $_SESSION['email'] = $utilisateur['email'];
        
        // Supprimer le code de vérification de la session
        unset($_SESSION['verification_code']);
        unset($_SESSION['user_id']);
        
        header("Location: interface.php");
        exit();
    } else {
        $verificationError = "Le code de vérification est incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification 2FA</title>
</head>
<body>
    <h2>Entrez le code de vérification</h2>
    <form method="POST">
        <label for="code_2fa">Code de vérification :</label>
        <input type="text" id="code_2fa" name="code_2fa" required>
        <button type="submit">Valider</button>
    </form>
    <?php if (isset($verificationError)): ?>
        <div><?= htmlspecialchars($verificationError) ?></div>
    <?php endif; ?>
</body>
</html>
