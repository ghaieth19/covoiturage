<?php
require_once __DIR__ . '/vendor/autoload.php';

session_start();

// Vérifie si un état est défini avant d'envoyer une requête à Facebook
if (!isset($_GET['state'])) {
    exit('Erreur : État manquant');
}

// Si l'état ne correspond pas, c'est une tentative de falsification CSRF
if ($_GET['state'] !== $_SESSION['oauth2state']) {
    unset($_SESSION['oauth2state']);
    exit('Erreur de sécurité : État non valide');
}

// Vérifie si le code OAuth2 est bien présent dans l'URL
if (!isset($_GET['code'])) {
    exit('Erreur OAuth : Code manquant');
}

$fbProvider = new League\OAuth2\Client\Provider\Facebook([
    'clientId'          => '2127706074339171', // Ton App ID Facebook
    'clientSecret'      => 'f595a7b667466859490bf04f033718f6', // Ton App Secret Facebook
    'redirectUri'       => 'http://localhost/web/facebook_callback.php', // L'URL de redirection
    'graphApiVersion'   => 'v19.0', // Version de l'API Graph de Facebook
]);

try {
    // Récupère le token d'accès avec le code d'autorisation retourné par Facebook
    $accessToken = $fbProvider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
    ]);

    // Utilise le token d'accès pour récupérer les informations de l'utilisateur
    $user = $fbProvider->getResourceOwner($accessToken);

    $userData = $user->toArray();

    // Affichage des informations de l'utilisateur (pour débogage)
    echo "<h2>Connexion réussie via Facebook !</h2>";
    echo "<pre>";
    print_r($userData); // Affiche les données récupérées de Facebook (comme le nom, l'email, etc.)
    echo "</pre>";

    // Optionnellement, tu peux maintenant stocker l'utilisateur dans ta base de données ou lui créer une session

} catch (Exception $e) {
    exit('Erreur lors de l\'authentification avec Facebook : ' . $e->getMessage());
}
