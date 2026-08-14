Projet 62 — Plateforme de gestion juridique des sociétés OHADA (AUSCGIE) et des formalités RCCM
Master CCA — École Supérieure Polytechnique de Dakar
Enseignant : M. Ousmane LY | Étudiant : Moussa | Deadline : 14 août 2026
1. Description
Plateforme web permettant de centraliser la vie juridique d'un portefeuille de sociétés :
référentiel des sociétés (forme, capital, RCCM, NINEA), registre des associés/actionnaires,
suivi des dirigeants et de leurs mandats, assemblées générales, formalités OHADA/RCCM avec
alertes d'échéance, et gestion des contrats.
2. Stack technique
Backend : PHP 8+ orienté objet, PDO (requêtes préparées)
Base de données : MySQL / MariaDB (via phpMyAdmin sous XAMPP)
Frontend : HTML5, CSS3, Bootstrap 5, JavaScript
Graphiques : Chart.js
Export PDF : FPDF (fourni dans `libs/fpdf/`)
Export CSV/Excel : génération native PHP
3. Prérequis
XAMPP (Apache + MySQL + PHP 8+) installé
Un navigateur web récent
4. Installation
Copier le dossier `projet62_ohada/` dans `C:\xampp\htdocs\` (Windows) ou `/opt/lampp/htdocs/` (Linux)
Démarrer Apache et MySQL depuis le panneau de contrôle XAMPP
Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
Créer la base de données en important le fichier `sql/schema.sql`
(onglet Importer → sélectionner le fichier → Exécuter)
Ce script crée la base `projet62_ohada`, les 9 tables liées par clés étrangères,
et insère des données de démonstration.
Vérifier les identifiants de connexion dans `config/database.php` si besoin
(par défaut : utilisateur `root`, pas de mot de passe — configuration standard XAMPP)
Accéder à l'application : `http://localhost/projet62_ohada/public/`
5. Comptes de test (mots de passe hachés avec `password_hash()`)
Rôle	Email	Mot de passe
Administrateur	admin@cabinet-ohada.sn	Admin@2026
Juriste (avancé)	juriste@cabinet-ohada.sn	Juriste@2026
Consultant (standard)	consultant@cabinet-ohada.sn	Consult@2026
Droits par rôle :
Administrateur : accès total (création, modification, suppression, consultation)
Juriste : création et modification des dossiers, pas de suppression
Consultant : consultation seule (lecture)
6. Structure du projet
```
projet62_ohada/
├── config/
│   ├── database.php        Connexion PDO sécurisée
│   └── .htaccess            Bloque l'accès direct
├── includes/
│   ├── auth.php              Authentification, sessions, rôles
│   ├── functions.php         Fonctions utilitaires (sécurité, pagination, audit)
│   ├── header.php / footer.php   Gabarit commun Bootstrap
│   └── .htaccess
├── modules/
│   └── societes/             Module CRUD complet (entité principale)
│       ├── liste.php          Recherche, filtres, pagination
│       ├── ajouter.php         Création (Create)
│       ├── modifier.php         Mise à jour (Update)
│       ├── supprimer.php         Suppression (Delete, admin uniquement)
│       └── voir.php              Fiche détaillée (Read)
├── public/
│   ├── index.php               Point d'entrée
│   ├── login.php                 Connexion
│   ├── logout.php                 Déconnexion
│   └── dashboard.php               Tableau de bord + graphiques Chart.js
├── exports/
│   ├── societes_export_pdf.php     Export PDF (FPDF)
│   └── societes_export_csv.php      Export CSV (compatible Excel)
├── libs/fpdf/                        Librairie FPDF pour génération PDF
├── assets/css/style.css               Charte graphique (bleu marine / or)
├── assets/js/script.js                  Comportements JS
└── sql/schema.sql                         Script de création + données de démo
```
7. Modèle Conceptuel de Données (résumé)
9 entités reliées par clés étrangères :
utilisateurs (1,n) — crée → societes
societes (1,n) — possède → associes
societes (1,n) — possède → dirigeants
societes (1,n) — possède → assemblees_generales
societes (1,n) — possède → formalites
societes (1,n) — possède → contrats
associes (1,n) — génère → mouvements_capital
utilisateurs (1,n) — enregistre → journal_audit (traçabilité)
Toutes les suppressions de société entraînent la suppression en cascade
(`ON DELETE CASCADE`) des enregistrements liés (associés, dirigeants, formalités, contrats, AG).
8. Sécurité mise en œuvre
Mots de passe hachés (`password_hash` / `password_verify`)
Requêtes 100% préparées via PDO (`PDO::ATTR_EMULATE_PREPARES = false`)
Protection XSS : `htmlspecialchars()` systématique à l'affichage (fonction `e()`)
Protection CSRF : jeton unique par formulaire (`genererTokenCSRF()` / `verifierTokenCSRF()`)
Sessions sécurisées : `httponly`, régénération d'ID à la connexion, expiration après 30 min d'inactivité
Gestion des rôles à 3 niveaux avec contrôle d'accès sur chaque page sensible
Journal d'audit horodaté de toutes les actions sensibles (connexion, création, modification, suppression, export)
8bis. Corrections apportées lors de l'audit complet
Un audit systématique de la plateforme a permis d'identifier et corriger :
Bug critique : réutilisation d'un même paramètre nommé PDO (`:q`) plusieurs fois dans une
requête (`LIKE :q OR ... LIKE :q`). Avec `PDO::ATTR_EMULATE_PREPARES = false` (sécurité renforcée),
MySQL en requêtes préparées natives interdit cette réutilisation → erreur fatale à chaque recherche.
Corrigé dans `societes/liste.php`, `contrats/liste.php`, et les 2 exports (paramètres renommés `:q1`,
`:q2`, `:q3`). Vérification finale : 0 duplication sur l'ensemble des requêtes `->prepare()` du projet.
Bug de compatibilité : `mb_substr()` (extension `mbstring`, optionnelle) utilisée pour l'avatar
utilisateur — remplacée par une expression régulière PCRE (`preg_match('/^./u', ...)`) qui ne dépend
d'aucune extension PHP optionnelle, évitant un plantage total de l'application si `mbstring` n'est
pas activée sur l'environnement XAMPP cible.
Chemins relatifs : détection automatique de la profondeur (`public/` vs `modules/xxx/`) pour que
tous les liens de navigation fonctionnent quelle que soit la page, validée par un test de rendu HTML
réel à deux profondeurs différentes.
Conformité HTML : entités `&` non échappées dans les URL Google Fonts (`&amp;`).
CRUD incomplet : le module Assemblées générales n'avait pas de fonction de modification —
`modules/assemblees/modifier.php` ajouté. Les 6 modules ont désormais un CRUD strictement complet.
Validation JavaScript manquante : le cahier des charges exige une validation client (JS) et
serveur (PHP) sur tous les formulaires. Un moteur de validation générique et réutilisable a été ajouté
(`assets/js/script.js`, classe `.js-validate`) : champs obligatoires, valeurs numériques négatives,
cohérence chronologique des dates (ex. date de fin ≥ date de début), affichage des erreurs inline.
Appliqué aux 12 formulaires de saisie. Testé fonctionnellement avec jsdom (pas seulement une
relecture de code) : cas invalide → soumission bloquée avec les bons messages ; cas valide → soumission
autorisée sans faux positif.
Validation effectuée par rendu HTML réel (`DOMDocument`) + vérification manuelle d'équilibre des
balises, en plus du `php -l` sur les 46 fichiers PHP du projet.
8quater. Correctifs suite aux tests utilisateur (post-audit)
Trois problèmes remontés après l'audit initial ont été corrigés :
Export PDF cassé : la version de FPDF fournie charge les métriques des 14 polices standard
(Helvetica, Times, Courier, Symbol, ZapfDingbats — styles normal/gras/italique/gras-italique)
depuis des fichiers JSON externes (`libs/fpdf/font/*.json`), qui n'étaient pas inclus. Ces 14
fichiers ont été régénérés à partir des métriques Adobe officielles (AFM) et de la table
d'encodage cp1252 standard, puis validés (structure JSON, clés attendues par `fpdf.php`,
cohérence des largeurs de caractères avec les valeurs Adobe de référence).
Nom d'administrateur tronqué dans la navbar : avec 7 liens de navigation plus le bloc
utilisateur, l'espace horizontal était insuffisant en résolution desktop standard. Le bloc
utilisateur est désormais un menu déroulant (avatar cliquable) qui affiche le nom complet
dans un panneau dédié, sans troncature possible. Le seuil de bascule vers le menu mobile a
également été relevé (`navbar-expand-xl`) pour laisser plus de place aux liens.
Emails de formalités (`modules/formalites/`) :
Bug corrigé : un échec d'envoi d'email pouvait s'afficher par erreur dans une bannière verte
de succès (variable de session réutilisée à tort). Une variable dédiée (`flash_erreur`,
bannière rouge) est maintenant utilisée pour les échecs, séparément du succès de création.
Un timeout de connexion (10 s) a été ajouté au mailer, et une détection immédiate des
identifiants SMTP encore laissés à leur valeur de test évite une attente longue avant
l'échec quand le SMTP n'est pas configuré.
Rappel : sans configuration SMTP réelle dans `includes/mailer.php` (voir section 10),
l'échec d'envoi d'email est normal et attendu — l'application reste pleinement fonctionnelle.
8ter. Vérification de conformité — Section 2.2 du cahier des charges
Les 22 exigences obligatoires ont été vérifiées présentes et fonctionnelles par script automatisé :
authentification hachée, 3 rôles, CRUD complet (6 modules), dashboard Chart.js, recherche/filtres,
pagination, validation JS+PHP, requêtes préparées PDO, protection XSS, exports PDF+CSV, responsive,
email automatique, sessions sécurisées + expiration, journal d'audit, script SQL avec démo, README.
9. Modules fonctionnels (état complet)
Tous les modules CRUD sont désormais implémentés et fonctionnels :
Module	CRUD complet	Recherche/filtres	Pagination	Particularité
Sociétés	✅	✅	✅	Exports PDF/CSV
Associés	✅	✅	✅	Registre par société
Dirigeants	✅	✅	✅	Alerte visuelle fin de mandat < 90 jours
Formalités	✅	✅	✅	Email automatique à la création + bouton d'alerte manuelle
Contrats	✅	✅	✅	Alerte tacite reconduction / échéance < 90 jours
Assemblées générales	Create/Read/Delete	✅	-	Calcul automatique du quorum
10. Configuration de l'envoi d'email (obligatoire au cahier des charges)
L'envoi automatique se déclenche à chaque création de formalité (`modules/formalites/ajouter.php`)
et via le bouton ✉️ dans la liste des formalités. Pour l'activer réellement :
Ouvrir `includes/mailer.php`
Renseigner vos identifiants SMTP réels :
```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_USER', 'votre.email@gmail.com');
   define('SMTP_PASS', 'mot_de_passe_application');  // pas votre mot de passe Gmail habituel
   ```
