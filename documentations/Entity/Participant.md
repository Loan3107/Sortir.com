# Participant

Utilisateur du site Sortir.com, il peut se connecter
sur le site.

----------------------------------------------------

Attributs :

- pseudo : Le pseudo est unique et permettra au
participant de se connecter
- nom : Nom de famille du participant
- prenom : Prénom du participant
- telephone : Numéro de téléphone du participant
(celui-ci n'est pas obligatoire)
- mail : Adresse email du participant
- mot_de_passe : Le mot de passe est crypter en 
base de données.
- is_administrateur : Les participants peuvent être
soit Administrateurs, soit Organisateurs, soit 
des Participants classiques. Ce champs permet de
faire la différence entre un Administrateur et
les deux autres rôles. Un organisateur est 
simplement un participant ayant créé une sortie
- is_actif : Si le participant est désactivé par 
l'administrateur, celui-ci ne pourra pas se 
connecter sur le site
- nomFichierPhoto : Nom de la photo de profil 
du participant
- campus : Campus auquel le participant est 
rattaché

----------------------------------------------------

Fonctionnalités disponibles pour un participant :
- Celui-ci pourra se connecter si son compte est
actif
- Il pourra créer une sortie et deviendra
Organisateur
- Il pourra supprimer les sorties qu'il a 
organisé
- Il pourra voir les sorties disponibles
- Il pourra lire le détail d'une sortie
- Il pourra s'inscrire aux sorties ouvertes
- Il pourra se désister des sorties auxquelles
il est inscrit
- Il pourra modifier son profil
- Il pourra lire le détail d'un profil

Fonctionnalités disponibles pour un Administrateur :
- Il pourra effectuer les mêmes tâches qu'un 
participant classique
- Il pourra créer une ville
- Il pourra modifier une ville
- Il pourra créer un campus
- Il pourra modifier un campus
- Il pourra créer un participant
- Il pourra importer un fichier json pour créer
un participant
- Il pourra modifier un participant
- Il pourra supprimer un participant
