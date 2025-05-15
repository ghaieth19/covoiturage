<?php
session_start();

$erreur = "";
$succes = "";

// Vérifie que l'utilisateur parrain est connecté
if (!isset($_SESSION['cin'])) {
    echo "Vous devez être connecté pour parrainer un utilisateur.";
    exit();
}

$parrain_cin = $_SESSION['cin']; // CIN du parrain connecté

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $connexion = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8", "root", "");
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $cin = $_POST["cin"];
        $nom = $_POST["nom"];
        $prenom = $_POST["prenom"];
        $email = $_POST["email"];
        $mot_de_passe = password_hash($_POST["motdepasse"], PASSWORD_BCRYPT);
        $telephone = $_POST["telephone"];

        $repertoire_telechargement = "../uploads/";
        if (!is_dir($repertoire_telechargement)) {
            mkdir($repertoire_telechargement, 0755, true);
        }

        $photo_pdp = null;
        if (isset($_FILES["photo_pdp"]) && $_FILES["photo_pdp"]["error"] === UPLOAD_ERR_OK) {
            $nom_photo_pdp = uniqid() . "_" . basename($_FILES["photo_pdp"]["name"]);
            $chemin_photo_pdp = $repertoire_telechargement . $nom_photo_pdp;
            if (move_uploaded_file($_FILES["photo_pdp"]["tmp_name"], $chemin_photo_pdp)) {
                $photo_pdp = $nom_photo_pdp;
            } else {
                $erreur = "Erreur lors de l’enregistrement de la photo de profil.";
            }
        } else {
            $erreur = "La photo de profil est obligatoire.";
        }

        $photo_voiture = null;
        if (isset($_FILES["photo_voiture"]) && $_FILES["photo_voiture"]["error"] === UPLOAD_ERR_OK) {
            $nom_photo_voiture = uniqid() . "_" . basename($_FILES["photo_voiture"]["name"]);
            $chemin_photo_voiture = $repertoire_telechargement . $nom_photo_voiture;
            if (move_uploaded_file($_FILES["photo_voiture"]["tmp_name"], $chemin_photo_voiture)) {
                $photo_voiture = $nom_photo_voiture;
            }
        }

        if (empty($erreur)) {
            $sql_insert = "INSERT INTO utilisateurs (cin, nom, prenom, email, mot_de_passe, telephone, photo_pdp, photo_voiture, parrain_cin)
                           VALUES (:cin, :nom, :prenom, :email, :motdepasse, :telephone, :photo_pdp, :photo_voiture, :parrain_cin)";

            $stmt_insert = $connexion->prepare($sql_insert);
            $stmt_insert->bindParam(':cin', $cin);
            $stmt_insert->bindParam(':nom', $nom);
            $stmt_insert->bindParam(':prenom', $prenom);
            $stmt_insert->bindParam(':email', $email);
            $stmt_insert->bindParam(':motdepasse', $mot_de_passe);
            $stmt_insert->bindParam(':telephone', $telephone);
            $stmt_insert->bindParam(':photo_pdp', $photo_pdp);
            $stmt_insert->bindParam(':photo_voiture', $photo_voiture);
            $stmt_insert->bindParam(':parrain_cin', $parrain_cin);
            $stmt_insert->execute();

            // Ajouter 150 points au parrain
            $points_a_ajouter = 150;
            $sql_update_parrain = "UPDATE utilisateurs SET points_parrainage = points_parrainage + :points WHERE cin = :parrain_cin";
            $stmt_update_parrain = $connexion->prepare($sql_update_parrain);
            $stmt_update_parrain->bindParam(':points', $points_a_ajouter, PDO::PARAM_INT);
            $stmt_update_parrain->bindParam(':parrain_cin', $parrain_cin);
            $stmt_update_parrain->execute();

            $succes = "✅ Partenaire ajouté avec succès.";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $erreur = "❌ Un partenaire avec ce CIN existe déjà.";
        } else {
            $erreur = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Parrainage Utilisateur</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4; /* Fond gris clair pour le contenu */
            color: #333; /* Texte sombre pour le contenu */
            display: flex;
            flex-direction: column; /* Pour placer le header en haut */
            align-items: center; /* Centrer le contenu horizontalement */
            min-height: 100vh;
            margin: 0;
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

        .container {
            background: white; /* Fond blanc pour le container */
            color: #333; /* Texte sombre pour le container */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 500px;
            margin-top: 20px; /* Espacement entre le header et le container */
        }

        h2 {
            color: #222;
            font-size: 26px;
            margin-bottom: 20px;
        }

        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            background-color: #222;
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.3s ease;
            font-weight: bold;
        }

        button:hover {
            background-color: #444;
        }

        label {
            text-align: left;
            display: block;
            margin-top: 10px;
            color: #333;
        }

        .team-button {
            margin-top: 20px;
        }

        .message {
            font-weight: bold;
        }

        .erreur { color: red; }
        .succes { color: green; }
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
        <h2>📝 Parrainer un ami</h2>

        <?php if ($erreur): ?>
            <p class="message erreur"><?= $erreur ?></p>
        <?php endif; ?>

        <?php if ($succes): ?>
            <p class="message succes"><?= $succes ?></p>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="text" name="cin" placeholder="CIN" required>
            <input type="email" name="email" placeholder="" value="">
