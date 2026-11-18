# Tableau de bord

## À quoi ça sert
Le tableau de bord global présente les projets accessibles et un accès pour créer un projet. Le routeur distingue cette page d’accueil de la fiche du projet.

## Comment faire
Ouvrez « Accueil » dans la barre supérieure pour revenir au tableau de bord. Depuis « Vos tableaux », choisissez un projet ou « Nouveau projet ».

## Règles
La connexion est requise. Les projets affichés dépendent des accès du compte. La création peut être soumise aux permissions et au parcours d’inscription.

## Limites
L’accueil ne contient pas de métriques : c’est une grille de cartes de projets. Un utilisateur voit les projets dont il est membre; un super-administrateur les voit tous. Sur ordinateur, les cartes se réorganisent par glisser-déposer et l’ordre est enregistré.

## Dans l'interface
Chemin : barre du haut > icône maison, libellé « Accueil » (`Header.vue`, route `dashboard`) > « Vos tableaux » (`Dashboard.vue`, `fr.json` `dashboard.your_projects`).

1. Pour ouvrir un projet, sélectionnez-le dans « Vos tableaux »; le composant de destination est la fiche ou le projet correspondant. Le libellé des lignes est le nom du projet.
2. Pour démarrer la création, sélectionnez « Nouveau projet ». Une fenêtre « Nouveau projet » s’ouvre avec un formulaire en une seule étape : « Nom » (obligatoire), « Type de métier associé à votre projet » (obligatoire : agence commerciale de prospection, installateur de panneaux photovoltaïques, de rénovation globale ou de pompes à chaleur, transport de personnes, gestionnaire d’intervention terrain, exposant salons et expositions, géolocalisation de véhicules, ou Autre) et, en option, téléphone, e-mail et adresse. Le bouton « Ajouter » crée le projet.
3. Pour revenir au projet actuellement ouvert, sélectionnez « Accueil » puis son projet, si celui-ci apparaît dans la liste.

Droit requis : être connecté et avoir accès au projet; la carte « Nouveau projet » est toujours affichée et le serveur n’exige que le nom (le contrôle réservé aux super-administrateurs est désactivé dans le code) Après sélection, le tableau du projet s’ouvre; les chiffres se trouvent dans « Statistiques », pas dans l’accueil.

Sources d’interface : `components/layouts/Header.vue`, `components/dashboard/Dashboard.vue`, `router/index.js`, `lang/fr.json`.

## Questions fréquentes
**Comment revenir à l’accueil ?** Sélectionnez « Accueil » dans la barre du haut.

**Comment ouvrir un projet ?** Choisissez-le dans « Vos tableaux ».

**Je ne vois pas un projet, que faire ?** Seuls les projets dont l’utilisateur est membre sont listés : demandez à un administrateur du projet de l’y ajouter (bloc « Utilisateurs » de la fiche du projet).
