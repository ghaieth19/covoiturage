<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $publication_id = $_GET['id'];

    // Récupérer la publication
    $stmt = $pdo->prepare("SELECT * FROM publications WHERE id = ?");
    $stmt->execute([$publication_id]);
    $publication = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$publication) {
        $_SESSION['error'] = "Publication non trouvée.";
        header("Location: recherche.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $depart = $_POST['depart'];
    $destination = $_POST['destination'];
    $date = $_POST['date'];
    $heure = $_POST['heure'];
    $places = $_POST['places'];
    $prix = $_POST['prix'];

    $stmt = $pdo->prepare("UPDATE publications SET depart = ?, destination = ?, date = ?, heure = ?, places = ?, prix_par_passager = ? WHERE id = ?");
    $stmt->execute([$depart, $destination, $date, $heure, $places, $prix, $publication_id]);

    $_SESSION['message'] = "✅ Publication mise à jour avec succès.";
    header("Location: interface.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Publication</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:rgb(254, 254, 254);
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color:rgb(0, 0, 0);
            color: #fff;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        header a {
            color: #ecf0f1;
            text-decoration: none;
            font-weight: 600;
            margin-left: 20px;
            transition: color 0.3s;
        }

        header a:hover {
            color: #f39c12;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 1rem;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #f9f9f9;
            color: #2c3e50;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3498db;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 14px;
            font-size: 1.1rem;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #218838;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
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

<div class="container">
    <h2>Modifier Publication</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?= isset($_SESSION['error']) ? 'error' : 'success' ?>">
            <?= $_SESSION['message']; unset($_SESSION['message'], $_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="depart">Départ</label>
            <input type="text" id="depart" name="depart" value="<?= htmlspecialchars($publication['depart']) ?>" required>
        </div>

        <div class="form-group">
            <label for="destination">Destination</label>
            <input type="text" id="destination" name="destination" value="<?= htmlspecialchars($publication['destination']) ?>" required>
        </div>

        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($publication['date']) ?>" required>
        </div>

        <div class="form-group">
            <label for="heure">Heure</label>
            <input type="time" id="heure" name="heure" value="<?= htmlspecialchars($publication['heure']) ?>" required>
        </div>

        <div class="form-group">
            <label for="places">Places restantes</label>
            <input type="number" id="places" name="places" value="<?= htmlspecialchars($publication['places']) ?>" required>
        </div>

        <div class="form-group">
            <label for="prix">Prix par passager (TND)</label>
            <input type="number" id="prix" name="prix" value="<?= htmlspecialchars($publication['prix_par_passager']) ?>" required>
        </div>

        <button type="submit" class="btn">Mettre à jour la publication</button>
    </form>
</div>

</body>
</html>
