<?php
require 'vendor/autoload.php'; // SDK Stripe
\Stripe\Stripe::setApiKey('sk_test_51RK0KIPvBY8IYmBBYemsdroZfYIhog8ZpgUP76jh8eF1T8kVkuyEDrVF8MovV4M0vrpMIdB5qT73yDTBArDumdP800cGeMUIeV'); // Ta clé secrète

session_start();

// Vérification si l'ID de session est fourni
if (!isset($_GET['session_id'])) {
    echo "ID de session non fourni.";
    exit;
}

try {
    // Récupérer la session Stripe
    $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);

    // Vérification du statut du paiement
    if ($session->payment_status === 'paid') {
        // Paiement effectué avec succès
        echo "<h1 style='color: green; text-align:center;'>✅ Paiement effectué avec succès !</h1>";
        echo "<p style='text-align:center;'>Merci pour votre achat. Bon voyage !</p>";

        // Récupérer les détails de la session
        $user_email = $session->customer_email;
        $amount_paid = $session->amount_total / 100; // Convertir en devise (ex: 100 = 1.00 TND)
        $payment_date = date('Y-m-d H:i:s');
        $session_id = $session->id;

        // Connexion à la base de données
        $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Enregistrer les détails du paiement dans la base de données
        $stmt = $pdo->prepare("INSERT INTO paiements (email_utilisateur, montant, date_paiement, session_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_email, $amount_paid, $payment_date, $session_id]);

        // Redirection vers la page d'admin ou autre page de confirmation
        header("Location: admin_paiements.php"); // Rediriger l'admin pour voir la liste des paiements
        exit();
    } else {
        echo "<h2 style='color:red;'>❌ Paiement non validé.</h2>";
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
