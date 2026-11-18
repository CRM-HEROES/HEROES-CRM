# Modèles de SMS, de messages et de description de RDV

## À quoi ça sert
Les modèles sont des textes prêts à l’emploi : on les crée une fois, puis on les réutilise sur n’importe quelle fiche pour gagner du temps et garder le même ton. Il existe trois sortes de modèles : de SMS, de messages (e-mails) et de description de rendez-vous.

## Comment faire
- **SMS :** fiche du prospect > « SMS » > « Templates SMS ». Cliquer un modèle copie son texte dans le champ du SMS, que l’on peut encore modifier avant d’envoyer. Dans le formulaire du SMS, l’icône « Modèle de SMS » ouvre aussi cette liste, y compris pour un envoi en masse.
- **Message :** fiche du prospect > « Message » > « Templates de message ». Cliquer un modèle remplit le message; « Envoyer » l’envoie directement.
- **Description de rendez-vous :** dans le formulaire d’un rendez-vous, « Templates de description » reprend le texte d’un modèle comme description.

## Règles
- **Modèle de SMS :** un « Nom » (obligatoire) et un « Contenu » (obligatoire). Titre de la fenêtre de création : « Nouveau modèle de SMS ».
- **Modèle de message :** un nom (obligatoire, 255 caractères au plus), un « Objet de l'e-mail » (facultatif, 255 caractères au plus), un contenu mis en forme, jusqu’à 10 pièces jointes et un design d’e-mail personnalisable (nom de la marque, formule de politesse, nom de la signature, mentions « envoyé par » et droits). Titre : « Nouveau template de message ».
- **Modèle de description de RDV :** un nom et un contenu. Titre : « Nouveau template de description de RDV ».
- **Variables dans un modèle de message :** on peut insérer des valeurs de la fiche, par exemple `{prospect.first_name}` ou `{prospect.meta.ville}` (pour un champ personnalisé, `meta` suivi de son identifiant). Elles sont remplacées par les valeurs du prospect au moment où l’on choisit le modèle. Le texte d’un modèle de SMS est copié tel quel.
- Les modèles ne sont pas affectés à des utilisateurs ou à des rôles : tous les modèles du projet sont proposés à tous ceux qui peuvent ouvrir le panneau « SMS » ou « Message ».

## Limites
Un modèle ne s’envoie pas tout seul : il remplit un message que l’utilisateur valide. Pour des envois automatiques, voir les workflows (action « Envoyer un SMS » ou « Envoyer un email »).

## Dans l'interface
Chemin : fiche du prospect > « SMS » ou « Message » (`components/prospect/sms/Slide.vue`, `components/prospect/message/Slide.vue`).

1. Pour créer un modèle de SMS : « Templates SMS », puis « Ajouter » (droit `all.prospect.sms.add` pour le bouton), « Nom », « Contenu », « Ajouter ».
2. Pour créer un modèle de message : « Templates de message », puis « Ajouter » (droit `all.project.message-template.add`), « Objet de l'e-mail », le contenu, les pièces jointes, « Ajouter ».
3. Pour créer un modèle de description de RDV : dans le formulaire d’un rendez-vous, « Templates de description », puis l’ajout (droit `all.project.event-description-template.add`).
4. Pour utiliser un modèle : cliquez-le dans la liste; son texte remplit le champ; vérifiez-le puis envoyez.
5. Pour modifier ou supprimer un modèle : droits `all.project.message-template.update` ou `.delete`, `all.project.sms-template.update` ou `.delete`, `all.project.event-description-template.update` ou `.delete`.

Droits requis : voir chaque étape. Après la création, le modèle apparaît dans la liste de tous les utilisateurs du projet.

Sources d’interface : `components/sms-template/`, `components/message-template/`, `components/event-description-template/`, `components/prospect/sms/Slide.vue`, `components/prospect/message/Slide.vue`, `lang/fr.json` (`sms_template.*`, `message_template.*`, `event_description_template.*`).

## Questions fréquentes
**Comment créer un modèle de SMS ?** Fiche d’un prospect > « SMS » > « Templates SMS » > « Ajouter ».

**Comment personnaliser un e-mail avec le prénom du prospect ?** Dans un modèle de message, écrivez `{prospect.first_name}`; il est remplacé quand on choisit le modèle.

**Pourquoi je ne vois pas un modèle ?** Vérifiez que vous êtes dans le bon projet et que vous avez accès au panneau « SMS » ou « Message » (droits d’envoi). Les modèles ne s’affectent pas par utilisateur.

**Combien de pièces jointes dans un modèle de message ?** 10 au plus.
