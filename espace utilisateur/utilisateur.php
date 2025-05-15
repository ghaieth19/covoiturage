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
    die("Erreur de connexion : " . $e->getMessage());
}

$cin = $_SESSION['cin'];

$sql = "SELECT nom, prenom, email, telephone, photo_pdp FROM utilisateurs WHERE cin = :cin";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cin', $cin);
$stmt->execute();
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Utilisateur</title>
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
            transition: background-color 0.5s, color 0.5s;
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

        .profil-box {
            background-color: #fff; /* Fond blanc pour la boîte de profil */
            color: #333; /* Texte sombre pour la boîte de profil */
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            width: 400px;
            margin-top: 20px; /* Espacement entre le header et la boîte de profil */
        }

        .profil-box img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fdd835;
            margin-bottom: 20px;
        }

        .btn-modifier {
            margin-top: 25px;
            padding: 12px 25px;
            font-size: 16px;
            background-color: #fbc02d;
            border: none;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-modifier:hover {
            background-color: #f9a825;
        }

        .toggle-dark {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .toggle-dark:hover {
            background-color: #555;
        }

        .dark-mode {
            background-color: #121212;
            color: #f1f1f1;
        }

        .dark-mode .profil-box {
            background-color: #1e1e1e;
            color: #f1f1f1;
        }

        .dark-mode .toggle-dark {
            background-color: #fdd835;
            color: #000;
        }

        .dark-mode .toggle-dark:hover {
            background-color: #e0c830;
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


    <div class="profil-box">
        <img src="../uploads/<?php echo htmlspecialchars($utilisateur['photo_pdp']); ?>" alt="Photo de profil">
        <h2>Mon Compte</h2>
        <p><strong>Nom :</strong> <?php echo htmlspecialchars($utilisateur['nom']); ?></p>
        <p><strong>Prénom :</strong> <?php echo htmlspecialchars($utilisateur['prenom']); ?></p>
        <p><strong>Email :</strong> <?php echo htmlspecialchars($utilisateur['email']); ?></p>
        <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($utilisateur['telephone']); ?></p>
        <a href="modification.php"><button class="btn-modifier">Modifier mes informations</button></a>
    </div>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }
    </script>
</body>
</html>