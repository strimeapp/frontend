Strime versions
===============

This file is made to list the evolutions of Strime over the time.

v1.0.15
------

Launch date: 2017/01/26

  * IMPROVEMENT: On retire de la page facturation les formulaires de gestion de CB et de code promo.
  * FEATURE: Désactivation de la page offres, et suppression des fonctions liées.
  * FEATURE: Mise en place d'un écran partenaires dans le bas de la page d'accueil.
  * IMPROVEMENT: Modification de l'écran d'accueil.
  * FEATURE: Mise en place d'un formulaire d'inscription à la newsletter dans le footer.
  * IMPROVEMENT: On remplace le helper Slack par Slackify.
  * FEATURE: Mise en place de la possibilité de modifier le nom d'une vidéo.
  * FEATURE: Si l'utilisateur change le son de la vidéo, se souvenir de son choix, même s'il navigue dans le site.
  * IMPROVEMENT: On désactive la gestion de la vidéo avec la barre espace lorsque l'utilisateur envoie un feedback.
  * IMPROVEMENT: Mise en place d'un exception listener pour mieux gérer les erreurs éventuelles, notamment celle que l'on renvoit en cas de connexion indue à l'API.
  * IMPROVEMENT: On crée un helper avec les fonctions du back pour factoriser tout ça.
  * IMPROVEMENT: On crée un helper avec les formulaires du back pour factoriser tout ça.
  * IMPROVEMENT: Dans le formulaire d'upload d'une vidéo, on gère le nom de fichier servant à l'upload dans un champ séparé pour éviter tout conflit.
  * IMPROVEMENT: On avertit un utilisateur qu'il dépasse son quota avant même le début de l'upload.
  * BUG: Correction d'un bug d'affichage du marqueur quand on clique sur un commentaire (çà n'affichait pas le bon).
  * IMPROVEMENT: On force le lecteur à suivre le mouvement du scroll quand on force celui-ci au moment d'un clic sur un commentaire.
  * FEATURE: On retire du BO ce qui permet de changer d'offre (bouton dans le header + section changer d'offre dans la partie facturation).

v1.0.14
------

Launch date: 2017/01/05

  * IMPROVEMENT: Si le sous-menu profil est ouvert, et que je clique n'importe où dans l'écran, ca le ferme.
  * FEATURE: Mise en place de la validation de l'adresse mail.
  * IMPROVEMENT: On gère le cas de l'échec au login si le compte de l'utilisateur a été désactivé.
  * BUG: On remet en place le lien de téléchargement direct des factures dans l'admin.
  * BUG: Correction d'un bug dans le calcul du montant total à indiquer sur la facture à l'inscription à l'offre payante d'un ressortissant UE avec numéro de TVA intra.
  * BUG: Correction du bug de calcul de la répartition HT/TTC au moment des requêtes Stripe ou de la regénération.
  * IMPROVEMENT: On ouvre le lien Trust Badge du footer dans un nouvel onglet pour éviter toute confusion.
  * IMPROVEMENT: On désactive le contrôle de la lecture avec la barre espace quand la modal de partage est ouverte.
  * IMPROVEMENT: On vide les champs de la modal de partage quand on la ferme.
  * IMPROVEMENT: On utilise Guzzle pour gérer les requêtes cURL.
  * IMPROVEMENT: On utilise la fonction Twig absolute_url à la place de app.request.
  * BUG: Correction d'un bug au moment de la suppression d'un vidéo.
  * BUG: Correction d'un bug d'affichage de la feedback box suite à l'ajout dynamique des encodages.
  * FEATURE : Mise en place de tests sur les classes TokenGenerator et HeadersAuthorization.
  * IMPROVEMENT: Quand on supprime une vidéo qui est dans un projet, on reste dans le dossier du projet.
  * IMPROVEMENT: Dans le nom pré-saisi de la vidéo, on retire l'extension.
  * IMPROVEMENT: On empêche l'utilisateur de saisir un espace lorsqu'il tape un email pour partager une vidéo, se connecter ou créer un compte.
  * BUG: On passe un fix sur un problème au moment de l'upgrade de l'offre.
  * FEATURE: Mise en place d'une progress bar permettant de suivre le chargement des pages.
  * FEATURE: Mise en place de la confirmation de l'adresse email suite à l'inscription.
  * IMPROVEMENT: Passage de la version 5.2.1 à la version 5.12.6 du lecteur VideoJS.
  * FEATURE: Affichage dans la liste des utilisateurs si les adresses mail ne sont pas confirmées.
  * IMPROVEMENT: Création d'un helper contenant toutes les fonctions utilisées dans la section Factures de l'admin.
  * IMPROVEMENT: Factorisation du formulaire de recherche de factures dans l'admin.
  * IMPROVEMENT: On affiche un lien de téléchargement des factures ainsi que le nom de l'utilisateur, même quand le compte a été supprimé.

