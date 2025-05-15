<?php
session_start();

try {
    $conn = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

$message = "";

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['cin'])) {
    $message = "<p style='text-align:center;color:red;'>❌ Veuillez vous connecter pour publier un trajet.</p>";
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['cin'])) {
    $required = ['depart', 'destination', 'date', 'heure', 'places', 'marque_voiture', 'telephone', 'prix_par_passager'];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $message = "<p style='text-align:center;color:red;'>❌ Le champ '".ucfirst(str_replace('_', ' ', $field))."' est requis.</p>";
            break;
        }
    }

    if (empty($message)) {
        $cin = $_SESSION['cin'];
        $sql = "INSERT INTO publications (utilisateur_cin, depart, destination, date, heure, places, marque_voiture, telephone, prix_par_passager)
                VALUES (:cin, :depart, :destination, :date, :heure, :places, :marque_voiture, :telephone, :prix_par_passager)";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':cin' => $cin,
                ':depart' => $_POST['depart'],
                ':destination' => $_POST['destination'],
                ':date' => $_POST['date'],
                ':heure' => $_POST['heure'],
                ':places' => $_POST['places'],
                ':marque_voiture' => $_POST['marque_voiture'],
                ':telephone' => $_POST['telephone'],
                ':prix_par_passager' => $_POST['prix_par_passager']
            ]);
            $message = "<p style='text-align:center;color:green;margin-top:20px;'>✅ Trajet publié avec succès !</p>";
        } catch (PDOException $e) {
            $message = "<p style='text-align:center;color:red;margin-top:20px;'>❌ Erreur : ".$e->getMessage()."</p>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Publier un trajet - Covoiturage TN</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #FDBA24; margin: 0; color: #111; }
    .navbar { background-color: #000; color: white; padding: 25px 30px; display: flex; justify-content: space-between; align-items: center; }
    .navbar a { color: white; text-decoration: none; margin-right: 20px; font-weight: bold; }
    .form-container { background: white; max-width: 500px; margin: 40px auto; padding: 30px; border-radius: 12px; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
    .form-container h2 { text-align: center; margin-bottom: 20px; }
    .form-container input { width: 100%; padding: 12px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; }
    .form-container button { width: 100%; background: #FDBA24; color: black; padding: 12px; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; margin-top: 10px; }
    .form-container button:hover { background-color: #e0a61f; }
    .suivi-btn { text-align: center; margin-top: 20px; }
    .suivi-btn a { display: inline-block; background-color: #000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; }
    .suivi-btn a:hover { background-color: #333; }
    footer { text-align: center; margin-top: 40px; color: white; }
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
    <a href="logout.php" style="color:#FDBA24;">Déconnexion</a>
  </div>
</div>

<?= $message ?>

<div class="form-container">
  <h2>Publiez un trajet</h2>
  <form method="POST">
    <input type="text" name="depart" placeholder="Départ" required>
    <input type="text" name="destination" placeholder="Destination" required>
    <input type="date" name="date" required>
    <input type="time" name="heure" required>
    <input type="number" name="places" placeholder="Nombre de places" required>
    <input type="text" name="marque_voiture" placeholder="Marque de voiture" required>
    <input type="tel" name="telephone" placeholder="Téléphone" required>
    <input type="number" name="prix_par_passager" step="0.01" placeholder="Prix par passager (TND)" required>
    <button type="submit">Publier</button>
  </form>

  <!-- Bouton Suivi juste en dessous du formulaire -->
  <div class="suivi-btn">
    <a href="suivi_traject.php">📍 Suivi de mes trajets</a>
  </div>
</div>

<footer>
  &copy; 2025 Covoiturage TN
</footer>

</body>
</html>