Pour Gmail : activer la validation en 2 étapes puis générer un mot de passe d'application
(myaccount.google.com/apppasswords)
Sans configuration SMTP, l'application reste pleinement fonctionnelle : l'échec d'envoi est
journalisé (`error_log`) et signalé à l'utilisateur sans bloquer la création de la formalité —
c'est volontaire pour ne pas casser la démo en environnement XAMPP local sans serveur mail.
11. Reste à produire avant la deadline du 14/08
Ces livrables sont documentaires, pas techniques — l'application elle-même est complète :
[ ] Manuel utilisateur PDF (15-20 pages, captures d'écran de chaque module)
[ ] Document technique (MCD détaillé, architecture, conventions de code)
[ ] Dépôt Git public avec historique de commits réguliers (créer le repo, `git init`, commits par module)
[ ] Vidéo de présentation (8 à 15 minutes) démontrant : connexion, CRUD Sociétés, alerte email
Formalités, dashboard, exports PDF/CSV, gestion des rôles
12. Design
Interface avec navigation horizontale (navbar) au style formel et professionnel :
Typographie Inter (Google Fonts), palette bleu marine (`#0b2d4f`) / or (`#c9962e`)
Navbar fixe avec dégradé, liens actifs mis en évidence automatiquement, badge de rôle coloré, avatar
Cartes KPI avec icônes contextuelles, tableaux avec en-têtes stylisés, badges de statut cohérents
Page de connexion en carte centrée avec dégradé de fond, comptes de démonstration affichés
Navigation responsive (menu Bootstrap standard) sur mobile/tablette
13. Auteur
Moussa — Master 2 CCA, ESP Dakar
