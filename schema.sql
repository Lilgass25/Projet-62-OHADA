-- =====================================================================
-- PROJET 62 - Plateforme de gestion juridique des sociétés OHADA (AUSCGIE)
-- et des formalités RCCM
-- Master CCA - ESP Dakar - M. Ousmane LY
-- Base de données : MySQL 8+ (compatible XAMPP)
-- =====================================================================

DROP DATABASE IF EXISTS projet62_ohada;
CREATE DATABASE projet62_ohada
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE projet62_ohada;

-- ---------------------------------------------------------------------
-- Table 1 : utilisateurs
-- Comptes d'accès à la plateforme (3 rôles obligatoires)
-- ---------------------------------------------------------------------
CREATE TABLE utilisateurs (
    id_utilisateur      INT AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(100)  NOT NULL,
    prenom              VARCHAR(100)  NOT NULL,
    email               VARCHAR(150)  NOT NULL UNIQUE,
    mot_de_passe        VARCHAR(255)  NOT NULL,           -- password_hash()
    role                ENUM('administrateur','juriste','consultant') NOT NULL DEFAULT 'consultant',
    -- administrateur = accès total (utilisateur avancé = juriste, standard = consultant)
    statut              ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    derniere_connexion  DATETIME NULL,
    date_creation       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 2 : societes  (entité principale)
-- ---------------------------------------------------------------------
CREATE TABLE societes (
    id_societe            INT AUTO_INCREMENT PRIMARY KEY,
    raison_sociale         VARCHAR(200) NOT NULL,
    sigle                  VARCHAR(50)  NULL,
    forme_juridique         ENUM('SARL','SARLU','SA','SAS','SASU','GIE','SNC','SCS') NOT NULL,
    capital_social          DECIMAL(18,2) NOT NULL DEFAULT 0,
    devise                  VARCHAR(10) NOT NULL DEFAULT 'FCFA',
    siege_social            VARCHAR(255) NOT NULL,
    ninea                   VARCHAR(20)  NULL,
    numero_rccm             VARCHAR(50)  NULL,
    date_immatriculation    DATE NULL,
    objet_social            TEXT NULL,
    duree_annees            INT NULL DEFAULT 99,
    statut                  ENUM('active','en_formation','dissoute','radiee') NOT NULL DEFAULT 'en_formation',
    id_utilisateur_creation  INT NOT NULL,
    date_creation            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification        DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_societe_createur FOREIGN KEY (id_utilisateur_creation)
        REFERENCES utilisateurs(id_utilisateur) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 3 : associes
-- Registre des actionnaires / associés d'une société
-- ---------------------------------------------------------------------
CREATE TABLE associes (
    id_associe        INT AUTO_INCREMENT PRIMARY KEY,
    id_societe        INT NOT NULL,
    type_personne     ENUM('physique','morale') NOT NULL DEFAULT 'physique',
    nom_denomination  VARCHAR(200) NOT NULL,
    piece_identite    VARCHAR(50)  NULL,      -- CNI / RCCM si personne morale
    nationalite       VARCHAR(80)  NULL,
    adresse           VARCHAR(255) NULL,
    nombre_parts      INT NOT NULL DEFAULT 0,
    valeur_nominale    DECIMAL(18,2) NOT NULL DEFAULT 0,
    date_entree        DATE NOT NULL,
    date_sortie         DATE NULL,
    statut               ENUM('actif','sorti') NOT NULL DEFAULT 'actif',
    CONSTRAINT fk_associe_societe FOREIGN KEY (id_societe)
        REFERENCES societes(id_societe) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 4 : mouvements_capital
-- Historique des cessions, souscriptions, donations de parts/actions
-- ---------------------------------------------------------------------
CREATE TABLE mouvements_capital (
    id_mouvement     INT AUTO_INCREMENT PRIMARY KEY,
    id_societe       INT NOT NULL,
    id_associe       INT NOT NULL,
    type_mouvement   ENUM('souscription','cession','donation','succession','rachat') NOT NULL,
    nombre_parts     INT NOT NULL,
    montant          DECIMAL(18,2) NULL,
    date_mouvement   DATE NOT NULL,
    beneficiaire     VARCHAR(200) NULL,       -- si cession vers un tiers non encore associé
    observations     TEXT NULL,
    id_utilisateur   INT NOT NULL,
    date_creation    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mouvement_societe FOREIGN KEY (id_societe)
        REFERENCES societes(id_societe) ON DELETE CASCADE,
    CONSTRAINT fk_mouvement_associe FOREIGN KEY (id_associe)
        REFERENCES associes(id_associe) ON DELETE CASCADE,
    CONSTRAINT fk_mouvement_utilisateur FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id_utilisateur) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 5 : dirigeants
-- Administrateurs, gérants, PDG, Commissaires aux comptes
-- ---------------------------------------------------------------------
CREATE TABLE dirigeants (
    id_dirigeant      INT AUTO_INCREMENT PRIMARY KEY,
    id_societe        INT NOT NULL,
    nom_complet       VARCHAR(200) NOT NULL,
    fonction          ENUM('Gerant','PDG','DG','Administrateur','President_CA','CAC_Titulaire','CAC_Suppleant') NOT NULL,
    date_debut_mandat  DATE NOT NULL,
    date_fin_mandat     DATE NULL,
    duree_mandat_mois   INT NULL DEFAULT 36,
    statut               ENUM('en_cours','termine','revoque') NOT NULL DEFAULT 'en_cours',
    CONSTRAINT fk_dirigeant_societe FOREIGN KEY (id_societe)
        REFERENCES societes(id_societe) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 6 : assemblees_generales
-- ---------------------------------------------------------------------
CREATE TABLE assemblees_generales (
    id_ag             INT AUTO_INCREMENT PRIMARY KEY,
    id_societe        INT NOT NULL,
    type_ag           ENUM('Ordinaire','Extraordinaire','Mixte') NOT NULL,
    date_ag           DATE NOT NULL,
    lieu              VARCHAR(200) NULL,
    ordre_du_jour     TEXT NOT NULL,
    parts_representees INT NULL,
    parts_totales      INT NULL,
    quorum_atteint      TINYINT(1) NOT NULL DEFAULT 0,
    resolutions_adoptees TEXT NULL,
    pv_fichier            VARCHAR(255) NULL,
    id_utilisateur         INT NOT NULL,
    date_creation           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ag_societe FOREIGN KEY (id_societe)
        REFERENCES societes(id_societe) ON DELETE CASCADE,
    CONSTRAINT fk_ag_utilisateur FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id_utilisateur) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 7 : formalites
-- Formalités OHADA / RCCM avec échéances et alertes
-- ---------------------------------------------------------------------
CREATE TABLE formalites (
    id_formalite        INT AUTO_INCREMENT PRIMARY KEY,
    id_societe          INT NOT NULL,
    type_formalite       ENUM('Immatriculation_RCCM','Modification_statuts','Depot_comptes_annuels',
                              'Renouvellement_mandat','Dissolution','Radiation','Publication_BOOC',
                              'Declaration_beneficiaires_effectifs','Autre') NOT NULL,
    description           VARCHAR(255) NOT NULL,
    date_echeance          DATE NOT NULL,
    date_realisation        DATE NULL,
    statut                    ENUM('a_faire','en_cours','realisee','en_retard') NOT NULL DEFAULT 'a_faire',
    document_fichier          VARCHAR(255) NULL,
    id_utilisateur_responsable INT NOT NULL,
    date_creation              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_formalite_societe FOREIGN KEY (id_societe)
        REFERENCES societes(id_societe) ON DELETE CASCADE,
    CONSTRAINT fk_formalite_utilisateur FOREIGN KEY (id_utilisateur_responsable)
        REFERENCES utilisateurs(id_utilisateur) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 8 : contrats
-- ---------------------------------------------------------------------
CREATE TABLE contrats (
    id_contrat       INT AUTO_INCREMENT PRIMARY KEY,
    id_societe       INT NOT NULL,
    intitule         VARCHAR(200) NOT NULL,
    type_contrat     ENUM('Bail','Prestation','Fourniture','Travail','Partenariat','Autre') NOT NULL,
    partie_cocontractante VARCHAR(200) NOT NULL,
    date_signature   DATE NOT NULL,
    date_echeance    DATE NULL,
    tacite_reconduction TINYINT(1) NOT NULL DEFAULT 0,
    preavis_jours    INT NULL,
    montant          DECIMAL(18,2) NULL,
    statut           ENUM('actif','expire','resilie') NOT NULL DEFAULT 'actif',
    document_fichier  VARCHAR(255) NULL,
    date_creation      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contrat_societe FOREIGN KEY (id_societe)
        REFERENCES societes(id_societe) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table 9 : journal_audit
-- Traçabilité horodatée des actions sensibles (obligatoire au cahier des charges)
-- ---------------------------------------------------------------------
CREATE TABLE journal_audit (
    id_log            INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur    INT NULL,
    action            VARCHAR(50) NOT NULL,        -- CREATE / UPDATE / DELETE / LOGIN / LOGOUT
    table_concernee   VARCHAR(50) NOT NULL,
    id_enregistrement INT NULL,
    details           TEXT NULL,
    adresse_ip        VARCHAR(45) NULL,
    date_action        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_utilisateur FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id_utilisateur) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
-- INDEX complémentaires pour la recherche / performance
-- =====================================================================
CREATE INDEX idx_societes_raison_sociale ON societes(raison_sociale);
CREATE INDEX idx_societes_rccm ON societes(numero_rccm);
CREATE INDEX idx_formalites_echeance ON formalites(date_echeance);
CREATE INDEX idx_formalites_statut ON formalites(statut);
CREATE INDEX idx_contrats_echeance ON contrats(date_echeance);
CREATE INDEX idx_associes_societe ON associes(id_societe);

-- =====================================================================
-- DONNÉES DE DÉMONSTRATION
-- =====================================================================

-- Comptes de test pour les 3 rôles obligatoires
-- Mots de passe hachés avec password_hash() PHP (bcrypt) :
--   admin@cabinet-ohada.sn      -> Admin@2026
--   juriste@cabinet-ohada.sn    -> Juriste@2026
--   consultant@cabinet-ohada.sn -> Consult@2026
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut) VALUES
('GASSAMA', 'Moussa', 'admin@cabinet-ohada.sn', '$2b$12$Ap8RFcZHEh4t6ZtgTFlqBu6zLqh/.cdD6Ae/Srd4toK.EYmhpGX82', 'administrateur', 'actif'),
('FALL', 'Aïssatou', 'juriste@cabinet-ohada.sn', '$2b$12$WSU3AJj1yU2x4kz6uC52oevLWESEqCra.al0OGAMqu1fWpF2TrxNC', 'juriste', 'actif'),
('NDIAYE', 'Cheikh', 'consultant@cabinet-ohada.sn', '$2b$12$j72lbCHRDT15eL9PFHZozeSmqDLXZm1rJJKY6V7UiyYXGp2zASObu', 'consultant', 'actif');

-- Sociétés de démonstration
INSERT INTO societes (raison_sociale, sigle, forme_juridique, capital_social, siege_social, ninea, numero_rccm, date_immatriculation, objet_social, duree_annees, statut, id_utilisateur_creation) VALUES
('ATLAS CONSEIL SARL', 'ATLAS', 'SARL', 5000000.00, 'Liberté 6, Dakar', '0056784512', 'SN.DKR.2022.B.4521', '2022-03-15', 'Conseil en organisation et gestion de projets institutionnels', 99, 'active', 1),
('BAOBAB DISTRIBUTION', 'BAOBAB', 'SA', 50000000.00, 'Point E, Dakar', '0012345678', 'SN.DKR.2015.A.1102', '2015-06-10', 'Distribution de produits de grande consommation', 99, 'active', 1),
('AFRIC BTP CONSTRUCTION', 'AFRIC BTP', 'SARLU', 10000000.00, 'Zone industrielle, Pikine', '0034567891', 'SN.DKR.2020.B.3390', '2020-11-02', 'Bâtiment et travaux publics', 99, 'active', 2),
('TERANGA CONSULTING GIE', 'TERANGA GIE', 'GIE', 1000000.00, 'Point E, Dakar', NULL, NULL, NULL, 'Conseil en gestion et fiscalité', 10, 'en_formation', 2);

-- Associés
INSERT INTO associes (id_societe, type_personne, nom_denomination, piece_identite, nationalite, nombre_parts, valeur_nominale, date_entree) VALUES
(1, 'physique', 'Alioune SALL', '1234-1990-00123', 'Sénégalaise', 300, 10000.00, '2022-03-15'),
(1, 'physique', 'Madawass DIAGNE', '1234-1988-00456', 'Sénégalaise', 200, 10000.00, '2022-03-15'),
(2, 'morale', 'HOLDING PHARMA WEST AFRICA', 'SN.DKR.2010.A.0087', 'Sénégalaise', 4000, 10000.00, '2015-06-10'),
(3, 'physique', 'Anne Marie KOUTA DAROSA', '1234-1985-00789', 'Sénégalaise', 1000, 10000.00, '2020-11-02');

-- Mouvements de capital
INSERT INTO mouvements_capital (id_societe, id_associe, type_mouvement, nombre_parts, montant, date_mouvement, id_utilisateur) VALUES
(1, 1, 'souscription', 300, 3000000.00, '2022-03-15', 1),
(1, 2, 'souscription', 200, 2000000.00, '2022-03-15', 1);

-- Dirigeants
INSERT INTO dirigeants (id_societe, nom_complet, fonction, date_debut_mandat, date_fin_mandat, duree_mandat_mois, statut) VALUES
(1, 'Alioune SALL', 'Gerant', '2022-03-15', '2028-03-15', 72, 'en_cours'),
(2, 'Mame Demba SECK', 'PDG', '2015-06-10', '2027-06-10', 144, 'en_cours'),
(3, 'Anne Marie KOUTA DAROSA', 'Gerant', '2020-11-02', '2026-11-02', 72, 'en_cours');

-- Assemblées générales
INSERT INTO assemblees_generales (id_societe, type_ag, date_ag, lieu, ordre_du_jour, parts_representees, parts_totales, quorum_atteint, id_utilisateur) VALUES
(1, 'Ordinaire', '2025-06-30', 'Siège social', 'Approbation des comptes annuels 2024, affectation du résultat', 500, 500, 1, 1),
(2, 'Extraordinaire', '2024-12-12', 'Siège social', 'Modification de l\'objet social', 4000, 4000, 1, 1);

-- Formalités OHADA / RCCM
INSERT INTO formalites (id_societe, type_formalite, description, date_echeance, date_realisation, statut, id_utilisateur_responsable) VALUES
(1, 'Depot_comptes_annuels', 'Dépôt des états financiers 2025 au greffe du tribunal de commerce', '2026-09-30', NULL, 'a_faire', 2),
(2, 'Renouvellement_mandat', 'Renouvellement du mandat du Commissaire aux comptes', '2026-08-25', NULL, 'a_faire', 2),
(4, 'Immatriculation_RCCM', 'Immatriculation initiale au RCCM de Dakar', '2026-08-20', NULL, 'en_cours', 2),
(3, 'Declaration_beneficiaires_effectifs', 'Déclaration des bénéficiaires effectifs (registre RBE)', '2026-07-01', '2026-06-25', 'realisee', 2);

-- Contrats
INSERT INTO contrats (id_societe, intitule, type_contrat, partie_cocontractante, date_signature, date_echeance, tacite_reconduction, preavis_jours, montant, statut) VALUES
(1, 'Bail commercial siège social', 'Bail', 'SCI DIALLO IMMOBILIER', '2023-01-01', '2026-12-31', 1, 90, 6000000.00, 'actif'),
(2, 'Contrat de fourniture de marchandises', 'Fourniture', 'DISTRIB PLUS SARL', '2024-02-15', '2026-02-15', 1, 60, 25000000.00, 'actif');

-- Journal d'audit (exemple)
INSERT INTO journal_audit (id_utilisateur, action, table_concernee, id_enregistrement, details) VALUES
(1, 'CREATE', 'societes', 1, 'Création de la société ATLAS'),
(1, 'LOGIN', 'utilisateurs', 1, 'Connexion administrateur');
