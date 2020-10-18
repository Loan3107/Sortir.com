<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $campusRepository;
    private $etatRepository;
    private $lieuRepository;

    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->campusRepository = $em->getRepository(Campus::class);
        $this->etatRepository = $em->getRepository(Etat::class);
        $this->lieuRepository = $em->getRepository(Lieu::class);

        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        //Instanciation du faker
        $faker = Factory::create();

        //Différents états possibles pour une sortie
        $etats = [
            "Créée",
            "Ouverte",
            "Cloturée",
            "En cours",
            "Passée",
            "Annulée"
        ];

        //Différent lieux pour une sortie
        $typesLieux = [
            "Piscine",
            "Parc",
            "Centre aéré",
            "Circuit",
            "Stade"
        ];

        //Noms des différents campus
        $nomsCampus = [
            "ENI Chartres-de-Bretagne",
            "ENI Saint-Herblain",
            "ENI Angers"
        ];

        //Génération des états
        for ($i = 0; $i < count($etats); $i++) {
            $oEtat = new Etat();
            $oEtat->setLibelle($etats[$i]);
            $manager->persist($oEtat);
        }

        $manager->flush();

        //Génération des campus
        for ($i = 0; $i < count($nomsCampus); $i++) {
            $oCampus = new Campus();
            $oCampus->setNom($nomsCampus[$i]);
            $manager->persist($oCampus);
        }

        $manager->flush();

        //Génération des Lieux et génération des Villes auxquelles les lieux sont rattachés
        for ($i = 0; $i < 20; $i++) {
            $oVille = new Ville();
            $oVille->setNom($faker->city());
            $oVille->setCodePostal($faker->postcode());

            for ($j = 0; $j < 2; $j++) {
                $oLieu = new Lieu();
                $rue = $faker->streetName();

                try {
                    $oLieu->setNom($typesLieux[random_int(0, count($typesLieux) - 1)] . " " . $rue);
                } catch (Exception $exception) {
                    print_r($exception);
                }

                $oLieu->setRue($rue);
                $oLieu->setLatitude($faker->latitude($min = -90, $max = 90));
                $oLieu->setLongitude($faker->latitude($min = -180, $max = 180));

                $oLieu->setVille($oVille);

                $manager->persist($oLieu);
            }

            $manager->persist($oVille);
        }

        $manager->flush();

        $toCampus = $this->campusRepository->findAll();
        $toEtat = $this->etatRepository->findAll();
        $toLieu = $this->lieuRepository->findAll();

        //Pour chaque campus
        foreach ($toCampus as $key => $oCampus) {
            //Génération d'un organisateur
            $organisateur = new Participant();
            $organisateur->setPrenom($faker->firstName());
            $organisateur->setNom($faker->name());
            $organisateur->setTelephone("0102030405");
            $organisateur->setMail($faker->email());
            $organisateur->setMotDePasse($this->passwordEncoder->encodePassword($organisateur, "password"));
            $organisateur->setPseudo("Organisateur_" . $key);
            $organisateur->setIsActif($faker->boolean());
            $organisateur->setIsAdministrateur($faker->boolean());
            $organisateur->setCampus($oCampus);

            $manager->persist($organisateur);

            //Génération d'une sortie
            $oSortie = new Sortie();
            $timestamp = $faker->unixTime($max = 'now');

            $oSortie->setNom("Sortie_" . $organisateur->getPseudo());
            $oSortie->setDateDebut(new DateTime());
            $oSortie->setDateCloture(new DateTime(date("d-m-Y", $timestamp + random_int(0, 10))));
            $oSortie->setEtat($toEtat[random_int(0, count($toEtat) - 1)]);
            $oSortie->setLieu($toLieu[random_int(0, count($toLieu) - 1)]);
            $oSortie->setOrganisateur($organisateur);
            $oSortie->setNbInscriptionsMax(random_int(2, 20));

            $manager->persist($oSortie);

            //Génération de 20 participants "classique"
            for ($i = 0; $i < 20; $i++) {
                $oParticipant = new Participant();
                $oParticipant->setPrenom($faker->firstName());
                $oParticipant->setNom($faker->name());
                $oParticipant->setTelephone("0102030405");
                $oParticipant->setMail($faker->email());
                $oParticipant->setMotDePasse($this->passwordEncoder->encodePassword($oParticipant, "password"));
                $oParticipant->setPseudo("Participant_" . $i . "_Campus_" . $oCampus->getId());
                $oParticipant->setIsActif($faker->boolean());
                $oParticipant->setIsAdministrateur($faker->boolean());
                $oParticipant->setCampus($oCampus);

                $manager->persist($oParticipant);

                //Génération d'une inscription à une sortie
                $oInscription = new Inscription();
                $oInscription->setDateInscription(
                    new DateTime($faker->date($format = 'd-m-Y', $max = 'now'))
                );
                $oInscription->setParticipant($oParticipant);
                $oInscription->setSortie($oSortie);

                $manager->persist($oInscription);
            }

            $manager->persist($oCampus);
        }

        $manager->flush();
    }
}