v1.0.13
-------

Launch date: 2016/11/21

  * IMPROVEMENT: On ajoute un timestamp dans les logs des CRON jobs.
  * FEATURE: Sur l'écran vidéo, lorsque l'utilisateur appuie sur ESC, ca ferme la comment box.
  * BUG: Correction d'une faille permettant de réaliser des injections JS : on sanitize tous les commentaires dès leur ajout.
  * BUG: Correction d'un bug dans l'upload de vidéos dont le nom du fichier contient des points.
  * IMPROVEMENT: On affiche dans Slack l'environnement d'où provient la notification.
  * BUG: Correction d'un problème d'affichage des photos dans la liste des utilisateurs dans l'admin.
  * IMPROVEMENT: On passe en HTTPS sur l'environnement de test.
  * FEATURE: Mise en place d'une pagination et de nouvelles fonctionnalités dans la liste des utilisateurs dans l'admin.
  * IMPROVEMENT: On colore le marquer sur la vidéo lorsque l'utilisateur clique dessus ou sur un commentaire en sidebar.
  * IMPROVEMENT: On colore le marquer sur la timeline lorsque l'utilisateur clique dessus ou sur un commentaire en sidebar.
  * IMPROVEMENT: Quand on clique sur un marqueur dans la timeline de la vidéo, on active le commentaire correspondant et on fait glisser l'écran vers celui-ci.
  * IMPROVEMENT: On masque l'éventuelle scrollbar horizontale dans la page vidéo.
  * FEATURE: On transforme automatiquement les liens dans les commentaires.
  * IMPROVEMENT: On met un poil de marge en dessous du champ de texte dans la popup de commentaire pour que le loader ne soit pas coupé.
  * FEATURE: Création d'un sitemap pour Strime contenant toutes les pages publiques.
  * FEATURE: Mise en place d'une page reprenant la liste de toutes les nouvelles fonctionnalités annoncées dans l'app.
  * FEATURE: On affiche les nouveaux encodages dans le dashboard sans avoir à recharger la page.
  * IMPROVEMENT: Lorsque l'on dit a l'utilisateur qu'il a atteint son quota de vidéos au moment de l'upload, afficher une croix pour fermer la modal, sinon il est coincé.
  * IMPROVEMENT: On met à jour automatiquement le statut des encodages dans le dashboard.
  * IMPROVEMENT: Lorsque l'utilisateur laisse un commentaire dans le bas de la vidéo, on affiche la comment box au-dessus du marqueur.
  * IMPROVEMENT: Amélioration des meta tags de la page Trust Badge.
  * IMPROVEMENT: Upgrade des bundles.

v1.0.12
-------

Launch date: 2016/10/18

  * IMPROVEMENT: On met en place une modal de confirmation de la popup de suppression d'un commentaire.
  * IMPROVEMENT: Lorsque l'on supprime un commentaire, on met à jour le décompte dans l'en-tête du flux.
  * FEATURE: Mise en place de la fonctionnalité permettant de modifier ses commentaires.
  * IMPROVEMENT: On change la mise en forme des modals de suppression (vidéo, projet, compte).
  * IMPROVEMENT: On ajoute un meta tag dans les pages vidéo et projet pour être sûr qu'elles ne soient pas indexées par les moteurs de recherche.
  * FEATURE: Mise en place d'un bouton permettant à un utilisateur de supprimer ses commentaires.
  * FEATURE: Intégration avec Slack. On envoie une alerte lorsqu'un utilisateur change d'offre ou souscrit une offre, et à chaque fois que Slack nous alerte d'un prélèvement ou d'une erreur de prélèvement.
  * IMPROVEMENT: Lorsque l'utilisateur met à jour son adresse mail, on met à jour son profil Stripe, pour le retrouver soit par son mail, soit par son ID Strime.
  * FEATURE: Création d'un CRON job permettant de faire le ménage dans la table stockant les utilisateurs ayant lu les messages de nouvelles fonctionnalités.
  * FEATURE: Message dans un bandeau annonçant les nouvelles fonctionnalités.
  * IMPROVEMENT: On place les scripts relatifs à la feedback box dans un fichier indépendant.
  * IMPROVEMENT: On retire le check TLS de l'interface publique.

