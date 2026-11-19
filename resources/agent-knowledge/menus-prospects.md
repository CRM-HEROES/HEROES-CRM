# Menus de prospects et icônes des menus

## À quoi ça sert
Un menu de prospects est un raccourci vers une liste prête à l’emploi dans le tableau « Prospects » : un clic affiche directement les prospects qui correspondent, avec leur nombre. Les icônes des menus sont les icônes de la barre du haut, que l’on peut personnaliser.

## Comment faire
Dans « Prospects », le panneau des menus se montre ou se cache avec « Afficher/cacher les menus ». Choisissez un menu pour filtrer le tableau. Pour en créer un, utilisez le bloc « Menus » de la fiche du projet.

## Règles
- Les menus de base sont toujours présents et affichent leur nombre : « Tous », « Archivés », « Non archivés » et « Attendent vos retours » (les prospects qui attendent une réponse de l’utilisateur).
- Un menu personnalisé enregistre une combinaison de filtres, avec un nom, une couleur de texte et une couleur de fond. Titre de la création : « Nouveau menu ».
- L’étoile d’un menu le définit comme menu par défaut de l’utilisateur. L’icône d’engrenage d’un menu personnalisé le modifie.
- Un menu personnalisé n’apparaît que pour les utilisateurs auxquels il est affecté (« Menus affectés » du profil utilisateur ou du rôle).

## Limites
Les menus de base ne se modifient pas. Un menu enregistre des filtres du tableau; il ne contient pas de prospects fixes : son contenu change avec les données.

## Dans l'interface
Chemin : « Prospects » > panneau des menus (`components/prospect/table/menu/`), et « Accueil » > le projet > bloc « Menus ».

1. Pour créer un menu : bloc « Menus » > ajout, « Nouveau menu ». Saisissez le nom, la couleur et la couleur de fond.
2. Choisissez les filtres : « Utiliser les filtres actuels » reprend ceux du tableau en cours, ou « Sélectionner des filtres » permet de choisir par catégorie (option « Avec » suivie du nom de la catégorie) ou par « Utilisateurs affectés ».
3. Cliquez « Créer le menu ». Droit requis : `all.project.menu.add`.
4. Pour modifier ou supprimer un menu : `all.project.menu.update` ou `all.project.menu.delete`.
5. **Icônes de la barre du haut :** icône engrenage > « Paramètres » > « Modifier les icones des menus » (« Prospects, Agendas, Map, ... ») permet de changer l’icône de chaque entrée de la barre du haut.

Droits requis : voir chaque étape. Après la création, le menu apparaît dans le panneau des menus de « Prospects ».

Sources d’interface : `components/menu/`, `components/menu-icon/`, `components/prospect/table/menu/`, `lang/fr.json` (`menu.*`, `setting.menu_icons.*`).

## Questions fréquentes
**Comment voir uniquement les prospects non archivés ?** Menu « Non archivés ».

**Comment créer une liste des prospects à rappeler ?** Filtrez le tableau comme voulu, puis bloc « Menus » > « Nouveau menu » > « Utiliser les filtres actuels » > « Créer le menu ».

**Comment choisir mon menu par défaut ?** Cliquez l’étoile du menu voulu.

**Comment changer les icônes de la barre du haut ?** « Paramètres » > « Modifier les icones des menus ».
