<?php
// Connexion à la base de données
$dsn = "mysql:host=localhost;dbname=covoiturage;charset=utf8mb4";
$username = "root";
$password = "";
$message = "";

try {
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Suppression utilisateur
if (isset($_GET['delete'])) {
    $cin = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE cin = ?");
    if ($stmt->execute([$cin])) {
        $message = "Utilisateur supprimé avec succès.";
    }
}

// Modification utilisateur
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {
    $cin = $_POST['cin'];
    $photoPath = null;

    if (!empty($_FILES['photo_pdp']['name'])) {
        $uploadDir = "../uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $photoName = basename($_FILES['photo_pdp']['name']);
        $targetPath = $uploadDir . time() . "_" . $photoName;

        if (move_uploaded_file($_FILES['photo_pdp']['tmp_name'], $targetPath)) {
            $photoPath = $targetPath;
        }
    }

    if ($photoPath) {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=?, telephone=?, photo_pdp=? WHERE cin=?");
        $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['telephone'], $photoPath, $cin]);
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, prenom=?, email=?, telephone=? WHERE cin=?");
        $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['telephone'], $cin]);
    }

    $message = "Informations mises à jour avec succès.";
}

// Récupération des utilisateurs
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('background1.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            transition: background 0.3s, color 0.3s;
        }

        .dark-mode {
            background-color: #121212 !important;
            color: #f1f1f1;
        }

        .dark-mode .signup-container {
            background-color: #1e1e1e;
            color: #f1f1f1;
        }

        .signup-container {
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 95%;
            max-width: 1200px;
            overflow-x: auto;
        }

        h2 {
            text-align: center;
            color: #f9c700;
        }

        .message {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }

        input[type="text"], input[type="email"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .dark-mode input {
            background-color: #333;
            color: #fff;
            border: 1px solid #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .dark-mode th {
            background-color: #2a2a2a;
        }

        img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }

        .actions a,
        .actions button {
            padding: 8px;
            border: none;
            border-radius: 5px;
            margin: 2px;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
        }

        .edit {
            background-color: #28a745;
        }

        .delete {
            background-color: #dc3545;
        }

        .btn-back {
            display: inline-block;
            padding: 10px 15px;
            background: #f3ab25;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
        }

        .dark-mode .modal-content {
            background: #2a2a2a;
            color: white;
        }

        .close {
            float: right;
            font-size: 20px;
            cursor: pointer;
        }

        .dark-toggle {
            position: fixed;
            top: 12px;
            right: 12px;
            z-index: 1000;
            background: #f9c700;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        #searchInput {
            padding: 10px;
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            display: block;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .dark-mode #searchInput {
            background-color: #2a2a2a;
            color: white;
            border: 1px solid #555;
        }
    </style>
</head>
<body>

<button class="dark-toggle" onclick="toggleDarkMode()">🌙</button>

<div class="signup-container">
    <h2>Liste des utilisateurs inscrits</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <input type="text" id="searchInput" placeholder="Rechercher un utilisateur..." oninput="filterUsers()">
    <table>
        <thead>
            <tr>
                <th>CIN</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Profil</th>
                <th>Voiture</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="userTable">
            <?php foreach ($utilisateurs as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['cin']) ?></td>
                    <td><?= htmlspecialchars($u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['prenom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['telephone']) ?></td>
                    <td><img src="../uploads/<?= htmlspecialchars(basename($u['photo_pdp'])) ?>" alt="Profil"></td>
                    <td><img src="../uploads/<?= htmlspecialchars(basename($u['photo_voiture'])) ?>" alt="Voiture"></td>
                    <td class="actions">
                        <button class="edit" onclick='openModal(<?= json_encode($u) ?>)'>Modifier</button>
                        <a href="?delete=<?= $u['cin'] ?>" class="delete" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="admin.html" class="btn-back">Retour</a>
</div>

<!-- Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="cin" id="cin">
            <label>Nom:</label><input type="text" name="nom" id="nom"><br>
            <label>Prénom:</label><input type="text" name="prenom" id="prenom"><br>
            <label>Email:</label><input type="email" name="email" id="email"><br>
            <label>Téléphone:</label><input type="text" name="telephone" id="telephone"><br>
            <label>Photo de profil:</label><input type="file" name="photo_pdp"><br>
            <input type="submit" name="update" value="Mettre à jour">
        </form>
    </div>
</div>

<script>
    function openModal(user) {
        document.getElementById('cin').value = user.cin;
        document.getElementById('nom').value = user.nom;
        document.getElementById('prenom').value = user.prenom;
        document.getElementById('email').value = user.email;
        document.getElementById('telephone').value = user.telephone;
        document.getElementById('editModal').style.display = "flex";
    }

    function closeModal() {
        document.getElementById('editModal').style.display = "none";
    }

    function filterUsers() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("#userTable tr");
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(input) ? "" : "none";
        });
    }

    function toggleDarkMode() {
        document.body.classList.toggle("dark-mode");
    }
</script>

</body>
</html>
