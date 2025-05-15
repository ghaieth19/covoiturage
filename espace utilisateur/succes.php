<?php
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51RK0KIPvBY8IYmBBYemsdroZfYIhog8ZpgUP76jh8eF1T8kVkuyEDrVF8MovV4M0vrpMIdB5qT73yDTBArDumdP800cGeMUIeV'); // TA CLÉ PRIVÉE

session_start();

if (!isset($_GET['session_id'])) {
    die("Session de paiement manquante.");
}

$session_id = $_GET['session_id'];

try {
    // Récupération de la session Stripe
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    $montant = $session->amount_total / 100; // En euros
    $metadata = $session->metadata;

    // Connexion à la base
    $conn = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enregistrement du paiement
    $stmt = $conn->prepare("INSERT INTO paiements (cin_utilisateur, montant, remise_appliquee, session_id)
                            VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $metadata->cin_utilisateur,
        $montant,
        $metadata->remise_appliquee ?? 'non',
        $session_id
    ]);

    // Ajouter des points au client pour le trajet payé (exemple : 100 points)
    $conn->prepare("UPDATE utilisateurs SET points_traject = points_traject + 100 WHERE cin = ?")
         ->execute([$metadata->cin_utilisateur]);

    echo "<h1>Paiement réussi ✅</h1>";
    echo "<p>Montant payé : $montant €</p>";
    if ($metadata->remise_appliquee === 'oui') {
        echo "<p>Une remise a été appliquée ! 🎉</p>";
    }

} catch (Exception $e) {
    echo "Erreur lors du traitement : " . $e->getMessage();
}
