<?php
class Database {
    private $host = "localhost";
    private $db_name = "covoiturage"; // Remplace par le nom de ta base
    private $username = "root"; // Remplace par ton utilisateur
    private $password = ""; // Remplace par ton mot de passe si nécessaire
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
