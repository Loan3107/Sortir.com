<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sortie", name="sortie")
 * @IsGranted("ROLE_USER")
 */
class SortieController extends AbstractController
{
    /**
     * Affiche la page de la liste des sorties
     * @Route("/getList", name="_get_list")
     */
    public function getList()
    {
        return $this->render('sortie/getList.html.twig', [
            'title' => 'Liste des sorties',
        ]);
    }

    /**
     * Récupère la liste des sorties au format JSON
     * @Route("/getListJson", name="_get_list_json")
     */
    public function getListJson()
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();
        //Récupération du repository de l'entité Sortie
        $sortieRepository = $em->getRepository(Sortie::class);
        //Récupération du repository de l'entité Inscription
        $inscriptionRepository = $em->getRepository(Inscription::class);
        //Récupération du repository de l'entité Participant
        $participantRepository = $em->getRepository(Participant::class);

        //Récupération de l'utilisateur connecté
        $user = $participantRepository->findOneBy(['pseudo' => $this->getUser()->getUsername()]);

        //Définition du tableau final à retourner
        $array = [];

        //On récupère les sorties en base
        $toSortie = $sortieRepository->findAll();
        //Pour chaque sortie, on définit un tableau contenant les informations que l'on souhaite
        foreach ($toSortie as $oSortie) {
            $t = array();
            //On récupère l'identifiant de la sortie
            $t['id'] = $oSortie->getId();
            //On récupère le nom
            $t['nom'] = $oSortie->getNom();

            //On récupère la date de début de la sortie et on la transforme en chaîne de caractère
            $t['dateDebut'] = $oSortie->getDateDebut()->format('d/m/Y H:i');
            //On récupère la date de cloture des inscriptions et on la transforme en chaîne de caractère
            $t['dateCloture'] = $oSortie->getDateCloture()->format('d/m/Y H:i');

            //Récupération du nombre d'inscription sur le nombre de places maximum
            $t['nbMaxInscriptions'] =
                sizeof($oSortie->getInscriptions()->toArray())
                . '/'
                . $oSortie->getNbInscriptionsMax();

            //On vérifie si l'utilisateur est inscrit à la sortie
            $isInscrit = $inscriptionRepository->findBy(
                ['participant' => $user, 'sortie' => $oSortie]
            );
            //S'il est inscrit, on retourne une icône indiquant qu'il est inscrit
            if ($isInscrit) {
                $t['isInscrit'] = '<i class="fas fa-check"></i>';
            } else { //Sinon on laisse le champs vide
                $t['isInscrit'] = "";
            }

            //On récupère le libellé de l'état de la sortie
            $t['etat'] = $oSortie->getEtat()->getLibelle();
            //On récupère le pseudo de l'organisateur de la sortie
            $t['organisateur'] = $oSortie->getOrganisateur()->getPseudo();

            $t['actions'] = "";

            //Si l'utilisateur est organisateur de la sortie et que l'état de la sortie est à créé,
            //l'utilisateur peut toujours la modifier ou la supprimer
            if (
                ($user->getPseudo() == $oSortie->getOrganisateur()->getPseudo())
                &&
                ($oSortie->getEtat()->getLibelle() == "Créée")
            ) {
                $t['actions'] .=
                    '<button class="btn p-0" id="btn_sortie_edit" title="Modifier">'
                    .'<i class="fas fa-edit"></i>'
                    .'</button>'
                    .'<button class="btn p-0" id="btn_sortie_delete" title="Supprimer">'
                    .'<i class="fas fa-trash"></i>'
                    .'</button>';
            }
            //Si l'utilisateur est organisateur de la sortie et que l'état de la sortie est à ouvert,
            //l'utilisateur peut toujours la supprimer
            elseif (
                ($user->getPseudo() == $oSortie->getOrganisateur()->getPseudo())
                &&
                ($oSortie->getEtat()->getLibelle() == "Ouverte")
            ) {
                $t['actions'] .=
                    '<button class="btn p-0" id="btn_sortie_delete" title="Supprimer">'
                    .'<i class="fas fa-trash"></i>'
                    .'</button>';
            }
            //Si l'utilisateur est organisateur de la sortie et que l'état de la sortie est à clôturé,
            //l'utilisateur peut seulement supprimer la sortie
            elseif (
                ($user->getPseudo() == $oSortie->getOrganisateur()->getPseudo())
                &&
                ($oSortie->getEtat()->getLibelle() == "Cloturée")
            ) {
                $t['actions'] .=
                    '<button class="btn p-0" id="btn_sortie_delete" title="Supprimer">'
                    .'<i class="fas fa-trash"></i>'
                    .'</button>';
            }
            //Si l'utilisateur est inscrit à une sortie et que la sortie n'est pas annulée, passée ou en
            //cours d'activité, il peut se désister
            elseif (
                ($isInscrit && $oSortie->getEtat()->getLibelle() != "Annulée") ||
                ($isInscrit && $oSortie->getEtat()->getLibelle() != "Passée") ||
                ($isInscrit && $oSortie->getEtat()->getLibelle() != "En cours")
            ) {
                $t['actions'] .=
                    '<button class="btn p-0" id="btn_sortie_withdraw" title="Se désister">'
                    .'<i class="far fa-times-circle"></i>'.
                    '</button>';
            }
            //Si l'utilisateur n'est pas inscrit et que l'état de la sortie est ouvert, il peut s'inscrire
            elseif (!$isInscrit && $oSortie->getEtat()->getLibelle() == "Ouverte") {
                $t['actions'] .=
                    '<button class="btn p-0" id="btn_sortie_subscribe" title="S\'inscrire">'
                    .'<i class="far fa-check-square"></i>'
                    .'</button>';
            }

            //On stocke les informations dans le tableau final
            $array[] = $t;
        }

        //On retourne le tableau au format JSON
        return new JsonResponse($array);
    }
}
