<?php
// models/Avis.php
class Avis {
    private $conn;
    private $table = "avis";

    // Déclaration des propriétés
    private $id;
    private $utilisateur_nom;
    private $message;
    private $date_avis;
    private $reponse;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Getter et Setter pour l'ID
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    // Getter et Setter pour le nom de l'utilisateur
    public function getUtilisateurNom() {
        return $this->utilisateur_nom;
    }

    public function setUtilisateurNom($utilisateur_nom) {
        $this->utilisateur_nom = $utilisateur_nom;
    }

    // Getter et Setter pour le message
    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    // Getter et Setter pour la date de l'avis
    public function getDateAvis() {
        return $this->date_avis;
    }

    public function setDateAvis($date_avis) {
        $this->date_avis = $date_avis;
    }

    // Getter et Setter pour la réponse
    public function getReponse() {
        return $this->reponse;
    }

    public function setReponse($reponse) {
        $this->reponse = $reponse;
    }

    // Récupérer tous les avis
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY date_avis DESC";
        $result = $this->conn->query($query);
        return $result;
    }

    // Mettre à jour la réponse d'un avis
    public function updateResponse($id, $reponse) {
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET reponse = ? WHERE id = ?");
        $stmt->bind_param("si", $reponse, $id);
        return $stmt->execute();
    }
}
?>
