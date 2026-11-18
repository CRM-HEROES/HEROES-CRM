# Véhicules, duplication de prospect, tutoriels et suivi d'activité

## À quoi ça sert
Quatre petites fonctions du CRM qui n’ont pas leur propre écran principal : suivre des véhicules sur la carte, dupliquer un prospect, réafficher les tutoriels et consulter l’activité d’un utilisateur.

## Comment faire
- **Véhicules :** profil d’un utilisateur > icône « Véhicules ».
- **Dupliquer un prospect :** dans le tableau « Prospects », menu d’une ligne, icône de copie.
- **Tutoriels :** icône engrenage > « Paramètres » > « Gérer les tutoriels ».
- **Activité d’un utilisateur :** profil de l’utilisateur > « Historique de connexion » et « Actions faites par l'utilisateur ».

## Règles
- **Véhicule :** « Nouveau véhicule » demande un nom, un « Tracker » (« Optimum Automotive »), l’« ID véhicule » du fournisseur de géolocalisation, le « Type équipement » et l’« Immatriculation ». Un véhicule est affecté à un utilisateur (« Utilisateur affecté ») et peut être choisi dans un rendez-vous; la carte peut afficher les positions des véhicules des utilisateurs (icône « MAP - Localisation des utilisateurs »). Droits : `all.project.vehicle.add`, `.update`, `.delete`.
- **Duplication :** crée un nouveau prospect dans le même projet avec les champs, les coordonnées géographiques, les filtres, les utilisateurs affectés et les groupes du prospect d’origine. Elle demande le droit `all.prospect.add` et une confirmation (« Voulez-vous vraiment dupliquer ce prospect ? »).
- **Tutoriels :** ce sont les bulles d’aide qui s’affichent à la première visite d’un écran. « Gérer les tutoriels » liste ceux qui ont été vus; en supprimer un le fait réapparaître. « Ne plus afficher des tutoriels » les désactive.
- **Activité :** « Historique de connexion » montre les connexions de l’utilisateur (avec les lieux et appareils); « Actions faites par l'utilisateur » montre un graphique (« Logs Chart ») de ses actions jour par jour. Ces écrans demandent `all` (administrateur du projet).

## Limites
Un seul fournisseur de suivi de véhicules est proposé (Optimum Automotive). La duplication ne copie ni les appels, ni les SMS, ni les messages, ni les fichiers, ni les devis du prospect d’origine. Le bloc « Liens externes » de la fiche d’un prospect liste les liens associés à ce prospect (par exemple vers une fiche dans un autre outil).

## Dans l'interface
Chemin : voir la rubrique Comment faire (`components/vehicle/`, `components/prospect/table/Row.vue`, `components/tutorial/`, `components/user/tracker/`).

1. **Ajouter un véhicule :** « Nouveau véhicule », nom, « Tracker », « ID véhicule », « Type équipement », « Immatriculation », puis l’utilisateur à qui il est affecté.
2. **Dupliquer un prospect :** menu d’une ligne du tableau, icône de copie, confirmer.
3. **Réafficher un tutoriel :** « Paramètres » > « Gérer les tutoriels » > supprimez le tutoriel voulu (« Supprimez des tutoriels pour les faire apparaître de nouveau. »).
4. **Voir l’activité :** profil de l’utilisateur > icônes « Historique de connexion » ou « Actions faites par l'utilisateur ».

Droits requis : voir la rubrique Règles. Après l’action, le véhicule, le prospect dupliqué ou le tutoriel réapparaît dans sa liste.

Sources d’interface : `components/vehicle/`, `components/prospect/table/Row.vue`, `components/tutorial/`, `components/user/tracker/stat/`, `lang/fr.json` (`vehicle.*`, `setting.tutorials.*`, `user.log_chart.*`).

## Questions fréquentes
**Comment dupliquer un prospect ?** Dans le tableau, menu de sa ligne > icône de copie > confirmer.

**Comment suivre mes véhicules sur la carte ?** Ajoutez-les (« Nouveau véhicule ») et affectez-les à un utilisateur; la carte affiche leur position avec « MAP - Localisation des utilisateurs ».

**Comment revoir un tutoriel ?** « Paramètres » > « Gérer les tutoriels » et supprimez-le de la liste.

**Comment voir ce qu’a fait un collègue ?** Son profil > « Actions faites par l'utilisateur ».
