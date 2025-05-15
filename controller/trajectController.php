<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/publication.php'; // Assure-toi que tu as bien ce fichier Trajet.php

class TrajetController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function publierTrajet(Trajet $trajet, $cin) {
        // Validation des champs requis
        $requiredFields = ['depart', 'destination', 'date', 'heure', 'places', 'marque_voiture', 'telephone', 'prix_par_passager'];
        
        foreach ($requiredFields as $field) {
            if (empty($trajet->{$field})) {
                return [
                    'success' => false,
                    'message' => "❌ Le champ '" . ucfirst(str_replace('_', ' ', $field)) . "' est requis."
                ];
            }
        }

        try {
            $stmt = $this->conn->prepare("
                INSERT INTO publications (
                    utilisateur_cin, depart, destination, date, heure, places, marque_voiture, telephone, prix_par_passager
                ) VALUES (
                    :cin, :depart, :destination, :date, :heure, :places, :marque_voiture, :telephone, :prix_par_passager
                )
            ");

            // Utilisation des getters pour récupérer les valeurs
            $stmt->execute([
                ':cin' => $cin,
                ':depart' => $trajet->getDepart(),
                ':destination' => $trajet->getDestination(),
                ':date' => $trajet->getDate(),
                ':heure' => $trajet->getHeure(),
                ':places' => $trajet->getPlaces(),
                ':marque_voiture' => $trajet->getMarqueVoiture(),
                ':telephone' => $trajet->getTelephone(),
                ':prix_par_passager' => $trajet->getPrixParPassager()
            ]);

            return [
                'success' => true,
                'message' => "✅ Trajet publié avec succès !"
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => "❌ Erreur : " . $e->getMessage()
            ];
        }
    }
}
?>
