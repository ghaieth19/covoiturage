<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=covoiturage", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement de la suppression de commentaire
if (isset($_GET['supprimer_commentaire_admin']) && is_numeric($_GET['supprimer_commentaire_admin'])) {
    $commentaire_id_suppr = $_GET['supprimer_commentaire_admin'];
    $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
    $stmt->execute([$commentaire_id_suppr]);
    $_SESSION['message_admin'] = "✅ Commentaire supprimé avec succès.";
    header("Location: commentaire_admin.php");
    exit();
}

// Gestion des commentaires non signalés
$searchTermNormal = $_GET['recherche_normal'] ?? '';
$sqlNormal = "SELECT c.*, u.nom AS nom_utilisateur, u.prenom AS prenom_utilisateur, p.destination AS destination_publication
              FROM commentaires c
              JOIN utilisateurs u ON c.utilisateur_cin = u.cin
              JOIN publications p ON c.publication_id = p.id
              WHERE c.est_signale = FALSE";

if (!empty($searchTermNormal)) {
    $sqlNormal .= " AND (c.commentaire LIKE :term_normal
                       OR u.nom LIKE :term_normal
                       OR u.prenom LIKE :term_normal
                       OR p.destination LIKE :term_normal)";
}

$sqlNormal .= " ORDER BY c.date_creation DESC";

$stmtNormal = $pdo->prepare($sqlNormal);
if (!empty($searchTermNormal)) {
    $stmtNormal->bindValue(':term_normal', '%' . $searchTermNormal . '%', PDO::PARAM_STR);
}
$stmtNormal->execute();
$commentairesNormaux = $stmtNormal->fetchAll(PDO::FETCH_ASSOC);

// Gestion des commentaires signalés
$searchTermSignale = $_GET['recherche_signale'] ?? '';
$sqlSignale = "SELECT c.*, u_reported.nom AS nom_signale, u_reported.prenom AS prenom_signale,
                      u_reporter.nom AS nom_signaleur, u_reporter.prenom AS prenom_signaleur,
                      p.destination AS destination_publication
               FROM commentaires c
               JOIN utilisateurs u_reported ON c.utilisateur_cin = u_reported.cin
               LEFT JOIN utilisateurs u_reporter ON c.signale_par_cin = u_reporter.cin
               JOIN publications p ON c.publication_id = p.id
               WHERE c.est_signale = TRUE";

if (!empty($searchTermSignale)) {
    $sqlSignale .= " AND (c.commentaire LIKE :term_signale
                        OR u_reported.nom LIKE :term_signale
                        OR u_reported.prenom LIKE :term_signale
                        OR u_reporter.nom LIKE :term_signale
                        OR u_reporter.prenom LIKE :term_signale
                        OR p.destination LIKE :term_signale
                        OR c.motif_signalement LIKE :term_signale)";
}

$sqlSignale .= " ORDER BY c.date_creation DESC";

