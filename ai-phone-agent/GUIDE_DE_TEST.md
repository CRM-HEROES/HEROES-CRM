# Guide de test — Assistant vocal & Appel IA

Le projet tourne en local :
- CRM : http://127.0.0.1:8000
- Les assets frontend se recompilent automatiquement (`npm run watch`)

## 1. Assistant vocal interne (bouton micro flottant)

C'est la fonctionnalité la plus simple à tester : ta clé `GEMINI_API_KEY`
est configurée et fonctionne (vérifié : la génération de jeton Gemini
répond correctement).

**Étapes :**
1. Connecte-toi au CRM.
2. Repère le bouton micro rond flottant (en bas à droite de l'écran par
   défaut — tu peux le glisser-déposer ailleurs, sa position est mémorisée).
3. Clique dessus. Autorise l'accès au micro si le navigateur le demande.
4. Le bouton doit passer par : gris (connexion) → bleu (à l'écoute) →
   orange (l'IA répond), avec des anneaux qui pulsent selon le niveau
   sonore réel.
5. Parle ("Bonjour, tu m'entends ?") et vérifie que tu entends une réponse
   vocale de Gemini.
6. Teste l'interruption : parle pendant que l'IA répond, elle doit
   s'arrêter immédiatement.
7. Clique à nouveau sur le bouton pour raccrocher — vérifie que l'icône
   micro du navigateur (dans la barre d'adresse) s'éteint bien.

**En cas de problème :** ouvre la console navigateur (F12), les logs sont
préfixés `[VoiceAssistant ...]`.

## 2. Bouton "Appeler avec l'IA" sur un prospect

Cette fonctionnalité **ne peut pas encore aboutir à un vrai appel** :
l'infrastructure téléphonique (extension Kavkom dédiée + serveur
FreeSWITCH + service `ai-phone-agent`) n'est pas encore déployée. Ce
qu'on peut vérifier aujourd'hui, c'est que l'interface et les
vérifications côté serveur fonctionnent correctement et échouent
proprement (message clair, pas de plantage) :

**Étapes :**
1. Ouvre la fiche d'un prospect ayant un numéro de téléphone.
2. Ouvre le panneau d'appel (icône téléphone), onglet Kavkom.
3. Le bouton **"Appeler avec l'IA"** doit apparaître à côté du bouton
   "Appeler" existant.
4. Clique dessus.
5. **Résultat attendu pour l'instant** : un message d'erreur clair du type
   *"L'agent vocal IA n'est pas configuré (URL du service ou secret
   manquant)"* — c'est normal, `AI_PHONE_AGENT_BRIDGE_URL` et
   `AI_PHONE_AGENT_SHARED_SECRET` ne sont pas encore renseignés dans
   `.env` puisque le service `ai-phone-agent` n'est pas déployé.
6. Vérifie qu'aucune erreur 500 ne s'affiche (juste le message ci-dessus)
   et que le reste de la page reste utilisable.

Ce test confirme que le bouton est bien câblé côté CRM ; le vrai appel à
trois (utilisateur + prospect + IA) ne sera testable qu'une fois
l'infrastructure FreeSWITCH en place (voir `ai-phone-agent/README.md`).

## 3. Ce qui n'est pas testable aujourd'hui

- Un appel réel géré par l'IA (nécessite FreeSWITCH + extension Kavkom
  dédiée).
- Le remplissage automatique de la fiche prospect après un appel IA
  (dépend du point précédent).

## Si quelque chose ne va pas

- Logs Laravel : `storage/logs/laravel.log` (et
  `storage/logs/ai-phone-agent.log` pour tout ce qui touche à
  l'assistant/agent IA une fois qu'il y a de l'activité).
- Logs navigateur : F12 → Console.
