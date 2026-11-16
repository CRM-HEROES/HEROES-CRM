# Appels

## À quoi ça sert
Le CRM peut lancer des appels sortants via une ligne CloudTalk ou Kavkom configurée. Les appels et leurs résultats peuvent apparaître dans l’historique des prospects.

## Comment faire
Pour CloudTalk, l’API projet reçoit un numéro et résout une ligne associée à l’utilisateur connecté; un identifiant de ligne peut être fourni. Les workflows proposent aussi une action d’appel quand une ligne compatible est configurée. Pour un appel d’agent, vérifie d’abord l’historique et la présence du téléphone.

## Règles
Les workflows appellent d’abord l’agent de la ligne, puis le prospect. Les appels démarrés par l’outil d’agent `call_start` sont soumis à validation humaine, même en mode autonome. L’aperçu d’approbation masque le numéro et présente le briefing. Aucun appel n’est lancé sans consentement ni hors heures ouvrées; une demande hors plage est différée sur la file `agents` si elle est active.

## Limites
`call_start` ne prend en charge que CloudTalk. Kavkom est disponible dans les API et workflows existants, mais pas par cet outil. Les modalités de disponibilité d’une ligne varient selon le compte et sont À vérifier.

## Dans l'interface
Chemin : « Prospects » > fiche du prospect > icône « Appel » (`prospect.profile.Profile.vue`) ouvre le panneau d’interactions.

1. Vérifiez le numéro de téléphone du prospect et ouvrez « Appel ».
2. Choisissez un moyen d’appel proposé dans le panneau; les options visibles dépendent des lignes configurées (CloudTalk, Kavkom ou intégrations compatibles).
3. Après l’appel, utilisez l’action d’enregistrement de l’interaction si elle est disponible. L’étiquette du bouton et la procédure exacte sont À vérifier.

Droits requis : l’affichage du panneau d’interactions est soumis à `all.prospect.interaction.view`; l’ajout d’un historique requiert `all.prospect.interaction.add`. Une ligne/configuration d’appel compatible doit être disponible. Après l’action, les interactions et résultats consignés apparaissent dans le panneau d’historique.

Sources d’interface : `components/prospect/profile/Profile.vue`, `components/prospect/interaction/Slide.vue`, `components/prospect/interaction/`, `components/layouts/Main.vue`, `lang/fr.json` (`prospect.profile.menus.call`, `prospect.interaction.*`).

## Questions fréquentes
**Où appeler un prospect ?** Dans sa fiche, cliquez l’icône « Appel ».

**Pourquoi aucun moyen d’appel n’est proposé ?** Une ligne compatible et les droits d’interaction sont nécessaires.

**L’appel d’un agent est-il lancé sans confirmation ?** Non, `call_start` attend une validation humaine.
