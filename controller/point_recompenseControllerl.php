<?php
require_once __DIR__ . '/../models/PointsRecompense.php';

class PointsRecompenseController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function reserver($utilisateurCin, $publicationId) {
        $reward = new PointsRecompense($this->pdo, $utilisateurCin, $publicationId);
        return $reward->reserverTrajet();
    }
}