$stmtSignale = $pdo->prepare($sqlSignale);
if (!empty($searchTermSignale)) {
    $stmtSignale->bindValue(':term_signale', '%' . $searchTermSignale . '%', PDO::PARAM_STR);
}
$stmtSignale->execute();
$commentairesSignales = $stmtSignale->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Commentaires et Signalements | Administration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:rgb(255, 179, 0);
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #374151;
            margin-bottom: 25px;
        }
        .section-title {
            color: #4a5568;
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .search-bar {
            display: flex;
            margin-bottom: 25px;
        }
        .search-bar input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px 0 0 6px;
            font-size: 16px;
        }
        .search-bar button {
            background-color: #4f46e5;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s ease-in-out;
        }
        .search-bar button:hover {
            background-color: #3730a3;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        .data-table th, .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        .data-table th {
            background-color: #f9fafb;
            color: #4b5563;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .data-table tbody tr:hover {
            background-color: #f3f4f6;
        }
        .delete-button {
            background-color: #dc2626;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.2s ease-in-out;
        }
        .delete-button:hover {
            background-color: #b91c1c;
        }
        .message-container {
            margin-bottom: 20px;
        }
        .message {
            padding: 12px 15px;
            border-radius: 6px;
            font-weight: 500;
        }
        .success {
            background-color: #d1fae5;
            color: #10b981;
            border: 1px solid #a7f3d0;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestion des Commentaires et Signalements</h2>

        <div class="message-container">
            <?php if (isset($_SESSION['message_admin'])): ?>
                <div class="message success">
                    <?php echo $_SESSION['message_admin']; unset($_SESSION['message_admin']); ?>
                </div>
            <?php endif; ?>
        </div>

        <h3 class="section-title">Gestion des Commentaires</h3>
        <form class="search-bar" method="get" action="">
            <input type="text" name="recherche_normal" placeholder="Rechercher un commentaire, utilisateur ou trajet" value="<?php echo htmlspecialchars($searchTermNormal); ?>">
            <button type="submit">Rechercher</button>
        </form>

        <?php if (!empty($commentairesNormaux)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Publication</th>
                        <th>Utilisateur</th>
                        <th>Commentaire</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commentairesNormaux as $commentaire): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($commentaire['id']); ?></td>
                            <td><?php echo htmlspecialchars($commentaire['destination_publication']); ?> (ID: <?php echo htmlspecialchars($commentaire['publication_id']); ?>)</td>
                            <td><?php echo htmlspecialchars($commentaire['prenom_utilisateur']) . ' ' . htmlspecialchars($commentaire['nom_utilisateur']); ?> (CIN: <?php echo htmlspecialchars($commentaire['utilisateur_cin']); ?>)</td>
                            <td><?php echo htmlspecialchars($commentaire['commentaire']); ?></td>
                            <td><?php echo htmlspecialchars($commentaire['date_creation']); ?></td>
                            <td>
                                <a href="commentaire_admin.php?supprimer_commentaire_admin=<?php echo htmlspecialchars($commentaire['id']); ?>" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">Aucun commentaire trouvé.</p>
        <?php endif; ?>

        <h3 class="section-title">Commentaires Signalés</h3>
        <form class="search-bar" method="get" action="">
            <input type="text" name="recherche_signale" placeholder="Rechercher dans les commentaires signalés" value="<?php echo htmlspecialchars($searchTermSignale); ?>">
            <button type="submit">Rechercher</button>
        </form>

        <?php if (!empty($commentairesSignales)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Publication</th>
                        <th>Auteur</th>
                        <th>Commentaire</th>
                        <th>Signalé par</th>
                        <th>Motif</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commentairesSignales as $commentaire): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($commentaire['id']); ?></td>
                            <td><?php echo htmlspecialchars($commentaire['destination_publication']); ?> (ID: <?php echo htmlspecialchars($commentaire['publication_id']); ?>)</td>
                            <td><?php echo htmlspecialchars($commentaire['prenom_signale']) . ' ' . htmlspecialchars($commentaire['nom_signale']); ?> (CIN: <?php echo htmlspecialchars($commentaire['utilisateur_cin']); ?>)</td>
                            <td><?php echo htmlspecialchars($commentaire['commentaire']); ?></td>
                            <td><?php echo htmlspecialchars($commentaire['prenom_signaleur'] ?? 'Inconnu') . ' ' . htmlspecialchars($commentaire['nom_signaleur'] ?? ''); ?> (CIN: <?php echo htmlspecialchars($commentaire['signale_par_cin'] ?? 'N/A'); ?>)</td>
                            <td><?php echo htmlspecialchars($commentaire['motif_signalement']); ?></td>
                            <td><?php echo htmlspecialchars($commentaire['date_creation']); ?></td>
                            <td>
                                <a href="commentaire_admin.php?supprimer_commentaire_admin=<?php echo htmlspecialchars($commentaire['id']); ?>" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire signalé ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">Aucun commentaire signalé pour le moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>