v1.0.11
-------

Launch date: 2016/10/07

  * IMPROVEMENT: Fait avec amour à Sainté & Valparaiso dans le bas des factures.
  * FEATURE: création d'un CRON job qui supprime les fichiers du dossier d'upload après 2 jours.
  * BUG FIX: correction de l'affichage des avatars dans les commentaires d'une vidéo.
  * FEATURE: mise en place d'un bouton pour tuer en encodage depuis l'admin.

v1.0.10
-------

Launch date: 2016/08/29

  * Correction de coquilles dans la présentation des infos des factures
  * Correction d'une coquille dans l'enregistrement de ces infos via le webhook Stripe

v1.0.9
------

Launch date: 2016/08/23

  * Correction d'une incohérence sur le destinataire des mails de commentaires.
  * On ajoute le pourcentage d'avancement dans la liste des encodages en cours dans l'admin.
  * On ajoute l'heure dans la liste des encodages, dans l'admin.
  * On empêche la fermeture de la popup d'upload si un upload est en cours.
  * Lorsque la session de l'utilisateur prend fin, on affiche une popup pour qu'il se reconnecte.
  * On met en place les formulaires de login dans l'admin.
  * On n'affiche la date de dernière connexion d'un utilisateur dans l'admin que s'il y en a une."
  * Mise en place de la vidéo dans la page trust badge.
  * On modifie le header de la page trust badge.
  * On met en place le lien vers la page trust badge sur le front, le back et l'admin.

v1.0.8
------

Launch date: 2016/08/08

  * On généralise la gestion des flash bags / redirect à toute l'app.
  * On généralise la gestion des flash bags / redirect à toute l'admin.
  * On crée un Form Type pour TaxRates et on gère différemment l'affichage des flash bags pour masquer l'URL.
  * Correction d'un bug dans l'affichage de l'avatar des commentateurs.
  * Correction d'un bug sur la définition du propriétaire de la vidéo.
  * On ajoute un graph dans le dashboard avec l'évolution du % d'utilisateurs actifs.
  * On enlève les éléments de version mis en place pour tester la dernière version de l'API.
  * On affiche la date de la dernière connexion d'un utilisateur dans sa page profil dans l'admin et dans la liste des utilisateurs.
  * Correction d'un bug dans la gestion du propriétaire dans la page vidéo.
  * Correction d'un bug dans l'affichage des photos dans les commentaires pour les personnes ayant un compte et une photo de profil.
  * On ajoute l'heure à la liste des encodages dans l'admin.

v1.0.7
------

Launch date: 2016/07/28

  * On passe les dates en français.
  * Meilleure mise en forme de l'offre choisie dans l'admin quand on change l'offre d'un utilisateur.
  * Lorsque l'on regénère une facture, ca met à jour les données manquante en base.
  * Mise en place d'une fonctionnalité de regénération de facture dans l'admin.
  * On donne la possibilité d'accéder à la fiche d'un utilisateur depuis la liste des factures.
  * On affiche le nombre d'utilisateur pour chaque offre dans l'admin
  * On améliore la gestion des coupons depuis la page profil : check sur le type d'offre.
  * Création de différents controllers pour la gestion de l'admin.
  * On masque le champ réductions dans la page profil pour les utilisateurs gratuits.
  * Mise en place de l'autoliquidation pour les clients de l'UE ayant renseigné leur numéro de TVA.

v1.0.6
------

