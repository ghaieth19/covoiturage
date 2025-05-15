<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #000000;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #000000;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            color: #fdfdfd;
        }

        h2 {
            text-align: center;
            color: #fdfdfd;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #040404;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
            font-size: 1rem;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            background-color: #dd9b02;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e4b50c;
        }

        .toggle-link {
            text-align: center;
            margin-top: 10px;
        }

        .toggle-link a {
            color: #007bff;
            text-decoration: none;
        }

        .toggle-link a:hover {
            text-decoration: underline;
        }

        .footer {
            background: #333333;
            color: white;
            text-align: center;
            padding: 1rem;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .footer p {
            margin: 0;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        <form id="admin-login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <div class="error-message" id="error-message"></div>
        </form>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Your Company. All rights reserved.</p>
    </footer>

    <script>
        // Informations d'identification simulées (à remplacer par une logique réelle côté serveur)
        const ADMIN_USERNAME = "admin";
        const ADMIN_PASSWORD = "admin123";

        // Gestion de la soumission du formulaire
        document.getElementById('admin-login-form').addEventListener('submit', function (event) {
            event.preventDefault(); // Empêche la soumission par défaut

            // Réinitialiser le message d'erreur
            document.getElementById('error-message').textContent = '';

            // Récupérer les valeurs des champs
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            // Validation des champs
            if (!username || !password) {
                showError('Veuillez remplir tous les champs.');
                return;
            }

            // Vérifier les informations d'identification
            if (username === ADMIN_USERNAME && password === ADMIN_PASSWORD) {
                // Rediriger vers la page admin.html
                window.location.href = 'admin.php';
            } else {
                showError('Nom d\'utilisateur ou mot de passe incorrect.');
            }
        });

        // Fonction pour afficher un message d'erreur
        function showError(message) {
            const errorElement = document.getElementById('error-message');
            errorElement.textContent = message;
        }
    </script>
</body>
</html>