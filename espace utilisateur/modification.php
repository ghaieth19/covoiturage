<?php
session_start();

if (!isset($_SESSION['cin'])) {
    header("Location: login.html");
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

$cin = $_SESSION['cin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $photo_pdp = $_FILES['photo_pdp'];

    $params = [
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':telephone' => $telephone
    ];

    $sql = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone";

    if (!empty($mot_de_passe)) {
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
        $sql .= ", mot_de_passe = :mot_de_passe";
        $params[':mot_de_passe'] = $mot_de_passe_hash;
    }

    if (!empty($photo_pdp['name'])) {
        $photo_nom = basename($photo_pdp['name']);
        $dossier = "uploads/";
        move_uploaded_file($photo_pdp['tmp_name'], $dossier . $photo_nom);
        $sql .= ", photo_pdp = :photo_pdp";
        $params[':photo_pdp'] = $photo_nom;
    }

    $sql .= " WHERE cin = :cin";
    $params[':cin'] = $cin;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: modification.php?success=1");
    exit();
}

// Récupération des infos pour affichage
$sql = "SELECT nom, prenom, email, telephone FROM utilisateurs WHERE cin = :cin";
$stmt = $pdo->prepare($sql);
$stmt->execute([':cin' => $cin]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mes informations</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4; /* Fond gris clair par défaut */
            color: #333; /* Texte sombre par défaut */
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column; /* Pour placer le header en haut */
            align-items: center;
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        header {
            background-color: #222; /* Header noir */
            color: white; /* Texte blanc pour le header */
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        header div strong {
            font-size: 20px;
        }

        header div a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }

        header div a:hover {
            text-decoration: underline;
        }

        form {
            background-color: #fff; /* Fond blanc pour le formulaire */
            color: #333; /* Texte sombre pour le formulaire */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            width: 350px;
            margin-top: 20px; /* Espacement entre le header et le formulaire */
            transition: background-color 0.3s, color 0.3s;
        }

        h2 {
            text-align: center;
            color: #222;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: white;
            transition: background-color 0.3s, color 0.3s;
        }

        button {
            background-color: rgb(7, 147, 255);
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e6b800;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }

        .toggle-dark {
            background-color: #333;
            color: #fff;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .toggle-dark:hover {
            background-color: #555;
        }

        /* Dark mode styles */
        body.dark {
            background-color: #121212;
            color: #f1f1f1;
        }

        body.dark header {
            background-color: #000;
            color: #f1f1f1;
        }

        body.dark form {
            background-color: #1e1e1e;
            color: #f1f1f1;
        }

        body.dark input[type="text"],
        body.dark input[type="email"],
        body.dark input[type="password"],
        body.dark input[type="file"] {
            background-color: #2c2c2c;
            color: #f1f1f1;
            border: 1px solid #444;
        }

        body.dark button {
            background-color: #007bff;
            color: #fff;
        }

        body.dark button:hover {
            background-color: #0056b3;
        }

        body.dark .toggle-dark {
            background-color: #3a3a3a;
            color: #f1f1f1;
        }

        body.dark .toggle-dark:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

    <header>
        <div><strong>Covoiturage TN</strong></div>
        <div>
            <a href="interface.php">Accueil</a>
            <a href="publication.php">Publier</a>
            <a href="contact.php">Support</a>
            <a href="couvoiturage.php">Déconnexion</a>
        </div>
    </header>

    <form method="POST" enctype="multipart/form-data">
        <h2>Modifier mes informations</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">✅ Modification effectuée avec succès.</div>
        <?php endif; ?>

        <input type="text" name="nom" placeholder="Nom" value="<?php echo htmlspecialchars($utilisateur['nom']); ?>" required>
        <input type="text" name="prenom" placeholder="Prénom" value="<?php echo htmlspecialchars($utilisateur['prenom']); ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($utilisateur['email']); ?>" required>
        <input type="text" name="telephone" placeholder="Téléphone" value="<?php echo htmlspecialchars($utilisateur['telephone']); ?>" required>
        <input type="password" name="mot_de_passe" placeholder="Nouveau mot de passe (facultatif)">
        <input type="file" name="photo_pdp">
        <button type="submit">Enregistrer</button>
        <button type="button" class="toggle-dark" onclick="toggleDarkMode()">🌙 Mode Nuit</button>
    </form>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark');
            localStorage.setItem('darkMode', document.body.classList.contains('dark') ? 'on' : 'off');
        }

        // Charger l'état du dark mode depuis le localStorage
        window.onload = () => {
            if (localStorage.getItem('darkMode') === 'on') {
                document.body.classList.add('dark');
            }
        };
    </script>
</body>
</html>