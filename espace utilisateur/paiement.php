<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'vendor/autoload.php';

session_start();

$message = ""; // Message vide par défaut

if (!isset($_SESSION['email'])) {
    $message = "Vous devez être connecté pour recevoir un email.";
} else {
    $email = $_SESSION['email'];
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ghaiethbouamor23@gmail.com';
        $mail->Password = 'mykg xsrq rcdv fymm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('contact@votre-site.com', 'Covoiturage');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de paiement - Covoiturage';
        $mail->Body    = '
        <html lang="fr">
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f9f9f9;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 50px auto;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 10px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                h2 {
                    color: #f8e05a;
                    text-align: center;
                    font-size: 2em;
                }
                p {
                    font-size: 1.2em;
                    line-height: 1.6;
                    color: #555;
                }
                .footer {
                    margin-top: 30px;
                    text-align: center;
                    font-size: 0.9em;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Confirmation de Paiement</h2>
                <p>Bonjour,</p>
                <p>Nous vous informons que votre paiement pour le trajet a été effectué avec succès.</p>
                <p>Merci pour votre confiance.</p>
                <div class="footer">
                    <p>Cordialement,</p>
                    <p>L’équipe Covoiturage</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = 'Votre paiement a été effectué avec succès. Merci pour votre confiance.';

        if ($mail->send()) {
            $message = "L'email de confirmation a été envoyé avec succès.";
        } else {
            $message = "L'envoi de l'email a échoué.";
        }

    } catch (Exception $e) {
        $message = "Erreur lors de l'envoi de l'email: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de Paiement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(245, 173, 29);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #f8e05a;
            text-align: center;
            font-size: 2em;
        }
        p {
            font-size: 1.2em;
            line-height: 1.6;
            color: #555;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }
        .message-box {
            background-color: #e6ffe6;
            border-left: 6px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 1.1em;
        }
        .button {
            display: inline-block;
            background-color: #f8e05a;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #f5c34d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Confirmation de Paiement</h2>
        <?php if (!empty($message)) : ?>
            <div class="message-box"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <p>Votre paiement a été effectué avec succès. Merci pour votre confiance.</p>
        <div class="footer">
            <p>Cordialement,</p>
            <p>L’équipe Covoiturage</p>
            <a href="http://localhost/web/espace%20utilisateur/interface.php" class="button">Retour à l'interface</a>
        </div>
    </div>
</body>
</html>
