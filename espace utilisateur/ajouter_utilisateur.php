<?php
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $cin = $_POST["cin"];
        $nom = $_POST["nom"];
        $prenom = $_POST["prenom"];
        $email = $_POST["email"];
        $mot_de_passe = password_hash($_POST["motdepasse"], PASSWORD_BCRYPT);
        $telephone = $_POST["telephone"];

        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Photo de profil obligatoire
        $photo_pdp = null;
        if (isset($_FILES["photo_pdp"]) && $_FILES["photo_pdp"]["error"] === UPLOAD_ERR_OK) {
            $photo_pdp_name = uniqid() . "_" . basename($_FILES["photo_pdp"]["name"]);
            $photo_pdp_path = $upload_dir . $photo_pdp_name;
            if (move_uploaded_file($_FILES["photo_pdp"]["tmp_name"], $photo_pdp_path)) {
                $photo_pdp = $photo_pdp_path;
            } else {
                $error = "Erreur lors de l’enregistrement de la photo de profil.";
            }
        } else {
            $error = "La photo de profil est obligatoire.";
        }

        // Photo de voiture optionnelle
        $photo_voiture = null;
        if (isset($_FILES["photo_voiture"]) && $_FILES["photo_voiture"]["error"] === UPLOAD_ERR_OK) {
            $photo_voiture_name = uniqid() . "_" . basename($_FILES["photo_voiture"]["name"]);
            $photo_voiture_path = $upload_dir . $photo_voiture_name;
            if (move_uploaded_file($_FILES["photo_voiture"]["tmp_name"], $photo_voiture_path)) {
                $photo_voiture = $photo_voiture_path;
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (cin, nom, prenom, email, mot_de_passe, telephone, photo_pdp, photo_voiture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cin, $nom, $prenom, $email, $mot_de_passe, $telephone, $photo_pdp, $photo_voiture]);
            $success = "✅ Utilisateur ajouté avec succès.";
        }

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Code erreur SQL pour clé dupliquée (CIN/email déjà existant)
            $error = "❌ Un utilisateur avec ce CIN ou email existe déjà.";
        } else {
            $error = "Erreur de base de données : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        body {
            background: url('background1.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            padding-top: 50px;
            transition: background-color 0.3s, color 0.3s;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(255, 224, 24, 0.9);
            width: 400px;
            position: relative;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="tel"], input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .error, .success {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
        }

        .error {
            background-color: #fdd;
            color: #a00;
        }

        .success {
            background-color: #dfd;
            color: #070;
        }

        button[type="submit"] {
            background: #f3ab25;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background: #ead40f;
        }

        .login-link {
            text-align: center;
            margin-top: 10px;
        }

        .login-link a {
            color: #f3ab25;
            text-decoration: none;
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #121212;
            background-image: none;
            color: #f1f1f1;
        }

        body.dark-mode .form-container {
            background: #1e1e1e;
            box-shadow: 0 0 10px rgba(255, 224, 24, 0.5);
        }

        body.dark-mode input,
        body.dark-mode input[type="file"] {
            background-color: #2c2c2c;
            color: white;
            border: 1px solid #555;
        }

        body.dark-mode label {
            color: #f3f3f3;
        }

        body.dark-mode .error {
            background-color: #661111;
            color: #fdd;
        }

        body.dark-mode .success {
            background-color: #225522;
            color: #dfd;
        }

        /* Dark mode toggle button */
        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            z-index: 999;
        }

        .dark-mode-toggle:focus {
            outline: none;
        }
    </style>
</head>
<body>

    <button class="dark-mode-toggle" id="darkModeBtn" title="Activer/désactiver le mode nuit">🌙</button>

    <div class="form-container">
        <h2>Inscription</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="form">
            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text" name="nom" id="nom" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom:</label>
                <input type="text" name="prenom" id="prenom" required>
            </div>
            <div class="form-group">
                <label for="cin">CIN:</label>
                <input type="text" name="cin" id="cin" maxlength="8" required>
            </div>
            <div class="form-group">
                <label for="email">Adresse Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="motdepasse">Mot de passe:</label>
                <input type="password" name="motdepasse" id="motdepasse" minlength="8" required>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone:</label>
                <input type="tel" name="telephone" id="telephone" pattern="\d{8}" maxlength="8" required>
            </div>
            <div class="form-group">
                <label for="photo_voiture">Photo de voiture (optionnelle):</label>
                <input type="file" name="photo_voiture" accept="image/*">
            </div>
            <div class="form-group">
                <label for="photo_pdp">Photo de profil:</label>
                <input type="file" name="photo_pdp" accept="image/*" required>
            </div>
            <button type="submit">S'inscrire</button>
            <div class="login-link">
                Déjà inscrit ? <a href="login.php">Connexion</a>
            </div>
        </form>
    </div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("form");
        form.addEventListener("submit", function(e) {
            let errorMessages = [];

            // Nom et Prénom (vérification basique, peut être améliorée)
            const nom = document.getElementById("nom").value.trim();
            const prenom = document.getElementById("prenom").value.trim();
            if (!/^[a-zA-Z\s]+$/.test(nom)) {
                errorMessages.push("❌ Le nom ne doit contenir que des lettres et des espaces.");
            }
            if (!/^[a-zA-Z\s]+$/.test(prenom)) {
                errorMessages.push("❌ Le prénom ne doit contenir que des lettres et des espaces.");
            }

            // CIN (exactement 8 chiffres)
            const cin = document.getElementById("cin").value.trim();
            if (!/^\d{8}$/.test(cin)) {
                errorMessages.push("❌ Le CIN doit contenir exactement 8 chiffres.");
            }

            // Email (format valide)
            const email = document.getElementById("email").value.trim();
            if (!/^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,}$/.test(email)) {
                errorMessages.push("❌ L'adresse email n'est pas valide.");
            }

            // Mot de passe (au moins 8 caractères)
            const motdepasse = document.getElementById("motdepasse").value;
            if (motdepasse.length < 8) {
                errorMessages.push("❌ Le mot de passe doit contenir au moins 8 caractères.");
            }

            // Téléphone (exactement 8 chiffres)
            const telephone = document.getElementById("telephone").value.trim();
            if (!/^\d{8}$/.test(telephone)) {
                errorMessages.push("❌ Le numéro de téléphone doit contenir exactement 8 chiffres.");
            }

            // Photo de profil (obligatoire)
            const photoPdp = document.querySelector('input[name="photo_pdp"]').files.length > 0;
            if (!photoPdp) {
                errorMessages.push("❌ La photo de profil est obligatoire.");
            }

            if (errorMessages.length > 0) {
                e.preventDefault();
                alert(errorMessages.join("\n"));
            }
        });

        // Bouton Dark Mode
        const darkBtn = document.getElementById("darkModeBtn");
        darkBtn.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");
            darkBtn.textContent = document.body.classList.contains("dark-mode") ? "☀️" : "🌙";
        });
    });
</script>
</body>
</html>