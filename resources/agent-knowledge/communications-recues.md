# Communications reçues

## À quoi ça sert
La barre du haut propose une vue des prospects associés aux communications entrantes. Un compteur indique le nombre de communications à traiter.

## Comment faire
Ouvrez « Communications reçues » pour afficher le tableau de prospects avec le filtre des communications reçues. Depuis la fiche du prospect, consultez les messages et SMS selon les droits.

## Règles
La liste suit la visibilité des prospects et communications accordée au compte. Le compteur est actualisé périodiquement et lors d’événements de lecture.

## Limites
Les règles exactes qui déterminent les communications comptées, leur statut et leur traitement sont À vérifier.

## Dans l'interface
Chemin : barre du haut du projet > icône communications avec libellé « Communications reçues » (`Header.vue`). Elle ouvre la route `prospect` avec le filtre `incomingCommunications`.

1. Sélectionnez « Communications reçues ».
2. La liste de prospects est chargée avec le filtre correspondant; sélectionnez un prospect pour ouvrir sa fiche.
3. Sur la fiche, utilisez « Message » ou « SMS » pour ouvrir le panneau de communication; les boutons d’envoi et les canaux disponibles dépendent des réglages.

Droits requis : accès au projet et visibilité du prospect. Le contrôle d’accès spécifique au menu et à la lecture des communications est À vérifier. Après sélection, le tableau filtré et, le cas échéant, le compteur sont visibles.

Sources d’interface : `components/layouts/Header.vue`, `components/prospect/table/Layout.vue`, `components/prospect/profile/Profile.vue`, `router/index.js`, `lang/fr.json` (`header.incoming_communications`).

## Questions fréquentes
**Où trouver les communications entrantes ?** Sélectionnez « Communications reçues » en haut de la page projet.

**Le compteur est-il instantané ?** Le code le rafraîchit périodiquement et après lecture; la règle de comptage est À vérifier.

**Comment ouvrir l’échange lié ?** Ouvrez le prospect concerné puis « Message » ou « SMS », selon le type.
