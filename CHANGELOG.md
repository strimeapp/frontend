CHANGELOG
=========

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

[Unreleased]
------------

### Added

- FEATURE: Possibilité d'utiliser les touches J, K et L pour changer la vitesse de lecture d'un fichier audio.
- FEATURE: Possibilité d'utiliser les touches J, K et L pour changer la vitesse de lecture d'une vidéo.

### Changed

- IMPROVEMENT: On donne la priorité au fichier WebM sur le fichier MP4 (meilleure compatibilité) pour les vidéos.
- IMPROVEMENT: Passage à VideoJS v6.2.8 au lieu de v5.12.6.
- IMPROVEMENT: Amélioration du scroll vers les commentaires : on ne scrolle plus si on clique dans une zone de commentaire ou sur le bouton d'envoi.
- IMPROVEMENT: On vide le champ de réponse à un commentaire quand celle-ci a bien été envoyée.


[1.8.1] - 2017-10-11
--------------------

### Changed

- BUG: Correction d'un bug dans l'affichage des marqueurs sur les vidéos.


[1.8.0] - 2017-10-11
--------------------

### Added

- FEATURE: Mise en place de la page de validation des fichiers audio.
- FEATURE: Possibilité d'uploader des fichiers audio pour les utilisateurs autorisés.
- FEATURE: Intégration des stats sur les fichiers audio à l'admin.

### Changed

- IMPROVEMENT: Correction d'un petit bug sur le placement de la comment box sur la droite.
- IMPROVEMENT: Quand l'utilisateur supprime un contenu, on met automatiquement à jour son espace de stockage dans sa session.
- IMPROVEMENT: Mise en place de sous-menus dans l'admin pour soulager le menu principal.
- IMPROVEMENT: Factorisation des styles des sous-menus.
- IMPROVEMENT: On fait en sorte que le lecteur vidéo soit toujours visible dans son intégralité.
- IMPROVEMENT: Adaptation de l'email de partage aux fichiers audio.
- IMPROVEMENT: Adaptation du sujet du mail de fin d'encodage au type de fichier.
- IMPROVEMENT: Modification du endpoint ajax permettant d'ajouter un contact à un asset pour le rendre agnostic au type de fichier.
- IMPROVEMENT: Modification du contenu du mail de fin d'encodage pour l'adapter aux différents types de fichiers.
- IMPROVEMENT: Modification du contenu du mail d'encodage tué pour l'adapter aux différents types de fichiers.


[1.7.0] - 2017-08-10
--------------------

### Added

- FEATURE: Admin : Mise en place de stats sur les images.
- FEATURE: Mise en place d'un encart description sous la vidéo.
- FEATURE: Image : Ajout de la possibilité d'ajouter des images en fonction de ses droits.
- FEATURE: Image : Modification du texte de l'upload box en fonction des droits de l'utilisateur.
- FEATURE: Video : Ajout d'un bouton au player permettant de passer au commentaire précédent.
- FEATURE: Video : Ajout d'un bouton au player permettant de passer au commentaire suivant.
- FEATURE: Video : Ajout d'un bouton dans le player vidéo permettant de masquer les marqueurs sur la vidéo.
- FEATURE: Possibilité de révoquer les droits Youtube depuis la page intégration.
- FEATURE: Video : possibilité d'exporter une vidéo sur Youtube.
- FEATURE: Admin : mise en place d'un bouton permettant de supprimer une vidéo.

### Changed

