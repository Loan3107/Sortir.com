<?php

namespace App\Controller;

use App\Entity\Sortie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/sortie", name="sortie")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("/getList", name="_get_list")
     */
    public function getList()
    {
        return $this->render('sortie/getList.html.twig', [
            'title' => 'Liste des sorties',
        ]);
    }

    /**
     * @Route("/getListJson", name="_get_list_json")
     */
    public function getListJson()
    {
        $em = $this->getDoctrine()->getManager();
        $sortieRepository = $em->getRepository(Sortie::class);

        $array = [];

        $toSortie = $sortieRepository->findAll();
        foreach ($toSortie as $oSortie) {
            $t = array();
            $t['id'] = $oSortie->getId();
            $t['nom'] = $oSortie->getNom();
            $t['dateDebut'] = $oSortie->getDateDebut()->format('d/m/Y H:i');
            $t['dateCloture'] = $oSortie->getDateCloture()->format('d/m/Y H:i');
            $t['nbMaxInscriptions'] = '/' . $oSortie->getNbInscriptionsMax();
            $t['etat'] = $oSortie->getEtat()->getLibelle();
            $t['organisateur'] = $oSortie->getOrganisateur()->getPseudo();

            $array[] = $t;
        }

        return new JsonResponse($array);
    }
}
