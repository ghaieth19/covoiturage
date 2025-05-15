<?php
require_once __DIR__ . '/../model/Avis.php'; // Assure-toi que le chemin est correct

class AvisController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer tous les avis
    public function getAllAvis() {
        $avis = new Avis($this->conn);
        $result = $avis->getAll();
        return $result;
    }

    // Mettre à jour la réponse d'un avis
    public function updateResponse($id, $reponse) {
        $avis = new Avis($this->conn);
        $avis->setId($id);  // Utilise le setter pour définir l'ID
        $avis->setReponse($reponse);  // Utilise le setter pour définir la réponse
        $result = $avis->updateResponse($id, $reponse);
        
        return $result ? 
            ['success' => true, 'message' => "✅ Réponse mise à jour avec succès !"] : 
            ['success' => false, 'message' => "❌ Erreur lors de la mise à jour de la réponse."];
    }
}
?>
