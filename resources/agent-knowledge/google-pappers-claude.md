# Google, Pappers et clé Claude

## À quoi ça sert
Trois intégrations accessibles depuis « Paramètres » : relier des comptes Google (fichiers, agenda, contacts), rechercher et importer des propriétaires avec Pappers, et enregistrer sa clé Claude pour faire fonctionner les agents IA.

## Comment faire
Icône engrenage > « Paramètres » : les lignes « Google Drive », « Google Calendar », « Google Contact », « claude.ai » et « Analyser des données grâce à l'IA » (Pappers). Une coche verte indique qu’un compte ou une clé est déjà enregistré.

## Règles
- **Google Drive** (« Associer un compte Google Drive pour les fichiers de vos prospects ») : lié au projet. Il sert aussi à créer un Google Sheet lors d’un export de prospects. Disponible avec `all.project`.
- **Google Calendar** (« Associer un compte Google Calendar à vos RDVs ») : lié à l’utilisateur, pour relier ses rendez-vous à son agenda Google.
- **Google Contact** (« Associer un compte Google Contact à vos prospects ») : lié à l’utilisateur.
- **Clé Claude** (« claude.ai », « Gérer ma clé API Claude ») : propre à chaque utilisateur. Sans clé valide, ses agents IA ne répondent pas.
- **Pappers** (« Analyser des données grâce à l'IA ») : recherche des propriétaires de biens immobiliers et ajoute directement les résultats comme prospects du projet.

## Limites
Cliquer une ligne Google déjà liée propose de la dissocier. La clé Claude complète n’est affichée qu’une fois, au moment de sa création chez Claude : conservez-la. L’intégration Pipedrive existe dans le code mais sa ligne est désactivée dans les réglages. Pappers dépend d’un accès au service Pappers configuré sur le serveur.

## Dans l'interface
Chemin : « Paramètres » (`components/layouts/setting/Layout.vue`).

1. **Relier Google :** cliquez « Google Drive », « Google Calendar » ou « Google Contact », puis connectez-vous à votre compte Google et acceptez. Pour dissocier, cliquez de nouveau et confirmez.
2. **Enregistrer sa clé Claude :** cliquez « claude.ai ». L’aide « Comment obtenir votre clé API ? » explique : ouvrir la page des clés API (« Ouvrir la page des clés API »), se connecter ou créer un compte, dans « API keys » cliquer « Create key », nommer la clé et choisir sa durée de validité, la copier, la coller dans « Clé API », puis « Enregistrer ». Le CRM vérifie la clé : « Clé valide : connexion à l’API Claude réussie. » ou la cause du refus (clé invalide ou expirée, accès refusé, aucun modèle, trop de demandes, vérification impossible). Pour remplacer une clé, saisissez-en une nouvelle.
3. **Pappers :** cliquez « Analyser des données grâce à l'IA », renseignez les filtres voulus (adresse, copropriété, propriétaire, occupant, vente, bâtiment, diagnostic de performance énergétique, fonds de commerce, permis…) puis lancez la recherche : les propriétaires trouvés sont ajoutés aux prospects du projet. Limitez les filtres pour ne pas importer trop de lignes.

Droits requis : `all.project` pour Google Drive; aucune autorisation particulière pour les autres lignes. Après l’enregistrement, la ligne affiche une coche verte (Google, Claude) ou les prospects apparaissent dans le tableau (Pappers).

Sources d’interface : `components/layouts/setting/Layout.vue`, `components/settings/Claude.vue`, `components/api/pappers/`, `lang/fr.json` (`setting.google_*`, `setting.claude.*`).

## Questions fréquentes
**Comment faire marcher mes agents IA ?** « Paramètres » > « claude.ai » : enregistrez votre clé API Claude.

**Ma clé Claude est refusée.** Le message indique la cause : clé invalide ou expirée, accès refusé ou aucun modèle disponible; créez une nouvelle clé.

**Comment synchroniser mes rendez-vous avec Google Calendar ?** « Paramètres » > « Google Calendar » et connectez votre compte.

**À quoi sert « Analyser des données grâce à l'IA » ?** À rechercher des propriétaires via Pappers et à les ajouter comme prospects.