- IMPROVEMENT: On fait en sorte de ne pas pouvoir déplacer un asset quand on en modifie le nom.
- IMPROVEMENT: On ajoute une marge à gauche des emojis dans les commentaires.
- IMPROVEMENT: On adapte le webhook d'envoi des commentaires en un seul mail au fait qu'il puisse y avoir plusieurs types de fichiers.
- IMPROVEMENT: Adaptation des notifications mail pour que ça marche quel que soit le type de fichier.
- IMPROVEMENT: Factorisation de styles communs aux pages image et vidéo.
- IMPROVEMENT: Modification de la manière dont on gère le scroll sur la page vidéo.
- IMPROVEMENT: On supprime les points dans les boutons de suppression de projet et de vidéo.
- IMPROVEMENT: Factorisation des modal box.
- IMPROVEMENT: Amélioration des titles des pages (en BO, on fait passer Strime à la fin et on retire le .io partout)
- IMPROVEMENT: Quand on change le nom d'une vidéo ou d'un projet, on change aussi le title de la page.
- BUG: Correction d'une incohérence dans le texte anglais de kill d'un encodage.
- BUG: Correction d'un bug de chaîne dans le mail de partage d'un projet.
- IMPROVEMENT: On réécrit tout le code de la partie dashboard & projet pour le rendre agnostic au type de fichier.
- IMPROVEMENT: Correction d'un bug dans des trads de la FAQ.
- IMPROVEMENT: Augmentation de la hauteur de la barre de lecture.
- IMPROVEMENT: Mise en place de la nouvelle version de la page vidéo.
- IMPROVEMENT: Mise en place d'une animation "3 petits points (ellipsis)" pour symboliser le fait que l'encodage soit en cours.
- IMPROVEMENT: Amélioration du process d'upload d'une vidéo pour ne pas avoir à attendre la fin de l'upload avant d'ajouter la vidéo, et possibilité d'ajouter plusieurs vidéos les unes à la suite des autres sans recharger la page.


[1.6.1] - 2017-06-22
--------------------

### Changed

- IMPROVEMENT: On modifie la structure HTML de la modal de modification d'un commentaire pour coller au plus près des maquettes de JP.
- IMPROVEMENT: On augmente la transparence de l'overlay du loader au déplacement des vidéos.


[1.6.0] - 2017-06-23
--------------------

### Added

- FEATURE: On ajoute le nom du serveur utilisé dans la liste des encodages en cours dans l'admin.
- FEATURE: Mise en place d'un graph dans l'admin pemettant de suivre la durée d'un encodage en fonction du poids et de la durée de la vidéo.
- FEATURE: Mise en place de stats sur l'intégration FB.
- FEATURE: Affichage de l'intégration FB dans le profil des utilisateurs dans l'admin.
- FEATURE: Intégration du bouton de connexion Facebook et de la gestion de cette intégration dans la partie profil.

### Changed

- IMPROVEMENT: Quand on clique sur un marqueur sur la vidéo, ça ne doit pas lancer celle-ci.
- IMPROVEMENT: Au déplacement d'une vidéo, on fait en sorte que le ghost qui suit la souris reprenne l'image de la vidéo avec une ombre.
- IMPROVEMENT: Au déplacement d'une vidéo, on utilise notre loader plutôt que l'animation.
- IMPROVEMENT: Quand on fait glisser un marqueur, on supprime les animations et on met en place un zoom à la place.
- IMPROVEMENT: On crée des fichiers de style indépendant par page.
- IMPROVEMENT: Quand on fait glisser un dossier sur un dossier, on n'active pas la zone.
- IMPROVEMENT: Amélioration du rendu responsive du dashboard.
- IMPROVEMENT: On retire la bordure grises des modal.
- IMPROVEMENT: On réduit le padding du titre et de la zone de contenu de la popup pour modifier ou supprimer un commentaire.
- IMPROVEMENT: Factorisation de la fonction de connexion avec les boutons sociaux (Google & Facebook).


[1.5.0] - 2017-06-12
--------------------

### Added

- FEATURE: Mise en place d'un Kit media EN dans les ressources.
- FEATURE: possibilité de réorganiser les vidéos en les faisant glisser dans les projets sur le dashboard.
- FEATURE: CRON job pour supprimer les token de réinitilisation des mots de passe ayant plus de 24h.
- FEATURE: On donne la possibilité de replacer un marqueur sur la vidéo.
- FEATURE: On branche le formulaire de contact sur l'API d'Hubspot pour créer automatiquement de nouveaux contacts.
- FEATURE: Intégration d'un lien boutique en footer