Launch date: 2016/07/19

  * On checke en ajax la validité du coupon dès la création du compte.
  * On s'assure que quand l'utilisateur renseigne son numéro de TVA intra dans son profil, ca met bien à jour son offre dans Stripe.
  * Possibilité de supprimer des coupons.
  * Renommage de méthodes de la classe Payment.
  * On logue automatiquement le vidéaste s'il clique sur le lien contenu dans le mail l'alertant d'un commentaire.
  * Correction dans le nom d'un gabarit de mail.
  * Correction de bugs dans des variables dans le webhook de désabonnement.
  * On logue automatiquement le vidéaste lorsqu'il clique sur un lien dans une notification de commentaire.
  * On n'applique la TVA que si le client n'a pas saisi de numéro de TVA intra.
  * On remplace le numéro de téléphone par le numéro de TVA intra.
  * Mise en place de l'interface de création des coupons.
  * Possibilité de supprimer une offre depuis l'admin si elle n'a pas d'utilisateurs associés.
  * Possibilité de créer de nouvelles offres depuis l'admin.
  * Possibilité de changer l'offre d'un utilisateur depuis sa fiche dans l'admin.
  * Ajout d'une section dans l'admin avec la liste des offres.
  * Ajout d'un donut dans le dashboard avec la répartition des utilisateurs par offre.
  * Correction d'un bug dans l'affichage des factures pour gérer le cas de compte utilisateurs supprimés.
  * Correction d'un bug dans le calcul de l'espace de stockage dans l'admin dans le profil utilisateur.
  * Ajout de la storage bar dans le profil utilisateur.
  * Affichage dans l'admin des infos Stripe dans le profil utilisateur.
  * Factorisation du JS de gestion de la storage bar.
  * On ajoute dans l'admin, dans la fiche utilisateur, le nom de son offre.
  * Mise en place d'une page dans l'admin permettant de gérer les factures.
  * Ajout d'une condition de gestion d'erreur dans le webhook Stripe.
  * Mise en place de la page Trust Badge.
  * Correction du title de la page FAQ.

v1.0.5
------

Launch date: 2016/06/07

  * Correction du pseudo Twitter et du profil de Bobby.
  * Mise en ligne du dossier de presse et du kit allant avec.
  * Correction d'un bug lié à la clé utilisée pour Stripe dans le webhook.
  * Correction d'un bug dans l'enregistrement en base d'un upload.
  * On donne la possibilité de controler le lecteur vidéo avec la barre espace.
  * On fait en sorte que les marqueurs sur la timeline n'apparaissent que quand le player a fini de charger.
  * On modifie l'affichage des encodages en cours dans le dashboard.
  * On modifie l'email de confirmation envoyé au vidéaste pour inclure l'adresse mail des contacts ajoutés à l'upload.
  * On donne la possibilité de saisir ses adresses mail dès l'upload de la vidéo.
  * On crée un controller pour les requête Ajax du back.
  * Faire en sorte que les éléments du sous-menu soient cliquables partout.
  * On ajoute l'ID de l'utilisateur dans sa description Stripe.

v1.0.4
------

Launch date: 2016/05/31

  * Mise à jour du sitemap avec l'URL de la FAQ.
  * On change le fonctionnement des logs en prod.
  * On ajoute la possibilité de relancer un encodage depuis l'admin.
  * On sort les styles responsives dans des fichiers séparés.
  * On améliore le rendu mobile du front, du back et de l'admin.
  * Mise en place de la fonctionnalité de marquage d'un commentaire comme traité.
  * On ajoute un onglet encodages dans l'admin.
  * Mise en place de la nouvelle page d'accueil.
  * On remonte très légèrement la barre verte de progression dans le lecteur vidéo.
  * On corrige le vert sur le bouton dans l'email de partage d'une vidéo.
  * Correction sur le visuel des utilisateurs dans la liste des utilisateurs dans l'admin.

v1.0.3
------

Launch date: 2016/05/23

  * On modifie la mise en forme des boutons de la page tarifs.
  * Correction d'un bug dans le webhook Stripe.

v1.0.2
------

Launch date: 2016/05/20

  * On change l'effet de hover sur les boutons de changement d'offre.
  * On ajoute le prix de l'offre dans les données de session.
  * On change le nom de l'offre Starter en Pro.
  * On corrige la baseline.
  * On met en place le call-to-action d'upgrade.
  * On rajoute un lien vers les CGV dans le footer.
  * On rajoute l'onglet tarifs sur la home.
  * On corrige un pb d'affichage de la vidéo sur les écrans larges.

v1.0.1
------

Launch date: 2016/05/20

  * On sort l'offre spéciale invités de Franck de la liste des offres dans la page billing.

v1.0.0
------

Launch date: 2016/05/20

  * Full version of the app with Stripe integrated, real offers, encoding out of the API, ...