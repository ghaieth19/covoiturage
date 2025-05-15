<?php
require 'vendor/autoload.php'; // Stripe SDK

\Stripe\Stripe::setApiKey('sk_test_51RK0KIPvBY8IYmBBYemsdroZfYIhog8ZpgUP76jh8eF1T8kVkuyEDrVF8MovV4M0vrpMIdB5qT73yDTBArDumdP800cGeMUIeV'); // Clé secrète Stripe

session_start();


$trajet_id = (int)$_GET['id'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des infos utilisateur
    $stmt = $conn->prepare("SELECT cin, points_traject FROM utilisateurs WHERE email = ?");
    $stmt->execute([$_SESSION['email']]);
    $user = $stmt->fetch();

    if (!$user) throw new Exception("Utilisateur non trouvé.");

    $cin = $user['cin'];
    $points = $user['points_traject'];

    // Récupération du trajet et de son prix
    $trajet_stmt = $conn->prepare("SELECT prix_par_passager FROM publications WHERE id = ?");
    $trajet_stmt->execute([$trajet_id]);
    $trajet = $trajet_stmt->fetch();

    if (!$trajet) throw new Exception("Trajet non trouvé.");

    $prix_base = floatval($trajet['prix_par_passager']);

    // Application de la remise selon les points
    if ($points >= 2000) {
        $prix_final = $prix_base * 0.5;
    } elseif ($points >= 1000) {
        $prix_final = $prix_base * 0.7;
    } else {
        $prix_final = $prix_base;
    }

    // Conversion en EUR (si nécessaire)
    $taux_tnd_eur = 3.2; // exemple : 1 EUR = 3.2 TND
    $prix_eur = $prix_final / $taux_tnd_eur;

    // Création de la session de paiement
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Réservation de trajet',
                    'description' => "Trajet ID #$trajet_id"
                ],
                'unit_amount' => intval($prix_eur * 100), // montant en centimes
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/web/espace%20utilisateur/paiement.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/web/espace%20utilisateur/cancel.php',
        'metadata' => [
            'cin_utilisateur' => $cin,
            'id_trajet' => $trajet_id,
            'prix_tnd' => $prix_final
        ]
    ]);

    header("Location: " . $session->url);
    exit();

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
