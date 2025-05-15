<?php
// Connexion à la base de données
$host = "localhost";
$dbname = "covoiturage";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si l'ID et l'action sont présents dans la requête
    if (isset($_POST['id']) && $_POST['action'] == 'supprimer') {
        $id = $_POST['id'];

        // Suppression de la publication
        $sql = "DELETE FROM publications WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirection vers la page de gestion avec un message de succès
        header('Location: index.php?success=1');
        exit();
    }
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
