# Guide d'installation FreeSWITCH — pour la personne qui a accès au serveur

Ce guide part d'un serveur Linux tout neuf (Ubuntu ou Debian) et arrive à
un FreeSWITCH opérationnel, prêt pour le service `ai-phone-agent/`.

**Comment lire ce guide :** chaque étape commence par une explication de
ce qu'on fait et pourquoi, suivie des commandes à copier-coller telles
quelles dans le terminal du serveur (connexion SSH), puis de ce à quoi
doit ressembler le résultat. Fais les étapes dans l'ordre, une par une —
ne saute pas au bloc de commandes sans lire l'explication, chaque étape
dépend souvent de la précédente.

**Règle de sécurité à respecter partout dans ce guide :** aucune valeur
secrète (mot de passe, jeton) ne doit être écrite dans un fichier de ce
dépôt Git. Les fichiers `.env` et les fichiers de configuration
FreeSWITCH avec les vraies valeurs restent uniquement sur le serveur, et
ne sont jamais commités.

## Prérequis — à vérifier avant de commencer

- **Un accès SSH** au serveur, avec les droits `sudo` (ou directement en
  root). Concrètement : tu dois pouvoir ouvrir un terminal sur ta machine
  et faire `ssh utilisateur@adresse-du-serveur` pour arriver dans un
  terminal *du serveur*, pas de ton PC.
- **Le système d'exploitation du serveur** : Ubuntu 22.04/24.04 ou Debian
  11/12. Si tu ne sais pas, une fois connecté en SSH, tape `lsb_release
  -a` et regarde la ligne "Distributor ID" et "Codename".
- **Les identifiants de l'extension Kavkom dédiée à l'IA**, déjà
  récupérés plus tôt dans ce projet : le numéro d'extension (`1000`), le
  mot de passe SIP (la valeur à 20 caractères, pas le mot de passe du
  compte Kavkom), et le domaine SIP (`aria-madacom.kavkom.com`). Garde-les
  sous la main, ils servent à l'étape 3.
- **Un compte SignalWire gratuit** — pas pour un service payant ici,
  juste pour télécharger FreeSWITCH (SignalWire est l'éditeur qui
  maintient FreeSWITCH aujourd'hui, et exige un jeton gratuit pour accéder
  à leurs paquets préconstruits). Va sur
  https://id.signalwire.com/personal_access_tokens et génère un "Personal
  Access Token" (bouton généralement en haut à droite) — copie-le
  immédiatement dans un endroit sûr, il ne sera réaffichable qu'une fois.

---

## Étape 1 — Installer FreeSWITCH lui-même

**Ce qu'on fait :** FreeSWITCH n'est pas dans les dépôts standards
d'Ubuntu/Debian — il faut ajouter le dépôt officiel de SignalWire, lui
donner le jeton d'accès créé juste avant, puis installer le paquet
"tout compris" (`freeswitch-meta-all`), qui inclut tous les modules
standards (téléphonie SIP, conférence, etc.).

```sh
sudo apt update
sudo apt install -y gnupg2 wget lsb-release software-properties-common apt-transport-https curl
```
*(Ces paquets sont juste des outils nécessaires pour ajouter un dépôt
sécurisé — rien de spécifique à FreeSWITCH pour l'instant.)*

Colle ton token SignalWire dans une variable, pour ne pas avoir à le
retaper à chaque commande :
```sh
TOKEN="colle_ton_token_signalwire_ici"
```

Télécharge la clé de sécurité du dépôt SignalWire (permet à `apt` de
vérifier que les paquets ne sont pas falsifiés) :
```sh
curl --http1.1 -fsSL https://freeswitch.signalwire.com/repo/deb/debian-release/signalwire-freeswitch-repo.gpg \
  | sudo gpg --dearmor -o /usr/share/keyrings/signalwire-freeswitch-repo.gpg
```

Enregistre ton jeton dans un fichier d'authentification (c'est ce qui
prouve à SignalWire que tu as le droit de télécharger leurs paquets) :
```sh
echo "machine freeswitch.signalwire.com login signalwire password $TOKEN" \
  | sudo tee /etc/apt/auth.conf.d/freeswitch.conf
sudo chmod 600 /etc/apt/auth.conf.d/freeswitch.conf
```
*(Le `chmod 600` restreint la lecture de ce fichier à l'administrateur
seul — il contient ton jeton en clair.)*

Ajoute le dépôt à la liste des sources de paquets du système :
```sh
echo "deb [signed-by=/usr/share/keyrings/signalwire-freeswitch-repo.gpg] https://freeswitch.signalwire.com/repo/deb/debian-release/ $(lsb_release -cs) main" \
  | sudo tee /etc/apt/sources.list.d/freeswitch.list
```
*(`$(lsb_release -cs)` détecte automatiquement le nom de code de ta
distribution — pas besoin de le connaître toi-même.)*

