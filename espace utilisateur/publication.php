<?php
session_start();

try {
    $conn = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

$message = "";

if (!isset($_SESSION['cin'])) {
    $message = "<p style='text-align:center;color:red;'>❌ Veuillez vous connecter pour publier un trajet.</p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['cin'])) {
    $sql = "INSERT INTO publications (utilisateur_cin, depart, destination, date, heure, places, marque_voiture, matricule_voiture, telephone, prix_par_passager)
            VALUES (:cin, :depart, :destination, :date, :heure, :places, :marque, :matricule, :telephone, :prix)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':cin' => $_SESSION['cin'],
            ':depart' => $_POST['depart'],
            ':destination' => $_POST['destination'],
            ':date' => $_POST['date'],
            ':heure' => $_POST['heure'],
            ':places' => $_POST['places'],
            ':marque' => $_POST['marque_voiture'],
            ':matricule' => $_POST['matricule_voiture'],
            ':telephone' => $_POST['telephone'],
            ':prix' => $_POST['prix_par_passager']
        ]);
        $message = "<p style='text-align:center;color:green;margin-top:20px;'>✅ Trajet publié avec succès !</p>";
    } catch (PDOException $e) {
        $message = "<p style='text-align:center;color:red;margin-top:20px;'>❌ Erreur : " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Publier un trajet - Covoiturage TN</title>
  <style>
    body { font-family: Arial, sans-serif; background-color:#fff; margin: 0; color: #111; }
    .navbar { background-color: #000; color: white; padding: 25px 30px; display: flex; justify-content: space-between; align-items: center; }
    .navbar a { color: white; text-decoration: none; margin-right: 20px; font-weight: bold; }
    .form-container { background: white; max-width: 500px; margin: 40px auto; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
    .form-container h2 { text-align: center; margin-bottom: 20px; }
    .form-container input { width: 100%; padding: 12px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; }
    .form-container button { width: 100%; background:#000; color: white; padding: 12px; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; margin-top: 10px; }
    .form-container button:hover { background-color:#333; }
    .suivi-btn { text-align: center; margin-top: 20px; }
    .suivi-btn a { display: inline-block; background-color: #000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; }
    .suivi-btn a:hover { background-color: #333; }
    footer { text-align: center; margin-top: 40px; color: white; background: #000; padding: 10px; }
  </style>
</head>
<body>

<div class="navbar">
  <div>
    <a href="interface.php">Accueil</a>
    <a href="recherche.php">Rechercher</a>
    <a href="contact.php">Support</a>
  </div>
  <div>
    <a href="couvoiturage.php" style="color:white">Déconnexion</a>
  </div>
</div>

<?= $message ?>

<div class="form-container">
  <h2>Publiez un trajet</h2>
  <form method="POST">
    <input type="text" name="depart" placeholder="Départ">
    <input type="text" name="destination" placeholder="Destination">
    <input type="date" name="date">
    <input type="time" name="heure">
    <input type="number" name="places" placeholder="Nombre de places">
    <input type="text" name="marque_voiture" placeholder="Marque de voiture">
    <input type="text" name="matricule_voiture" placeholder="Matricule de voiture">
    <input type="tel" name="telephone" placeholder="Téléphone">
    <input type="number" name="prix_par_passager" step="0.01" placeholder="Prix par passager (TND)">
    <button type="submit">Publier</button>
  </form>

  <div class="suivi-btn">
    <a href="suivi_traject.php">📍 Suivi de mes trajets</a>
  </div>
</div>

<footer>
  &copy; 2025 Covoiturage TN
</footer>

<script>
document.querySelector("form").addEventListener("submit", function(e) {
    let formValid = true;
    let messages = [];

    const telephone = document.querySelector('input[name="telephone"]').value.trim();
    const matricule = document.querySelector('input[name="matricule_voiture"]').value.trim();
    const prix = parseFloat(document.querySelector('input[name="prix_par_passager"]').value);
    const places = parseInt(document.querySelector('input[name="places"]').value);
    const dateInput = document.querySelector('input[name="date"]').value;
    const marque = document.querySelector('input[name="marque_voiture"]').value.trim();
    const depart = document.querySelector('input[name="depart"]').value.trim();
    const destination = document.querySelector('input[name="destination"]').value.trim();

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (!/^\d{8}$/.test(telephone)) {
        formValid = false;
        messages.push("📞 Le numéro de téléphone doit contenir exactement 8 chiffres.");
    }

    if (matricule.length < 6) {
        formValid = false;
        messages.push("🚗 Le matricule doit contenir au moins 6 caractères.");
    }

    if (isNaN(prix) || prix <= 0) {
        formValid = false;
        messages.push("💰 Le prix par passager doit être un nombre positif.");
    }

    if (isNaN(places) || places < 1 || places > 6) {
        formValid = false;
        messages.push("👥 Le nombre de places doit être entre 1 et 6.");
    }

    if (dateInput) {
        const date = new Date(dateInput);
        if (date < today) {
            formValid = false;
            messages.push("📅 La date ne peut pas être dans le passé.");
        }
    } else {
        formValid = false;
        messages.push("📅 La date est obligatoire.");
    }

    const textOnly = str => /^[A-Za-zÀ-ÿ\s\-']+$/.test(str);

    if (!textOnly(depart)) {
        formValid = false;
        messages.push("🛫 Le champ Départ doit contenir uniquement des lettres.");
    }

    if (!textOnly(destination)) {
        formValid = false;
        messages.push("🛬 Le champ Destination doit contenir uniquement des lettres.");
    }

    if (!textOnly(marque)) {
        formValid = false;
        messages.push("🚘 La marque de voiture doit contenir uniquement des lettres.");
    }

    if (!formValid) {
        e.preventDefault();
        alert(messages.join("\n"));
    }
});
</script>

</body>
</html>