### Changed

- BUG: Correction d'un bug de mise en forme de la page projet quand on y accède comme client.
- IMPROVEMENT: Factorisation des constantes JS liées aux messages de signup et login.
- IMPROVEMENT: Amélioration du rendu des icônes sociales dans le menu fixe sur le front (elles étaient en blanc sur blanc au survol).
- IMPROVEMENT: On retarde un peu le chargement des marqueurs sur la timeline d'une vidéo pour éviter qu'ils ne se chargent mal.
- IMPROVEMENT: Légère modification du contenu sur la page d'accueil, on ajoute : "et de vos collègues".
- IMPROVEMENT: Le propriétaire d'une vidéo souhaitant recevoir des notifs mail à chaque commentaire n'est plus notifié de manière immédiate mais avec un décalage afin de laisser le temps à la miniature de se générer.
- IMPROVEMENT: On répartit le controller Ajax dans plusieurs controllers pour une meilleure maintenance du code.
- IMPROVEMENT: On transforme certaines variables JS en constantes pour plus de cohérence et de sécurité.
- IMPROVEMENT: Changement du processus de modification de mot de passe pour plus de sécurité.
- IMPROVEMENT: On augmente légèrement la marge du loader dans la boîte d'ajout d'un commentaire pour éviter que celui-ci ne soit tronqué en haut.
- IMPROVEMENT: On stocke dans Mailchimp la locale de l'utilisateur depuis le formulaire d'inscription du footer.
- BUG: Correction de bugs dans la structure HTML de la home.
- IMPROVEMENT: amélioration de l'affichage des boutons sociaux dans le header en front sur mobile


[1.4.3] - 2017-06-07
--------------------

### Changed

- BUG: Le Facebook Pixel devait contenir une vraie adresse mail. On a corrigé ce défaut.


[1.4.1] - 2017-04-28
--------------------

### Added

- FEATURE: Mise en place du code de tracking Hubspot.


[1.4.1] - 2017-04-28
--------------------

### Changed

- BUG: Correction d'un bug lié à la fonction setAvatar du helper Users.


[1.4.0] - 2017-04-27
--------------------

### Added

- FEATURE: Mise d'un place d'un outil permettant d'utiliser des emojis dans les commentaires.
- FEATURE: On conserve les éventuels paramètres GET suite aux redirections de langue /en, /es, /fr.
- FEATURE: Installation du Facebook Pixel pour pouvoir mettre en place les campagnes pub FB et leur suivi.
- FEATURE: Quand on clique sur le lien contenu dans un mail de notification de commentaire, on est directement emmené vers le commentaire en question.
- FEATURE: Intégration d'un dashboard dans l'admin avec des stats sur les intégrations.
- FEATURE: Lorsqu'une personne commente une vidéo, on sauvegarde ses informations dans la session pour qu'elle n'ait pas à s'identifier à chaque fois.
- FEATURE: Possibilité de modifier le nom des vidéos et des projets directement depuis le dashboard.

### Changed

