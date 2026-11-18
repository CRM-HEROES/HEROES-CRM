# Communications reçues

## À quoi ça sert
La barre du haut propose une vue des prospects associés aux communications entrantes. Un compteur indique le nombre de communications à traiter.

## Comment faire
Ouvrez « Communications reçues » pour afficher le tableau de prospects avec le filtre des communications reçues. Depuis la fiche du prospect, consultez les messages et SMS selon les droits.

## Règles
La liste suit la visibilité des prospects et communications accordée au compte. Le compteur est actualisé toutes les 2 minutes (si l’onglet est visible) et dès qu’une communication est marquée comme traitée.

## Limites
Trois types de communications entrantes sont comptés : les SMS reçus, les e-mails reçus (dans un canal de discussion du projet) et les messages vocaux (messagerie d’un appel, détectée à partir de l’issue de l’appel ou d’un lien d’enregistrement vocal). Une communication reste « à traiter » tant que personne ne l’a marquée comme traitée avec « Demande traité » : dès qu’un utilisateur la traite, elle disparaît pour tous les autres (l’historique garde qui l’a traitée). Le badge de l’en-tête et la liste comptent des prospects, pas des messages : un prospect avec trois SMS non traités compte une fois.

## Dans l'interface
Chemin : barre du haut du projet > icône communications avec libellé « Communications reçues » (`Header.vue`). Elle ouvre la route `prospect` avec le filtre `incomingCommunications`.

1. Sélectionnez « Communications reçues ».
2. La liste de prospects est chargée avec le filtre correspondant; sélectionnez un prospect pour ouvrir sa fiche.
3. Sur la fiche, utilisez « Message » ou « SMS » pour ouvrir le panneau de communication; les boutons d’envoi et les canaux disponibles dépendent des réglages.

Droits requis : accès au projet et visibilité du prospect. L’entrée de menu n’est pas conditionnée par un droit particulier. Le compteur et la liste ne portent que sur les prospects visibles de l’utilisateur : tous les prospects pour un super-administrateur, le créateur du projet, un détenteur de `all` ou de `all.prospect.view`; sinon seulement les prospects qu’il a créés, qui lui sont affectés ou qui sont dans l’un de ses groupes. Après sélection, le tableau filtré et, le cas échéant, le compteur sont visibles.

Sources d’interface : `components/layouts/Header.vue`, `components/prospect/table/Layout.vue`, `components/prospect/profile/Profile.vue`, `router/index.js`, `lang/fr.json` (`header.incoming_communications`).

## Questions fréquentes
**Où trouver les communications entrantes ?** Sélectionnez « Communications reçues » en haut de la page projet.

**Le compteur est-il instantané ?** Non : il est rafraîchi toutes les 2 minutes et après chaque traitement. Il compte les prospects ayant au moins une communication non traitée, tous types confondus.

**Comment ouvrir l’échange lié ?** Ouvrez le prospect concerné puis « Message » ou « SMS », selon le type.
