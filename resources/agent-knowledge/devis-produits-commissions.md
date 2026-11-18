# Devis : produits, états, étapes, actions et commissions

## À quoi ça sert
Un devis (appelé aussi commande) enregistre ce qu’un prospect achète : des produits avec quantité et prix, un état, des étapes de traitement, des documents et les commissions des commerciaux. Cette fiche explique comment le remplir et comment configurer ce qui le compose.

## Comment faire
- **Créer un devis :** fiche du prospect > « Devis » > « Nouveau devis », ou barre du haut > « Devis » > « Nouveau devis ».
- **Le compléter :** ouvrez le devis; il a six onglets : « Produits », « Commissions », « Etapes », « Documents », « Autre infos » et « Etat ».
- **Configurer ce qui le compose :** fiche du projet > blocs « Etats de devis », « Etapes de devis », « Actions sur les devis » et « Produits ».

## Règles
- **Produits d’un devis :** « Ajouter {product} dans le devis » demande la « Quantité », le « Prix », la « Devise », la « Taxe en % » et l’option « Taxe incluse ». Le bouton « Ajouter dans le devis » valide la ligne.
- **Un produit du catalogue** a un nom, une description, un prix, une devise, une taxe en pourcentage, l’option « Taxe incluse » et des images; il peut être archivé. Titre : « Nouveau produit ».
- **États, étapes et actions** ont chacun un nom, une description, une couleur et une couleur de fond. Titres : « Nouvel état de devis », « Nouvelle étape de devis » et « Nouveau type d'acion sur les devis » (l’orthographe de l’interface). Un état décrit où en est le devis (par exemple signé ou payé); des étapes se cochent au fil du traitement; une action est un acte commercial attribué à un utilisateur (par exemple vendre ou installer) et sert à calculer les commissions.
- **Commissions :** l’onglet « Commissions » du devis affiche « Total », « Reste » et « Payé ». « Marquer comme payé » et « Marquer comme non payé » suivent le règlement; « Choisir un utilisateur ... » désigne le commercial concerné; « Commission non définie » indique qu’aucune règle n’existe encore pour ce produit, cette action et cet utilisateur.
- **Gérer les commissions** (bouton du même nom, affiché avec `all.user.commission.add`, donc en pratique aux administrateurs; le serveur demande `all.project.user.commission.add`) : on sélectionne des produits, des actions et des utilisateurs, puis on choisit le « Type » (« En pourcentage » ou « En Fixe »), la valeur, et « Calculer à partir du prix du produit en » TTC ou HT. Une commission fixe vaut la valeur multipliée par la quantité; une commission en pourcentage s’applique au prix du produit.
- Un état, une étape ou une action n’est utilisable par un utilisateur que s’il lui est affecté (« Etats de devis affectés », « Etapes de devis affectées », « Actions sur les devis affectées » du profil utilisateur ou rôle).

## Limites
Cette fiche ne remplace pas la comptabilité : le CRM ne produit ni numérotation de factures ni suivi légal de paiement. Pour un PDF de devis ou de facture, voir les modèles de documents. Les montants se lisent dans le devis; ne les recalcule pas à la main.

## Dans l'interface
Chemin : « Devis » (barre du haut) ou fiche prospect > « Devis » (`components/order/`, `components/prospect/order/`).

1. **Tableau des devis :** « Paramètre des colonnes » (« Gérer les colonnes de {project} ») propose notamment « Prospect », « Produits », « Etat », « Etapes », « Commissions » et le total des commissions; « Nombre de chiffres après virgule: » règle l’arrondi des montants. Les filtres sont « Filtrer par actions », « Filtrer par produits », « Filtrer par état » et « Filtrer par étapes », chacun avec une option « Avec » ou « Sans ». « Nombre de devis par page » règle la pagination.
2. **Créer ou supprimer :** « Nouveau devis » (droit `all.order.add` depuis le tableau, `all.prospect.order.add` depuis la fiche); la suppression d’un devis demande `all.prospect.order.delete`, et « Supprimer définitivement » dans le tableau demande `all.order.delete` avec confirmation.
3. **Produits d’un devis :** onglet « Produits », puis « Ajouter dans le devis ». Créer un produit depuis cet onglet demande `all.project.order.product.add`.
4. **Configuration :** pour créer un état, une étape ou une action : `all.project.order.status.add`, `all.project.order.step.add`, `all.project.order.action.add`; pour les modifier ou supprimer, les droits `.update` et `.delete` correspondants. Pour un produit : `all.project.order.product.add`, `.update`, `.delete`.
5. **Documents d’un devis :** onglet « Documents », « Ajouter » (droit `all.project.document.add`) génère un document à partir d’un modèle (voir la fiche Documents).

Droits requis : voir chaque étape. Après chaque ajout, le devis et le tableau se mettent à jour.

Sources d’interface : `components/order/`, `components/prospect/order/`, `components/commission/`, `components/product/`, `lang/fr.json` (`order.*`, `prospect.order.*`, `commission.*`, `product.*`).

## Questions fréquentes
**Comment ajouter un produit à un devis ?** Ouvrez le devis > « Produits » > choisissez le produit > quantité, prix, taxe > « Ajouter dans le devis ».

**Comment définir la commission d’un commercial ?** Dans l’onglet « Commissions » du devis > « Gérer les commissions » (réservé en pratique aux administrateurs) : produits, actions, utilisateurs, type (pourcentage ou fixe) et valeur.

**Comment marquer une commission comme payée ?** Onglet « Commissions » > « Marquer comme payé ».

**Comment créer un nouvel état de devis ?** Fiche du projet > bloc « Etats de devis » > ajout (droit `all.project.order.status.add`).

**Pourquoi je ne vois pas un état ou une étape ?** Il ne m’est pas affecté.
