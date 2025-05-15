<?php
session_start();
require_once '../../vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('273604060688-eqm5ck65mdjtcns10qn35mqm2ua9gk7a.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/web/espace%20utilisateur/google_callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token["error"])) {
        $client->setAccessToken($token['access_token']);
      

   

        // Connexion BDD
        $pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8mb4", "root", "");

        // Vérifie si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$utilisateur) {
            // Crée un nouvel utilisateur si pas trouvé
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, photo_pdp) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $photo]);
            $cin = $pdo->lastInsertId();
        } else {
            $cin = $utilisateur['cin'];
        }

        $_SESSION['cin'] = $cin;
        $_SESSION['email'] = $email;

        header('Location: ../interface.php');
        exit();
    } else {
        echo "Erreur lors de l'authentification Google.";
    }
} else {
    echo "Code Google non fourni.";
}
