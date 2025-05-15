<?php 
// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Message de feedback
$message = '';

// Remise à zéro des points
if (isset($_GET['supprimer'])) {
    $cin = $_GET['supprimer'];
    $stmt = $pdo->prepare("UPDATE utilisateurs SET points_parrainage = 0, points_traject = 0 WHERE cin = ?");
    if ($stmt->execute([$cin])) {
        $message = "Les points de l'utilisateur avec CIN $cin ont été remis à zéro.";
    } else {
        $message = "Erreur lors de la remise à zéro des points.";
    }
}

// Modification d’un utilisateur
if (isset($_POST['modifier'])) {
    $cin = $_POST['cin'];
    $points_parrainage = $_POST['points_parrainage'];
    $points_traject = $_POST['points_traject'];

    $stmt = $pdo->prepare("UPDATE utilisateurs SET points_parrainage = ?, points_traject = ? WHERE cin = ?");
    if ($stmt->execute([$points_parrainage, $points_traject, $cin])) {
        $message = "Utilisateur avec CIN $cin modifié avec succès.";
    } else {
        $message = "Erreur lors de la modification.";
    }
}

// Ajout manuel d’un chèque promo (sans condition de points)
if (isset($_GET['ajouterPromo'])) {
    $cin = $_GET['ajouterPromo'];
    $stmt = $pdo->prepare("SELECT cheques FROM utilisateurs WHERE cin = ?");
    $stmt->execute([$cin]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nouveauCheques = $user['cheques'] + 1;
        $stmt = $pdo->prepare("UPDATE utilisateurs SET cheques = ? WHERE cin = ?");
        if ($stmt->execute([$nouveauCheques, $cin])) {
            $message = "✅ 1 chèque promo a été ajouté manuellement pour l'utilisateur CIN $cin.";
        } else {
            $message = "❌ Erreur lors de l'ajout du chèque promo.";
        }
    } else {
        $message = "Utilisateur introuvable.";
    }
}

// Récupération des utilisateurs
$stmt = $pdo->query("SELECT nom, prenom, cin, telephone, points_parrainage, points_traject, cheques FROM utilisateurs");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Points</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffbb00, #e0a800);
            padding: 30px;
        }

        .container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            max-width: 1100px;
            margin: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .message {
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        input[type="number"] {
            width: 80px;
        }

        .search-bar {
            text-align: right;
            margin-bottom: 10px;
        }

        .search-bar input {
            padding: 5px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #ffbb00;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .actions input[type="submit"], .actions a {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .actions input[type="submit"]:hover, .actions a:hover {
            transform: translateY(-2px);
        }

        .actions a {
            background-color: #dc3545;
            margin-left: 10px;
            text-decoration: none;
        }

        .actions a:hover {
            background-color: #c82333;
        }

        .actions a:nth-child(3) {
            background-color: #28a745;
        }

        .actions a:nth-child(3):hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 Gestion des Points - Admin</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="search-bar">
        <input type="text" id="searchInput" onkeyup="filterUsers()" placeholder="Rechercher...">
    </div>

    <table id="userTable">
        <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>CIN</th>
            <th>Téléphone</th>
            <th>Points Parrainage</th>
            <th>Points Trajet</th>
            <th>Total</th>
            <th>Chèques</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($utilisateurs as $u): 
            $total = $u['points_parrainage'] + $u['points_traject'];
        ?>
            <tr>
                <form method="post">
                    <td><?= htmlspecialchars($u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['prenom']) ?></td>
                    <td><input type="hidden" name="cin" value="<?= $u['cin'] ?>"><?= $u['cin'] ?></td>
                    <td><?= htmlspecialchars($u['telephone']) ?></td>
                    <td><input type="number" name="points_parrainage" value="<?= $u['points_parrainage'] ?>"></td>
                    <td><input type="number" name="points_traject" value="<?= $u['points_traject'] ?>"></td>
                    <td><strong><?= $total ?></strong></td>
                    <td><?= $u['cheques'] ?></td>
                    <td class="actions">
                        <input type="submit" name="modifier" value="Modifier">
                        <a href="?supprimer=<?= $u['cin'] ?>" onclick="return confirm('Remettre les points à zéro ?')">Supprimer</a>
                        <a href="?ajouterPromo=<?= $u['cin'] ?>" onclick="return confirm('Ajouter un chèque promo ?')">Ajouter</a>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function filterUsers() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const rows = document.querySelectorAll("#userTable tbody tr");

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            row.style.display = rowText.includes(input) ? "" : "none";
        });
    }
</script>
</body>
</html>