- IMPROVEMENT: On enlève un "s" dans une trad anglaise.
- IMPROVEMENT: Changement de la mise en forme des liens dans la barre de langue.
- BUG: Correction du bug faisant que tous les commentaires se retrouvaient imbriqués les uns dans les autres.
- IMPROVEMENT: Changement de la couleur du pointeur et du petit curseur apparaissant ou bout de la barre verte, au survol,  sur la timeline de progression de la vidéo.
- BUG: Correction d'un bug dans la génération du lien dans le mail de commentaire. On ne doit pas avoir d'autologin si on n'est pas sûr d'envoyer le mail au propriétaire de la vidéo.
- IMPROVEMENT: On renomme le Controller Stats de l'admin en Dashboard pour plus de cohérence.
- IMPROVEMENT: Amélioration d'un test dans le endpoint Ajax de définition de l'Avatar.
- IMPROVEMENT: Correction de l'URL de partage dans la page vidéo qui doit reprendre le protocole HTTP.
- IMPROVEMENT: Admin - Ajout dans la liste des vidéos de la taille et de l'extension de la vidéo.
- IMPROVEMENT: Admin - Affichage des intégrations de chaque utilisateur dans sa fiche profil.
- IMPROVEMENT: Admin - Affichage des intégrations de chaque utilisateur dans la liste des utilisateurs.
- IMPROVEMENT: Admin - Amélioration de la présentation de l'info pour savoir si l'adresse mail a été confirmée.
- BUG: Correction d'un bug dans l'URL de suppression d'une vidéo lorsqu'elle est ajoutée au moment de l'encodage.
- IMPROVEMENT: On réorganise les fichiers JS dans le back.
- IMPROVEMENT: On retire la vieille version de VideoJS des assets.
- IMPROVEMENT: Quand on lance un nouvel encodage, s'il est rangé dans un dossier, on n'ajoute pas d'élément dans le dashboard, on met juste à jour le dossier.
- IMPROVEMENT: Quand on lance un nouvel encodage, il s'ajoute bien au début de la liste et non à la fin.
- IMPROVEMENT: On réécrit la structure HTML des éléments du tableau de bord pour plus de souplesse.


[1.3.1] - 2017-04-07
--------------------

### Changed

- BUG: On remet en place le fichier error_base qui n'aurait pas dû être supprimé.
- BUG: Variables were missing in the project page to deal with messages related to the upload


[1.3.0] - 2017-04-06
--------------------

### Added

- FEATURE: Mise à dispo de photos haute def pour nous 3.
- FEATURE: Création de 3 URLs /fr, /en et /es permettant de créer des liens directement vers la bonne langue.
- FEATURE: Mise en place de la version espagnole.
- FEATURE: Mise en place d'une section vidéo dans l'admin listant les vidéos uploadées (hors vidéos de test), pour pouvoir controler le contenu.
- FEATURE: Possilité d'être notifié sur Slack des commentaires de ses clients.
- FEATURE: Ajout dans l'admin d'un graphique avec la répartition du nombre d'utilisateur par langue.
- FEATURE: Création d'une page permettant de gérer les intégrations dans la section profil.
- FEATURE: Mise en place du Sign in Google

### Changed

- IMPROVEMENT: Amélioration de la manière dont on gère l'extension des fichiers vidéos à l'upload.
- IMPROVEMENT: Lorsque l'utilisateur se crée un compte via Google, on affiche un loader dans la popup de signup pour le faire patienter et l'empêcher de cliquer ailleurs.
- IMPROVEMENT: Intégration du screenshot du commentaire dans le mail quotidien de notification de commentaires.
- BUG: Correction d'un bug dans la sélection de la langue pour les mails envoyés en fin d'encodage.
- IMPROVEMENT: On retire de la page Users dans l'admin le bloc de recherche, devenu inutile suite à la mise en place de dataTables.
- IMPROVEMENT: On passe le endpoint de vérification de la validité de la session de l'utilisateur en ajax dans le controller AjaxUser.
- IMPROVEMENT: On passe le endpoint d'enregistrement de la lecture du bandeau de nouvelle fonctionnalité en ajax dans le controller AjaxUser.
- IMPROVEMENT: On passe le endpoint de renvoi du mail de confirmation en ajax dans le controller AjaxUser.
- IMPROVEMENT: On extrait les JS de génération de graphiques dans l'admin pour les gérer dans un fichier indépendant.
- IMPROVEMENT: Mise en place dans l'admin du graphique sur l'évolution du nombre de contacts, qui n'apparaissait pas.
- IMPROVEMENT: Si un utilisateur ayant déjà un compte mais n'ayant pas confirmé son adresse mail ou ayant le statut désactivé se connecte grâce à Google, çà valide automatiquement son adresse mail ou le réactive.
- IMPROVEMENT: Ajout de quelques captures d'écran dans la FAQ.
- IMPROVEMENT: Léger enrichissement de la section Comptes Utilisateurs de la FAQ.


