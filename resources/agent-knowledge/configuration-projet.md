# Configuration du projet

## À quoi ça sert
La fiche du projet permet de définir ce que les équipes utilisent ensuite sur les prospects : champs, filtres, groupes, calendriers, dossiers, canaux de discussion, produits, modèles de SMS et véhicules.

## Comment faire
Ouvrez « Accueil » puis le projet. Les icônes de l’en-tête de la fiche (« Canaux de discussion », « Dossiers », « Catégories de filtres », « Champs de prospects », « Groupes », « Rôles », « Produits ») ouvrent chacune le formulaire de création. Les autres éléments (calendriers, modèles de SMS) se créent depuis leur bloc de la fiche; les véhicules se créent depuis le profil d’un utilisateur (icône « Véhicules »). Pour connaître ce qui existe déjà dans le projet, utilise `project_config` : il donne les identifiants des labels, groupes, champs et statuts.

## Règles
Un élément appartient à un seul projet. Un élément supprimé va dans la « Corbeille » du projet quand elle gère ce type d’élément (prospects, filtres, canaux, dossiers, fichiers, produits…) et peut alors être restauré. Les droits suivent le modèle `all.project.<élément>.add`, `.update` et `.delete` : le serveur refuse l’action sans le droit, même si l’icône est affichée.

## Limites
Cette fiche décrit les formulaires et les droits relevés dans le code. Les règles de gestion propres à chaque projet (noms des filtres, étapes utilisées) viennent de la configuration du projet, pas du CRM.

## Dans l'interface
Chemin : « Accueil » > le projet > fiche du projet (`components/project/profile/Profile.vue`).

1. **Champs de prospects** (icône « Champs de prospects », titre « Nouveau champ ») : un champ a un type parmi « Texte », « Long texte », « Nombre », « Email », « Date », « Date et heure », « Adresse », « URL » et « Numéro ». Les options sont « Valeur par défault », « Obligatoire », « Vérifier doublon lors du saisi », « Format d'affichage » et « Ajouter dans la recherche globale ». Le numéro de téléphone et le numéro mobile sont des champs par défaut. Supprimer un champ qui contient des données demande une confirmation qui indique le nombre de données liées aux prospects. Droits : `all.project.field.add`, `.update`, `.delete`.
2. **Catégories de filtres et filtres** (icône « Catégories de filtres », titre « Nouvelle catégorie de filtre ») : une catégorie regroupe des filtres (les labels des prospects). L’option « Choix unique » impose un seul filtre de la catégorie par prospect. Un filtre se crée avec « Nouveau filtre » dans la catégorie; il a les options « Combiner avec », « Utilisateurs affectés » et « Archiver » (« Ne plus afficher dans la liste des filtres »). Droits : `all.project.category.add`, `.update`, `.delete` pour les catégories, et `all.project.category.label.add`, `.update`, `.delete` pour les filtres.
3. **Groupes** (icône « Groupes », titre « Nouveau groupe ») : ils regroupent des prospects et des utilisateurs; un utilisateur d’un groupe voit les prospects du groupe. Droits : `all.project.group.add`, `.update`, `.delete`.
4. **Calendriers** (bloc « Calendriers », titre « Nouveau calendrier ») : un calendrier a un type parmi « Physique », « Téléphonique », « GMeet », « Tâche » et « Autre ». Chaque utilisateur peut définir une « Adresse par défaut pour vous ». Droits : `all.project.calendar.add`, `.update`, `.delete`.
5. **Dossiers** (icône « Dossiers », titre « Nouveau dossier ») : ils classent les fichiers des prospects. Droits : `all.project.folder.add`, `.update`, `.delete`.
6. **Canaux de discussion** (icône « Canaux de discussion », titre « Nouveau canal de discussion ») : une conversation du CRM où sont classés les e-mails; elle peut « Envoyer un mail au prospect » ou envoyer à une adresse précise, et être associée à un utilisateur. Droits : `all.project.thread.add`, `.update`, `.delete`.
7. **Produits** (icône « Produits ») : un produit a un « Prix », une « Devise », une « Tax en pourcentage », l’option « Taxe incluse », des images (« Gérer les images du produit ») et peut être archivé (« Ne plus afficher dans la liste des produits »). Droits : `all.project.order.product.add`, `.update`, `.delete` (voir la fiche Devis, produits et commissions).
8. **Modèles de SMS** : ils servent de textes prêts à l’emploi dans le panneau « SMS » (« Templates SMS »). Droits dans l’écran des permissions : `all.project.sms-template.add`, `.update`, `.delete` (voir la fiche Droits et permissions pour une incohérence connue avec le serveur).
9. **Véhicules** (« Nouveau véhicule ») : un véhicule est affecté à un utilisateur et peut être choisi dans un rendez-vous; la carte peut afficher la position des véhicules.

Droits requis : voir chaque élément ci-dessus. Après création, l’élément apparaît dans son bloc de la fiche du projet et devient choisissable dans le tableau des prospects, les fiches ou les workflows.

Sources d’interface : `components/project/profile/Profile.vue`, `components/project/profile/blocs/`, `components/field/`, `components/category/`, `components/label/`, `components/group/`, `components/calendar/`, `components/folder/`, `components/thread/`, `components/product/`, `components/sms-template/`, `components/vehicle/`, `lang/fr.json`.

## Questions fréquentes
**Comment ajouter un champ personnalisé aux prospects ?** Icône « Champs de prospects » de la fiche du projet, puis choisissez son type; droit `all.project.field.add`.

**Comment créer un filtre pour classer les prospects ?** Créez d’abord une catégorie (« Catégories de filtres »), puis « Nouveau filtre » dans cette catégorie.

**À quoi sert « Choix unique » ?** Un prospect ne peut avoir qu’un seul filtre de cette catégorie.

**J’ai supprimé un élément par erreur.** Ouvrez « Paramètres » > « Corbeille » : « Restaurer » le remet en place, si ce type d’élément y figure.
