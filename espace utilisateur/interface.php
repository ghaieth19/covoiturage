<?php
session_start();

// Vérification de la connexion de l'utilisateur
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = htmlspecialchars($_SESSION['email']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface Utilisateur</title>
    <style>
        :root {
            --bg-color: rgb(245, 245, 245);
            --text-color: #333;
            --container-bg: white;
            --option-bg:rgb(44, 44, 44);
            --option-hover:rgb(45, 45, 45);
        }

        body.dark-mode {
            --bg-color: #121212;
            --text-color: #f1f1f1;
            --container-bg: #1e1e1e;
            --option-bg: #bb86fc;
            --option-hover: #9f6dfd;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        header {
            background-color: #333;
            color: white;
            padding: 15px 20px;
            width: 100%;
            text-align: center;
            font-size: 1.2em;
            font-weight: bold;
        }

        .container {
            background-color: var(--container-bg);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: fadeIn 1s ease-in-out;
            position: relative;
            margin-top: 40px;
        }

        .dark-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: 2px solid var(--option-bg);
            color: var(--option-bg);
            padding: 5px 10px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
        }

        h2 {
            color: var(--option-bg);
            font-size: 1.8em;
            margin-bottom: 20px;
            animation: slideIn 1s ease-in-out;
        }

        .options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .option {
            background-color: var(--option-bg);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .option:hover {
            background-color: var(--option-hover);
            transform: translateY(-5px);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            .options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- ✅ Header HTML ajouté -->
    <header>
        Interface Utilisateur
    </header>

    <div class="container">
        <button class="dark-toggle" onclick="toggleDarkMode()">🌙</button>
        <h2>Bienvenue, <?= $email ?> !</h2>
        <div class="options">
            <a href="utilisateur.php" class="option">👤 Mon compte</a>
            <a href="publication.php" class="option">🚗 Publier un trajet</a>
            <a href="recherche.php" class="option">🔍 Rechercher un trajet</a>
            <a href="parrainage.php" class="option">👥 Parrainage</a>
            <a href="point_recompense.php" class="option">🎯 Progression</a>
            <a href="traitement paiement.php" class="option">💳 Paiement</a>
            <a href="contact.php" class="option">✉️ Contact</a>
            <a href="couvoiturage.php" class="option">🚪 Déconnexion</a>
        </div>
    </div>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle("dark-mode");
            localStorage.setItem("darkMode", document.body.classList.contains("dark-mode"));
        }

        if (localStorage.getItem("darkMode") === "true") {
            document.body.classList.add("dark-mode");
        }
    </script>
</body>
</html>