[1.2.4] - 2017-03-27
--------------------

### Changed

- BUG: Correction d'un bug dans la traduction d'une chaine de caractères dans l'email de confirmation de fin d'encodage.


[1.2.3] - 2017-03-23
--------------------

### Changed

- BUG: Correction d'un bug quand un utilisateur tente d'accéder à un projet qui n'existe pas.


[1.2.2] - 2017-03-23
--------------------

### Changed

- BUG: Je réintègre le fichier JS pour afficher les marqueurs que j'ai oublié de réinclure.


[1.2.1] - 2017-03-23
--------------------

### Changed

- BUG: Correction d'un bug dans le changement d'offre d'un utilisateur dans l'admin.
- BUG: Fixed a bug in the path to an entity.


[1.2.0] - 2017-03-22
--------------------

### Added

- FEATURE: Possibilité de modifier le nom d'un projet en cliquant dessus depuis la page projet.
- FEATURE: Nouveaux graphiques sur le dashboard admin : évolution du nombre de projets, de vidéos, de commentaires et de contacts.
- FEATURE: Intégration de stats sur les projets, vidéos, commentaires et contacts dans l'admin.
- FEATURE: Mise en place des boutons pour trier les commentaires.
- FEATURE: Création d'un webhook pour envoyer un mail contenant une liste de commentaires.
- FEATURE: Possibilité d'éditer ses préférences de notifications pour les commentaires.
- FEATURE: Possibilité de modifier ses paramètres d'inscription à la newsletter.

### Changed

- IMPROVEMENT: On ajoute la locale aux mails de feedback pour pouvoir debugger plus facilement.
- IMPROVEMENT: Quand on passe la souris au-dessus du nom d'une vidéo, si le marqueur de succès de modification est affiché, on n'affiche pas le crayon.
- IMPROVEMENT: Patchage de failles de sécurité dans l'admin.
- IMPROVEMENT: On retire des variables inutiles, et on utilise app.session dans Twig.
- BUG: Correction d'un bug dans le webhook de renvoi du mail de confirmation.
- BUG: Correction d'un bug générant une faille de sécurité dans l'autologin de l'utilisateur au moment de l'envoi d'une notification de commentaire.
- BUG: Correction d'un bug dans l'inscription par le formulaire pied de page && dans l'objet de l'email envoyé suite à ce formulaire.
- IMPROVEMENT: On range toutes les entités dans le bundle Global.
- IMPROVEMENT: La case résolu ne doit apparaître que pour le propriétaire, pas pour les utilisateurs logués.
- IMPROVEMENT: Ajout d'informations sur la fiche d'un utilisateur dans l'admin : confirmation de l'email, langue
- IMPROVEMENT: On s'assure que les boutons de partage et pour marquer un commentaire comme résolu n'apparaissent que si c'est le propriétaire qui visite la page, et pas un client connecté à un vrai compte Strime.


[1.1.0] - 2017-03-07
--------------------

### Added

- FEATURE: Envoie d'une alerte Slack quand un utilisateur supprime son compte
- FEATURE: Mise en place du multilingue
- FEATURE: On adapte la numérotation de l'app au Semantic Version (http://semver.org/)
- FEATURE: Création d'un CHANGELOG-1.0.md pour archivage

### Changed

- BUG: Correction dans le changement d'avatar qui n'était pas bien pris en compte.
- IMPROVEMENT: Factorisation des styles du switch et des boutons radio.
- IMPROVEMENT: Possibilité de se connecter directement depuis les pages projet et vidéo quand on est invité, sans repasser par la home.
- IMPROVEMENT: Création d'un controller contenant uniquement les fonctions endpoint ajax du Front
- IMPROVEMENT: Changement du menu dans le header dans les pages internes du Front
- IMPROVEMENT: On vide le message de la feedback box quand on l'ouvre pour éviter qu'un vieux message ne traine.
- BUG: Correction d'une coquille dans les TOS