Enfin, installe :
```sh
sudo apt update
sudo apt install -y freeswitch-meta-all
```
Cette dernière commande peut prendre plusieurs minutes (beaucoup de
paquets à télécharger).

**Comment vérifier que ça a marché :**
```sh
sudo systemctl status freeswitch
```
Tu dois voir une ligne verte avec `active (running)`. Si c'est le cas,
FreeSWITCH tourne déjà, avec sa configuration par défaut (encore vide de
Kavkom pour l'instant — normal, on configure ça plus loin). Appuie sur
`q` pour sortir de cet écran de statut.

**Si ça ne marche pas :** une erreur "401 Unauthorized" pendant `apt
update` veut dire que le token est invalide ou mal collé — regénère-en un
et recommence à partir de la ligne `TOKEN=`.

---

## Étape 2 — Installer mod_audio_stream

**Ce qu'on fait :** `freeswitch-meta-all` installe FreeSWITCH avec ses
modules standards, mais pas `mod_audio_stream` — c'est un module tiers
(pas édité par SignalWire) qui permet à FreeSWITCH d'envoyer l'audio d'un
appel vers un service externe (notre service `ai-phone-agent`, qui lui
fera le lien avec Gemini). Il n'existe pas en paquet tout prêt : il faut
le compiler depuis son code source.

Installe d'abord les outils de compilation et les bibliothèques dont il a
besoin :
```sh
sudo apt-get -y install git libfreeswitch-dev libssl-dev zlib1g-dev libevent-dev libspeexdsp-dev cmake build-essential
```

Récupère le code source :
```sh
git clone https://github.com/amigniter/mod_audio_stream.git
cd mod_audio_stream
git submodule init && git submodule update
```
*(Les "submodules" sont d'autres bouts de code dont ce module dépend —
cette commande les télécharge aussi.)*

Compile et installe :
```sh
mkdir build && cd build
cmake -DCMAKE_BUILD_TYPE=Release ..
make
sudo make install
```
`make` peut prendre plusieurs minutes selon la puissance du serveur —
c'est normal, ne l'interromps pas.

**Active le module** dans la configuration de FreeSWITCH. Ouvre ce
fichier avec un éditeur de texte en ligne de commande (nano est le plus
simple si tu ne connais pas vim) :
```sh
sudo nano /etc/freeswitch/autoload_configs/modules.conf.xml
```
Dans ce fichier, cherche la section qui commence par `<modules>` et
ajoute cette ligne n'importe où avant `</modules>` :
```xml
<load module="mod_audio_stream"/>
```
Vérifie aussi que ces deux lignes existent déjà dans le fichier et ne
sont **pas** transformées en commentaire (un commentaire XML ressemble à
`<!-- <load module="..."/> -->` — s'il y a `<!--` et `-->` autour, retire
ces symboles) :
```xml
<load module="mod_loopback"/>
<load module="mod_conference"/>
```
Pour sauvegarder et quitter nano : `Ctrl+O` puis Entrée (sauvegarder),
puis `Ctrl+X` (quitter).

Redémarre FreeSWITCH pour que le nouveau module soit chargé :
```sh
sudo systemctl restart freeswitch
```

**Comment vérifier que ça a marché :**
```sh
sudo fs_cli -x "module_exists mod_audio_stream"
```
Doit répondre `true`.

---

## Étape 3 — Connecter FreeSWITCH à Kavkom (le "gateway")

**Ce qu'on fait :** un "gateway" dans FreeSWITCH, c'est simplement la
déclaration d'un compte SIP externe sur lequel FreeSWITCH doit
s'enregistrer — exactement comme un téléphone physique s'enregistre sur
une ligne. Ici, on lui donne les identifiants de l'extension Kavkom
`1000` créée spécialement pour l'IA, pour que FreeSWITCH puisse
s'enregistrer dessus et, une fois enregistré, appeler à travers elle
(aussi bien vers d'autres extensions Kavkom que vers des numéros
extérieurs).

Crée le fichier de configuration du gateway :
```sh
sudo nano /etc/freeswitch/sip_profiles/external/kavkom.xml
```
Colle ce contenu (c'est le même modèle que
`ai-phone-agent/freeswitch/gateway-example.xml` dans ce dépôt) :
```xml
<include>
    <gateway name="kavkom">
        <param name="username" value="1000"/>
        <param name="password" value="LE_MOT_DE_PASSE_SIP_DEJA_RECUPERE"/>
        <param name="realm" value="aria-madacom.kavkom.com"/>
        <param name="proxy" value="aria-madacom.kavkom.com"/>
        <param name="register" value="true"/>
        <param name="expire-seconds" value="600"/>
        <param name="retry-seconds" value="30"/>
        <param name="caller-id-in-from" value="true"/>
    </gateway>
</include>
```
**Remplace `LE_MOT_DE_PASSE_SIP_DEJA_RECUPERE`** par le vrai mot de passe
SIP à 20 caractères (celui obtenu via `resolveExtension`, pas
"15janvier3002@" qui est le mot de passe du compte Kavkom, différent).
Sauvegarde (`Ctrl+O`, Entrée, `Ctrl+X`).

Demande à FreeSWITCH de relire la configuration SIP et de tenter
l'enregistrement :
```sh
sudo fs_cli -x "sofia profile external rescan"
sudo fs_cli -x "sofia status gateway kavkom"
```

**Comment vérifier que ça a marché :** la deuxième commande doit afficher
un statut `REGED` (= "registered", enregistré avec succès) après
quelques secondes. Si tu relances la commande juste après avoir créé le
gateway et qu'elle affiche encore `NOREG` ou `TRYING`, attends 10-15
secondes et réessaie.

**Si ça reste sur `FAILED` :** le mot de passe ou le `realm` sont
probablement incorrects — revérifie qu'ils correspondent exactement à ce
qui a été récupéré depuis Kavkom, sans espace ajouté par erreur.

---

## Étape 4 — Ajouter le "poste fantôme" de l'IA (dialplan)

**Ce qu'on fait :** l'IA n'est pas un vrai téléphone — c'est un canal
"loopback" (une sorte de ligne virtuelle interne à FreeSWITCH) qui va
juste décrocher et rester silencieusement en attente dans la conférence.
C'est sur ce canal-là que le service `ai-phone-agent` viendra ensuite
brancher l'audio de/vers Gemini. Cette étape déclare ce comportement
("quand on appelle l'extension interne `ai-agent-tap`, décroche et
attends").

Ouvre le fichier de dialplan principal :
```sh
sudo nano /etc/freeswitch/dialplan/default.xml
```
Cherche la ligne `<context name="default">` et colle ce bloc juste après
(le même contenu que `ai-phone-agent/freeswitch/dialplan-example.xml`
dans ce dépôt — rien à modifier, pas d'identifiant ici) :
```xml
<extension name="ai-agent-tap">
    <condition field="destination_number" expression="^ai-agent-tap$">
        <action application="answer"/>
        <action application="park"/>
    </condition>
</extension>
```
Sauvegarde (`Ctrl+O`, Entrée, `Ctrl+X`).

---

## Étape 5 — Créer la salle de conférence à 3

**Ce qu'on fait :** on déclare un "profil de conférence" — un modèle de
salle où l'utilisateur du CRM, le prospect, et le canal IA seront réunis
et s'entendront tous les uns les autres. C'est la même logique qu'une
conférence téléphonique classique, en plus simple (pas de code PIN, pas
de modération, puisque ce sont toujours les 3 mêmes types de
participants).

Ouvre :
```sh
sudo nano /etc/freeswitch/autoload_configs/conference.conf.xml
```
Cherche la section `<profiles>` et colle dedans (contenu identique à
`ai-phone-agent/freeswitch/conference-profile-example.xml` de ce dépôt,
rien à modifier) :
```xml
<profile name="ai-agent">
    <param name="rate" value="16000"/>
    <param name="domain" value="$${domain}"/>
    <param name="caller-id-name" value="Heroes CRM"/>
    <param name="caller-controls" value="none"/>
    <param name="moderator-controls" value="none"/>
</profile>
```
Sauvegarde, puis recharge toute la configuration FreeSWITCH pour que les
étapes 4 et 5 prennent effet :
```sh
sudo fs_cli -x "reloadxml"
```

---

## Étape 6 — Sécuriser l'accès de contrôle (Event Socket / ESL)

**Ce qu'on fait :** le service `ai-phone-agent` ne parle pas à FreeSWITCH
en SIP, mais via un canal de contrôle séparé appelé "Event Socket"
(ESL) — c'est par là qu'il va lui dire "décroche cette ligne", "démarre
l'envoi audio", etc. Par défaut, FreeSWITCH protège ce canal avec un mot
de passe **identique sur toutes les installations du monde**
(`ClueCon`) — donc à changer absolument, sinon n'importe qui connaissant
ce mot de passe par défaut et ayant accès au bon port pourrait prendre le
contrôle du serveur téléphonique.

Ouvre :
```sh
sudo nano /etc/freeswitch/autoload_configs/event_socket.conf.xml
```
Trouve la ligne avec `ClueCon` et remplace par un mot de passe fort que
tu choisis :
```xml
<param name="password" value="CHOISIS_UN_MOT_DE_PASSE_FORT_ICI"/>
```
Laisse `listen-ip` sur `127.0.0.1` — ça veut dire "n'accepte les
connexions de contrôle que depuis cette même machine", ce qui est ce
qu'on veut puisque `ai-phone-agent` va tourner sur ce même serveur
(étape 8). Ne mets jamais `0.0.0.0` ici sauf si tu sais exactement ce que
tu fais côté pare-feu.

Sauvegarde, puis redémarre :
```sh
sudo systemctl restart freeswitch
```

Retiens bien ce mot de passe — il sera nécessaire à l'étape 8, dans le
fichier `.env` de `ai-phone-agent` (variable `FS_ESL_PASSWORD`), jamais
dans un fichier Git.

---

## Étape 7 — Vérification avant de brancher le service Node

Une petite vérification manuelle pour confirmer que tout ce qui précède
est bien en place avant d'ajouter la dernière pièce.

Ouvre la console interactive de FreeSWITCH :
```sh
sudo fs_cli
```
Le prompt doit changer pour ressembler à `freeswitch@ton-serveur>`. Tape :
```
sofia status gateway kavkom
```
→ doit toujours afficher `REGED`. Tape ensuite `/exit` pour quitter la
console.

Si ce statut est bon, FreeSWITCH est prêt.

---

## Étape 8 — Déployer et lancer le service ai-phone-agent

**Ce qu'on fait :** on installe maintenant le programme (déjà écrit et
testé) qui fait le lien entre FreeSWITCH et Gemini, et on le fait tourner
en permanence en arrière-plan sur ce même serveur.

Récupère le code du CRM sur ce serveur (si ce n'est pas déjà fait) :
```sh
git clone <url_du_repo_HEROES-CRM>
cd HEROES-CRM/ai-phone-agent
npm install
```

Crée son fichier de configuration à partir du modèle :
```sh
cp .env.example .env
nano .env
```
Renseigne dans ce fichier (en remplaçant les valeurs vides) :
- `GEMINI_API_KEY` : la clé Gemini utilisée par le CRM.
- `AI_PHONE_AGENT_SHARED_SECRET` : une valeur secrète de ton choix,
  **qui doit être exactement identique** à celle mise dans le `.env` du
  CRM Laravel en production — c'est ce qui permet aux deux services de se
  faire confiance mutuellement.
- `LARAVEL_BASE_URL` : l'adresse web publique du CRM (ex.
  `https://crm.tondomaine.com`).
- `FS_ESL_PASSWORD` : le mot de passe choisi à l'étape 6.
- `WS_PUBLIC_URL` : `ws://127.0.0.1:4001/` si tout tourne sur ce même
  serveur (cas normal).

Sauvegarde (`Ctrl+O`, Entrée, `Ctrl+X`).

Installe un superviseur de processus (pm2), qui garde le service actif
en permanence et le relance automatiquement s'il plante ou si le serveur
redémarre :
```sh
sudo npm install -g pm2
pm2 start index.js --name ai-phone-agent
pm2 save
pm2 startup
```
La dernière commande affiche une ligne de commande à copier-coller et
exécuter (elle configure le démarrage automatique au boot du serveur) —
fais-le, c'est indiqué directement dans le message affiché.

**Comment vérifier que ça a marché :**
```sh
pm2 logs ai-phone-agent
```
Tu dois voir des lignes du type `[ESL] Connected to FreeSWITCH.` et
`[HTTP] Control API listening on port 4000.` sans erreur en rouge. `Ctrl+C`
pour sortir de l'affichage des logs (le service continue de tourner en
arrière-plan).

---

## Étape 9 — Le dernier réglage côté Kavkom

Dans le dashboard Kavkom, va sur l'extension `1000` ("IA CRM") et
attribue-lui un numéro de sortie (DID) valide — un vrai numéro de
téléphone déjà rattaché à votre compte Kavkom. Sans ça, l'IA ne pourra
pas appeler un prospect : l'appel sortant sera automatiquement refusé,
exactement comme documenté pour les postes humains dans les paramètres
Kavkom du CRM.

---

## Étape 10 — Test final, de bout en bout

Dans le CRM (le vrai site web, pas en local), ouvre une fiche prospect de
test et clique sur **"Appeler avec l'IA"**. En parallèle, sur le
serveur, regarde les logs en direct :
```sh
pm2 logs ai-phone-agent
```
Ce que tu dois voir apparaître, dans l'ordre : la création de la
conférence, la connexion de `mod_audio_stream`, puis la connexion et la
réponse de Gemini. Si ton propre poste Kavkom sonne bien et que tu
entends l'IA parler au décrochage, l'installation est terminée et
fonctionnelle.
