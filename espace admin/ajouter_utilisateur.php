<?php
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $cin = $_POST['cin'] ?? '';
        $email = $_POST['email'] ?? '';
        $motdepasse = $_POST['motdepasse'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $photo_pdp = $_FILES['photo_pdp'] ?? null;
        $photo_voiture = $_FILES['photo_voiture'] ?? null;

        if (!preg_match('/^\d{8}$/', $cin)) {
            $error = "Le CIN doit contenir exactement 8 chiffres.";
        } elseif (!preg_match('/^\d{8}$/', $telephone)) {
            $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
        } elseif (!$photo_pdp || $photo_pdp['error'] !== UPLOAD_ERR_OK) {
            $error = "La photo de profil est obligatoire.";
        } else {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE cin = ? OR email = ?");
            $checkStmt->execute([$cin, $email]);
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                $error = "Un utilisateur avec ce CIN ou cet email existe déjà.";
            } else {
                $photo_pdp_path = "uploads/" . uniqid() . "_" . basename($photo_pdp["name"]);
                move_uploaded_file($photo_pdp["tmp_name"], $photo_pdp_path);

                $photo_voiture_path = null;
                if ($photo_voiture && $photo_voiture['error'] === UPLOAD_ERR_OK) {
                    $photo_voiture_path = "uploads/" . uniqid() . "_" . basename($photo_voiture["name"]);
                    move_uploaded_file($photo_voiture["tmp_name"], $photo_voiture_path);
                }

                $stmt = $pdo->prepare("INSERT INTO utilisateurs (cin, nom, prenom, email, mot_de_passe, telephone, photo_pdp, photo_voiture) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $cin,
                    $nom,
                    $prenom,
                    $email,
                    password_hash($motdepasse, PASSWORD_BCRYPT),
                    $telephone,
                    $photo_pdp_path,
                    $photo_voiture_path
                ]);

                $success = "Inscription réussie !";
            }
        }

    } catch (PDOException $e) {
        $error = "Erreur de base de données : " . $e->getMessage();
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
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(255, 224, 24, 0.9);
            width: 400px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input {
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

        button {
            background: #f3ab25;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        button:hover {
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
    </style>
</head>
<body>
<div class="form-container">
    <h2>Inscription</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="form">
        <div class="form-group">
            <label for="nom">Nom:</label>
            <input type="text" name="nom" id="nom">
        </div>
        <div class="form-group">
            <label for="prenom">Prénom:</label>
            <input type="text" name="prenom" id="prenom">
        </div>
        <div class="form-group">
            <label for="cin">CIN:</label>
            <input type="text" name="cin" id="cin">
        </div>
        <div class="form-group">
            <label for="email">Adresse Email:</label>
            <input type="text" name="email" id="email">
        </div>
        <div class="form-group">
            <label for="motdepasse">Mot de passe:</label>
            <input type="password" name="motdepasse" id="motdepasse">
        </div>
        <div class="form-group">
            <label for="telephone">Téléphone:</label>
            <input type="text" name="telephone" id="telephone">
        </div>
        <div class="form-group">
            <label for="photo_voiture">Photo de voiture (optionnelle):</label>
            <input type="file" name="photo_voiture" id="photo_voiture" accept="image/*">
        </div>
        <div class="form-group">
            <label for="photo_pdp">Photo de profil:</label>
            <input type="file" name="photo_pdp" id="photo_pdp" accept="image/*">
        </div>
        <button type="submit">S'inscrire</button>
        <div class="login-link">
            Déjà inscrit ? <a href="login.php">Connexion</a>
        </div>
    </form>
</div>

<script>
document.getElementById("form").addEventListener("submit", function (e) {
    const nom = document.getElementById("nom").value.trim();
    const prenom = document.getElementById("prenom").value.trim();
    const cin = document.getElementById("cin").value.trim();
    const email = document.getElementById("email").value.trim();
    const motdepasse = document.getElementById("motdepasse").value.trim();
    const telephone = document.getElementById("telephone").value.trim();
    const photo_pdp = document.getElementById("photo_pdp");

    // Vérifications JS
    if (!/^[a-zA-Z]{2,}$/.test(nom)) {
        alert("Le nom doit contenir au moins 2 lettres.");
        e.preventDefault();
        return;
    }

    if (!/^[a-zA-Z]{2,}$/.test(prenom)) {
        alert("Le prénom doit contenir au moins 2 lettres.");
        e.preventDefault();
        return;
    }

    if (!/^\d{8}$/.test(cin)) {
        alert("Le CIN doit contenir exactement 8 chiffres.");
        e.preventDefault();
        return;
    }

    if (!/^[\w\.-]+@[\w\.-]+\.\w{2,4}$/.test(email)) {
        alert("L'adresse email est invalide.");
        e.preventDefault();
        return;
    }

    if (motdepasse.length < 6) {
        alert("Le mot de passe doit contenir au moins 6 caractères.");
        e.preventDefault();
        return;
    }

    if (!/^\d{8}$/.test(telephone)) {
        alert("Le numéro de téléphone doit contenir exactement 8 chiffres.");
        e.preventDefault();
        return;
    }

    if (!photo_pdp.files.length) {
        alert("La photo de profil est obligatoire.");
        e.preventDefault();
        return;
    }
});
</script>
</body>
</html>
