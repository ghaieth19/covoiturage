<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Choix de l'Espace</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: url('background1.jpg') no-repeat center center fixed;
      background-size: cover;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .signup-container {
      background-color: rgba(255, 255, 255, 0.95);
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 400px;
      text-align: center;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .signup-container h1 {
      margin-bottom: 10px;
      font-size: 28px;
      color: #f9c700;
    }

    .signup-container p {
      margin-bottom: 30px;
      font-size: 16px;
      color: #555;
    }

    .form-group {
      margin-bottom: 20px;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #f3ab25;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #e0c00f;
    }

    .login-link {
      margin-top: 25px;
      font-size: 14px;
    }

    .login-link a {
      color: #ffbb00;
      text-decoration: none;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    /* Mode nuit */
    .dark-mode {
      background-color: #121212;
      background-image: none;
    }

    .dark-mode .signup-container {
      background-color: rgba(30, 30, 30, 0.95);
      color: #f0f0f0;
    }

    .dark-mode .signup-container h1 {
      color: #ffd700;
    }

    .dark-mode button {
      background-color: #444;
      color: #fff;
    }

    .dark-mode button:hover {
      background-color: #666;
    }

    .dark-mode .login-link a {
      color: #ffd700;
    }

    /* Petit bouton rond pour le dark mode */
    .mode-toggle {
      position: absolute;
      top: 15px;
      right: 15px;
      width: 40px;
      height: 40px;
      border: none;
      border-radius: 50%;
      background-color: #f3ab25;
      color: white;
      font-size: 18px;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      display: flex;
      justify-content: center;
      align-items: center;
      transition: background-color 0.3s ease;
    }

    .dark-mode .mode-toggle {
      background-color: #ffd700;
      color: #000;
    }
  </style>
</head>
<body>
  <!-- Bouton toggle dark mode -->
  <button class="mode-toggle" onclick="toggleDarkMode()" title="Mode Nuit">🌙</button>

  <div class="signup-container">
    <h1>Bienvenue</h1>
    <p>Choisissez votre espace :</p>

    <div class="form-group">
      <button onclick="location.href='logadmin.php'">Espace Admin</button>
    </div>

    <div class="form-group">
      <button onclick="location.href='couvoiturage.php'">Espace Utilisateur</button>
    </div>

    <div class="login-link">
      <a href="login.html">🔙 Retour à l'Accueil</a>
    </div>
  </div>

  <script>
    function toggleDarkMode() {
      const body = document.body;
      const toggleBtn = document.querySelector(".mode-toggle");
      body.classList.toggle("dark-mode");

      if (body.classList.contains("dark-mode")) {
        toggleBtn.textContent = "☀️";
        toggleBtn.title = "Mode Jour";
      } else {
        toggleBtn.textContent = "🌙";
        toggleBtn.title = "Mode Nuit";
      }
    }
  </script>
</body>
</html>