<input type="password" name="motdepasse" placeholder="*********" value="">
            <input type="tel" name="telephone" placeholder="Téléphone" required>

            <label>Photo de voiture (optionnelle) :</label>
            <div style="display: flex; align-items: center; border: 1px solid #ccc; border-radius: 8px; padding: 8px;">
                <label for="photo_voiture" style="margin-right: 10px; margin-bottom: 0; color: #222; cursor: pointer;">Choisir un fichier</label>
                <input type="file" id="photo_voiture" name="photo_voiture" accept="image/*" style="display: none;">
                <span id="photo_voiture_label">Aucun fichier choisi</span>
            </div>

            <label>Photo de profil (obligatoire) :</label>
            <div style="display: flex; align-items: center; border: 1px solid #ccc; border-radius: 8px; padding: 8px;">
                <label for="photo_pdp" style="margin-right: 10px; margin-bottom: 0; color: #222; cursor: pointer;">Choisir un fichier</label>
                <input type="file" id="photo_pdp" name="photo_pdp" accept="image/*" required style="display: none;">
                <span id="photo_pdp_label">Aucun fichier choisi</span>
            </div>

            <button type="submit" style="margin-top: 20px; padding: 15px; font-size: 18px;">✅ Ajouter à mon équipe</button>
        </form>

        <div class="team-button">
            <button onclick="window.location.href='mon equipe.php'" style="padding: 12px; font-size: 16px;">👥 Mon Équipe</button>
        </div>
    </div>

    <script>
        document.querySelector("form").addEventListener("submit", function(e) {
            let formulaireValide = true;
            let messages = [];

            const nom = document.querySelector('input[name="nom"]').value.trim();
            const prenom = document.querySelector('input[name="prenom"]').value.trim();
            const cin = document.querySelector('input[name="cin"]').value.trim();
            const email = document.querySelector('input[name="email"]').value.trim();
            const motdepasse = document.querySelector('input[name="motdepasse"]').value;
            const telephone = document.querySelector('input[name="telephone"]').value.trim();
            const photoPdpInput = document.querySelector('input[name="photo_pdp"]');
            const photoPdp = photoPdpInput.files[0];

            const texteSeulement = str => /^[A-Za-zÀ-ÿ\s\-']+$/.test(str);
            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            // Vérifie nom et prénom
            if (!texteSeulement(nom)) {
                formulaireValide = false;
                messages.push("❌ Le nom ne doit contenir que des lettres.");
            }
            if (!texteSeulement(prenom)) {
                formulaireValide = false;
                messages.push("❌ Le prénom ne doit contenir que des lettres.");
            }

            // Vérifie CIN
            if (!/^\d{8}$/.test(cin)) {
                formulaireValide = false;
                messages.push("❌ Le CIN doit contenir exactement 8 chiffres.");
            }

            // Vérifie email (bien qu'il soit en lecture seule)
            if (!regexEmail.test(email)) {
                formulaireValide = false;
                messages.push("❌ L'adresse email est invalide.");
            }

            // Vérifie téléphone
            if (!/^\d{8}$/.test(telephone)) {
                formulaireValide = false;
                messages.push("❌ Le numéro de téléphone doit contenir 8 chiffres.");
            }

            // Vérifie mot de passe (bien qu'il soit en lecture seule)
            if (motdepasse.length < 6) {
                formulaireValide = false;
                messages.push("❌ Le mot de passe doit contenir au moins 6 caractères.");
            }

            // Vérifie photo de profil obligatoire
            if (!photoPdp) {
                formulaireValide = false;
                messages.push("❌ La photo de profil est obligatoire.");
            } else {
                const typesValides = ['image/jpeg', 'image/png', 'image/gif'];
                if (!typesValides.includes(photoPdp.type)) {
                    formulaireValide = false;
                    messages.push("❌ La photo de profil doit être une image (jpg, png ou gif).");
                }
            }

            if (!formulaireValide) {
                e.preventDefault();
                alert(messages.join("\n"));
            }
        });

        // Met à jour le nom du fichier sélectionné
        document.getElementById('photo_voiture').addEventListener('change', function() {
            const label = document.getElementById('photo_voiture_label');
            if (this.files && this.files.length > 0) {
                label.textContent = this.files[0].name;
            } else {
                label.textContent = 'Aucun fichier choisi';
            }
        });

        document.getElementById('photo_pdp').addEventListener('change', function() {
            const label = document.getElementById('photo_pdp_label');
            if (this.files && this.files.length > 0) {
                label.textContent = this.files[0].name;
            } else {
                label.textContent = 'Aucun fichier choisi';
            }
        });
    </script>

</body>
</html>