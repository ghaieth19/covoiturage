<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = '';
$step = 'email';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 'email';

    if ($step === 'email') {
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "❌ Email invalide.";
        } else {
            $_SESSION['email'] = $email;
            $code = rand(100000, 999999);
            $_SESSION['code'] = $code;

            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ghaiethbouamor23@gmail.com';
                $mail->Password = 'mykg xsrq rcdv fymm'; // Remplace avec ton mot de passe d’application
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('ghaiethbouamor23@gmail.com', 'Covoiturage - Support');

                $mail->addAddress($email);
                // 📷 Ajout de l'image
                $mail->addEmbeddedImage('photo1.png', 'logo_site');
                $mail->isHTML(true);
                $mail->Subject = '🔐 Votre code de vérification';
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; text-align: center;'>
                    <img src='cid:logo_site' alt='Logo' style='width: 150px; margin-bottom: 20px;'>
                    <h2>Votre code de vérification</h2>
                    <p style='font-size: 18px;'>Voici votre code :</p>
                    <p style='font-size: 24px; font-weight: bold; color: #fbc02d;'>$code</p>
                </div>
            ";

                $mail->send();
                $message = "✅ Code envoyé à <strong>" . htmlspecialchars($email) . "</strong>.";
                $step = 'code';
            } catch (Exception $e) {
                $message = "❌ Erreur d'envoi : " . htmlspecialchars($mail->ErrorInfo);
            }
        }
    }

    elseif ($step === 'code') {
        $code = trim($_POST['code']);
        if ($code == $_SESSION['code']) {
            $step = 'reset';
        } else {
            $message = "❌ Code incorrect.";
            $step = 'code';
        }
    }

    elseif ($step === 'reset') {
        $mdp1 = $_POST['mdp1'];
        $mdp2 = $_POST['mdp2'];

        if ($mdp1 !== $mdp2) {
            $message = "❌ Les mots de passe ne correspondent pas.";
            $step = 'reset';
        } else {
            $email = $_SESSION['email'];
            $hashed = password_hash($mdp1, PASSWORD_BCRYPT);

            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            if ($conn->connect_error) die("Erreur connexion DB");

            $stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe=? WHERE email=?");
            $stmt->bind_param("ss", $hashed, $email);

            if ($stmt->execute()) {
                $message = "✅ Mot de passe réinitialisé ! <a href='login.php'>Se connecter</a>";
                session_destroy();
                $step = 'done';
            } else {
                $message = "❌ Erreur SQL lors de la mise à jour.";
                $step = 'reset';
            }

            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation</title>
    <style>
        :root {
            --bg-light: #fefae0;
            --bg-dark: #1a1a1a;
            --card-light: #fff;
            --card-dark: #2a2a2a;
            --text-light: #000;
            --text-dark: #fff;
            --accent: #fbc02d;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('background1.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        .dark-mode {
            background: var(--bg-dark) !important;
            color: var(--text-dark);
        }

        .card {
            background: var(--card-light);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            width: 350px;
            transition: background 0.3s, color 0.3s;
        }

        .dark-mode .card {
            background: var(--card-dark);
            color: var(--text-dark);
        }

        h2 {
            margin-bottom: 15px;
            text-align: center;
        }

        input[type=email], input[type=text], input[type=password], input[type=submit] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        input[type=submit] {
            background-color: var(--accent);
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        input[type=submit]:hover {
            background-color: #e6b800;
        }

        .message {
            text-align: center;
            margin-top: 10px;
            padding: 10px;
            border-radius: 8px;
            background: #f8d7da;
            color: #721c24;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .toggle-mode {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--accent);
            color: white;
            padding: 5px 10px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="toggle-mode" onclick="toggleMode()">🌓</button>
    <div class="card">
        <?php if ($step === 'email'): ?>
            <h2>🔐 Entrez votre email</h2>
            <form method="POST">
                <input type="hidden" name="step" value="email">
                <input type="email" name="email" placeholder="Email" required>
                <input type="submit" value="Envoyer le code">
            </form>
        <?php elseif ($step === 'code'): ?>
            <h2>📩 Code reçu par email</h2>
            <form method="POST">
                <input type="hidden" name="step" value="code">
                <input type="text" name="code" placeholder="Code" required>
                <input type="submit" value="Vérifier le code">
            </form>
        <?php elseif ($step === 'reset'): ?>
            <h2>🔑 Nouveau mot de passe</h2>
            <form method="POST">
                <input type="hidden" name="step" value="reset">
                <input type="password" name="mdp1" placeholder="Mot de passe" required>
                <input type="password" name="mdp2" placeholder="Confirmer mot de passe" required>
                <input type="submit" value="Réinitialiser">
            </form>
        <?php elseif ($step === 'done'): ?>
            <h2>✅ Terminé</h2>
            <p>Mot de passe mis à jour avec succès.</p>
            <a href="login.php">🔐 Se connecter</a>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') === 0 ? 'success' : '' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleMode() {
            document.body.classList.toggle('dark-mode');
        }
    </script>
</body>
</html>
