CREATE TABLE `utilisateurs` (
  `cin` INT(8) NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(50) NOT NULL,
  `prenom` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `telephone` VARCHAR(20) NOT NULL,
  `photo_pdp` VARCHAR(255) NOT NULL,
  `photo_voiture` VARCHAR(255),
  PRIMARY KEY (`cin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
