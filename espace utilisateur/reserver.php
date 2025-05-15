<?php
session_start();

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['email']) || !isset($_SESSION['cin'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $publication_id = (int)$_GET['id'];
    $user_cin = $_SESSION['cin'];

    // Vérifier si une réservation existe déjà
    $check = $pdo->prepare("SELECT * FROM reservations WHERE utilisateur_cin = ? AND publication_id = ?");
    $check->execute([$user_cin, $publication_id]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing && $existing['status'] === 'active') {
        $_SESSION['message'] = "❌ Vous avez déjà réservé ce trajet.";
        $_SESSION['error'] = true;
    } else {
        // Vérifier les places disponibles
        $places_stmt = $pdo->prepare("SELECT places FROM publications WHERE id = ?");
        $places_stmt->execute([$publication_id]);
        $places = $places_stmt->fetchColumn();

        if ($places > 0) {
            if ($existing && $existing['status'] === 'annulée') {
                // Réactiver la réservation
                $reactivate = $pdo->prepare("UPDATE reservations SET status = 'active' WHERE utilisateur_cin = ? AND publication_id = ?");
                $reactivate->execute([$user_cin, $publication_id]);
            } else {
                // Nouvelle réservation
                $reserve = $pdo->prepare("INSERT INTO reservations (utilisateur_cin, publication_id, places, status) VALUES (?, ?, 1, 'active')");
                $reserve->execute([$user_cin, $publication_id]);
            }

            // Décrémenter les places
            $update_places = $pdo->prepare("UPDATE publications SET places = places - 1 WHERE id = ?");
            $update_places->execute([$publication_id]);

            // Ajouter 30 points dans `points_traject`
            $add_points = $pdo->prepare("UPDATE utilisateurs SET points_traject = points_traject + 30 WHERE cin = ?");
            $add_points->execute([$user_cin]);

            $_SESSION['message'] = "✅ Réservation confirmée.";
        } else {
            $_SESSION['message'] = "❌ Désolé, ce trajet est complet.";
            $_SESSION['error'] = true;
        }
    }

    header("Location: recherche.php");
    exit();
} else {
    $_SESSION['message'] = "❌ Trajet introuvable.";
    $_SESSION['error'] = true;
    header("Location: recherche.php");
    exit();
}
?>
