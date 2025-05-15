<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Covoiturage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background: #f0f2f5;
            overflow: hidden;
            transition: background 0.3s, color 0.3s;
        }
        .dark-mode { background: #1c1c1c; color: #f1f1f1; }
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #ffbb00, #e0a800);
            padding: 20px;
            display: flex;
            flex-direction: column;
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            transition: background 0.3s;
        }
        .dark-mode .sidebar { background: linear-gradient(135deg, #444, #333); }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 28px;
            letter-spacing: 1px;
        }
        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 15px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: background 0.4s;
            display: flex;
            align-items: center;
        }
        .sidebar a:hover { background: rgba(255, 255, 255, 0.2); }
        .logout { margin-top: auto; text-align: center; }
        .content {
            flex-grow: 1;
            padding: 40px;
            overflow-y: auto;
            animation: fadeIn 0.8s ease-in-out;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 30px;
            transition: background 0.3s, color 0.3s;
        }
        .dark-mode .dashboard-card {
            background: #2c2c2c;
            color: #f1f1f1;
        }
        .dashboard-card h3 {
            color: #4b4a5c;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .dark-mode .dashboard-card h3 { color: #ffc107; }
        .approve-btn {
            background: #ffbb00;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 16px;
        }
        .approve-btn:hover { background: #e0a800; }

        /* Style pour le bouton Gérer les commentaires */
        .manage-comments-btn {
            background: #ffbb00;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 16px;
        }
        .manage-comments-btn:hover { background: #e0a800; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .message-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .dark-mode .message-box {
            background: #333;
            color: #f1f1f1;
        }
        .dark-mode-toggle {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 14px;
            cursor: pointer;
            z-index: 1000;
            transition: background 0.3s;
        }
        .dark-mode-toggle:hover { background: #666; }
    </style>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙</button>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <a href="#stats">📊 Statistiques</a>
        <a href="#users">👤 Gestion des Utilisateurs</a>
        <a href="#rides">🚗 Validation des Publications</a>
        <a href="#commentaire">💬commentaire</a>
        <a href="#referral">👥 Parrainer un Ami</a>
        <a href="#progress">📈 Suivre la Progression</a>
        <a href="#contact">📬 Contact</a>
        <div class="logout">
            <a href="acceuil.php">🚪 Déconnexion</a>
        </div>
    </div>

    <div class="content">
        <section id="stats" class="dashboard-card stats-grid">
            <div>
                <h3>📈 Chiffres Clés</h3>
                <p>Total des utilisateurs : <strong>1200</strong></p>
                <p>Publications en attente : <strong>45</strong></p>
                <p>Revenus générés : <strong>2500,000 TND</strong></p>
            </div>
        </section>

        <section id="users" class="dashboard-card">
            <h3>👤 Gestion des Utilisateurs</h3>
            <p>Voir, modifier ou supprimer des comptes utilisateurs.</p>
            <button class="approve-btn" onclick="window.location.href='view_utilisateur.php'">Gérer les utilisateurs</button>
        </section>

        <section id="rides" class="dashboard-card">
            <h3>🚗 Validation des Publications</h3>
            <p>Valider ou rejeter les publications de covoiturage.</p>
            <button class="approve-btn" onclick="location.href='gerer_pub.php'">Gérer les publications</button>
        </section>

        <section id="commentaire" class="dashboard-card">
            <h3>💬 Gérer les Commentaires des Publications</h3>
            <p>Modérez, supprimez et visualisez les commentaires laissés sur vos publications.</p>
            <button class="manage-comments-btn" onclick="location.href='commentaire_admin.php'">Gérer les commentaires</button>
        </section>

        <section id="referral" class="dashboard-card">
            <h3>👥 Parrainer un Ami</h3>
            <p>Invitez vos amis à rejoindre l'application et gagnez des récompenses.</p>
            <button class="approve-btn" onclick="location.href='paraignage admin.php'">Inviter un ami</button>
        </section>

        <section id="progress" class="dashboard-card">
            <h3>📈 Suivre la Progression</h3>
            <p>Suivez l'avancement des recommandations et actions des utilisateurs.</p>
            <button class="approve-btn" onclick="location.href='objectif admin.php'">Voir la progression</button>
        </section>

        <section id="contact" class="dashboard-card">
            <h3>📬 Contact</h3>
            <p>Consultez les messages reçus des utilisateurs et répondez-y.</p>
            <button class="approve-btn" onclick="location.href='afficher reclamation.php'">Gérer les messages</button>
        </section>
    </div>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle("dark-mode");
        }
    </script>
</body>
</html>