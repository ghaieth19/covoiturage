<?php  
session_start();

// Connexion à la base de données
$pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8mb4", "root", "");
$loginError = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recaptchaSecret = '6Lf5iSYrAAAAACyfjr-4lNvV8WGMIlA1mYr95JRf'; // Ta clé secrète
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if ($recaptchaResponse) {
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
        $response = json_decode($verify);

        if ($response->success) {
            $email = $_POST['email'] ?? '';
            $motDePasse = $_POST['mot_de_passe'] ?? '';

            if (!empty($email) && !empty($motDePasse)) {
                $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
                $stmt->execute([$email]);
                $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
                    $_SESSION['cin'] = $utilisateur['cin'];
                    $_SESSION['email'] = $utilisateur['email'];
                    header("Location:interface.php"); // Redirection modifiée ici
                    exit();
                } else {
                    $loginError = "Email ou mot de passe incorrect.";
                }
            } else {
                $loginError = "Veuillez remplir tous les champs.";
            }
        } else {
            $loginError = "Vérification reCAPTCHA échouée.";
        }
    } else {
        $loginError = "Veuillez valider le reCAPTCHA.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Covoiturage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        :root {
            --background-color: #ffdf7d;
            --text-color: #000;
            --box-bg: #fff;
            --btn-color: #FFC107;
            --btn-hover: #FFD54F;
            --input-bg: #fff;
        }

        body.dark-mode {
            --background-color: #121212;
            --text-color: #f1f1f1;
            --box-bg: #1e1e1e;
            --btn-color: #bb86fc;
            --btn-hover: #9f6dfd;
            --input-bg: #2c2c2c;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            transition: 0.3s;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-box {
            background: var(--box-bg);
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            background: var(--input-bg);
            border: 1px solid #ccc;
            border-radius: 5px;
            color: var(--text-color);
            transition: 0.3s;
        }

        .g-recaptcha {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }

        .btn {
            width: 100%;
            background: var(--btn-color);
            color: #000;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: var(--btn-hover);
        }

        .options, .social-login, .create-account {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }

        .options a, .create-account a {
            color: #007bff; /* Bleu */
            text-decoration: underline;
            font-weight: bold;
        }

        .social-login a {
            display: block;
            margin-top: 10px;
            text-decoration: none;
        }

        .create-account {
            margin-top: 25px;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        .dark-mode-toggle, .prev-link {
            position: absolute;
            top: 10px;
            font-size: 20px;
        }

        .dark-mode-toggle {
            right: 10px;
            background: transparent;
            border: 2px solid var(--btn-color);
            color: var(--btn-color);
            border-radius: 20px;
            padding: 5px 12px;
            cursor: pointer;
        }

        .prev-link {
            left: 10px;
            color: var(--btn-color);
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <a href="acceuil.html" class="prev-link">&#8592;</a>
        <button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙</button>

        <h2>Connexion</h2>

        <?php if ($loginError): ?>
            <div class="error-message"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>

        <form method="post" id="login-form">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
                <div class="error-message" id="email-error"></div>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe :</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                <div class="error-message" id="password-error"></div>
            </div>

            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="6Lf5iSYrAAAAAKxxwAdlEQ6Rd97fe9dYmxTnjJ7X"></div> 

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div class="options">
            <label><input type="checkbox"> Se souvenir de moi</label>
            <br>
            <a href="envoyer_code.php">Mot de passe oublié ?</a>
        </div>

        <div class="social-login">
            <p>Ou se connecter avec :</p>
            <a href="facebook_login.php" class="btn" style="background-color: #3b5998; color: white;">Facebook</a>
            <a href="google_login.php" class="btn" style="background-color: #db4437; color: white;">Google</a>
        </div>

        <div class="create-account">
            <p>Pas encore de compte ? <a href="ajouter_utilisateur.php">Créer un compte</a></p>
        </div>
    </div>
</div>

<script>
    function toggleDarkMode() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    }

    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }

    document.getElementById('login-form').addEventListener('submit', function (event) {
        let valid = true;
        if (!document.getElementById('email').value) {
            document.getElementById('email-error').textContent = "Veuillez entrer votre email.";
            valid = false;
        }
        if (!document.getElementById('mot_de_passe').value) {
            document.getElementById('password-error').textContent = "Veuillez entrer votre mot de passe.";
            valid = false;
        }
        if (!valid) event.preventDefault();
    });
</script>

</body>
</html>
