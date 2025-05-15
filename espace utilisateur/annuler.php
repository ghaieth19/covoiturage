<?php
session_start();

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['email']) || !isset($_SESSION['cin'])) {
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

// Vérification de l'ID du trajet à annuler
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $publication_id = (int) $_GET['id'];
    $user_cin = $_SESSION['cin'];

    // Vérifier si la réservation existe et est active
    $check = $pdo->prepare("SELECT * FROM reservations WHERE utilisateur_cin = ? AND publication_id = ? AND status = 'active'");
    $check->execute([$user_cin, $publication_id]);

    if ($check->rowCount() > 0) {
        // Marquer la réservation comme annulée (sans suppression)
        $update_status = $pdo->prepare("UPDATE reservations SET status = 'annulée' WHERE utilisateur_cin = ? AND publication_id = ?");
        $update_status->execute([$user_cin, $publication_id]);

        // Incrémenter les places disponibles
        $update_places = $pdo->prepare("UPDATE publications SET places = places + 1 WHERE id = ?");
        $update_places->execute([$publication_id]);

        $_SESSION['message'] = "✅ Annulation effectuée.";
    } else {
        $_SESSION['message'] = "❌ Aucune réservation active trouvée.";
        $_SESSION['error'] = true;
    }
} else {
    $_SESSION['message'] = "❌ Trajet invalide.";
    $_SESSION['error'] = true;
}

// Redirection vers la page de recherche
header("Location: recherche.php");
exit();
