<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ParticipantType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/participant", name="participant")
 * @IsGranted("ROLE_USER")
 * Class ParticipantController
 * @package App\Controller
 */
class ParticipantController extends AbstractController
{
    /**
     * @Route("/showProfile/{pseudoParticipant}", name="_show_profile")
     * Affiche le détail d'un profil
     * @param $pseudoParticipant
     * @return Response
     */   
    public function showProfile(Request $request, UserPasswordEncoderInterface $encoder, $pseudoParticipant = null)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();
        //Récupération du repository de l'entité Participant
        $participantRepository = $em->getRepository(Participant::class);

        //Si aucun pseudo n'est renseigné, on récupère le profil de l'utilisateur connecté
        if(!$pseudoParticipant) {
            $title = "Mon profil";
            $oParticipant = $participantRepository->findOneBy(
                ['pseudo' => $this->getUser()->getPseudo()]
            );
            $isAuthorizedToModify = true;

            //Création du formulaire de modification
            $form = $this->createForm(ParticipantType::class, $oParticipant);
            $form->handleRequest($request);

            //Si le formulaire est soumit et est valide
            if($form->isSubmitted() && $form->isValid()) {                
                $photo = $form->get('photo')->getData();
                $oldMotDePasse = $form->get('motDePasse')->getData();
                $newMotDePasse = $form->get('newMotDePasse')->getData();

                //Si le mot de passe entré est valide, on peut effectuer les modifications
                if($encoder->isPasswordValid($oParticipant, $oldMotDePasse)) {   
                    //On récupère les données
                    $oParticipant = $form->getData();
                    //Si une photo a été transmise
                    if($photo) {
                        $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$photo->guessExtension();

                        //On essaye de placer l'image dans le répertoire d'images
                        try {
                            $photo->move(
                                $this->getParameter('images_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            $this->addFlash('danger', $e->getMessage());
                            return $this->redirectToRoute('participant_show_profile');
                        }

                        //On enregistre le nom du fichier
                        $oParticipant->setNomFichierPhoto($newFilename);
                    }

                    //Si l'utilisateur souhaite changer son mot de passe, on le change
                    if($newMotDePasse) {
                        $newEncodedPassword = $encoder->encodePassword($oParticipant, $newMotDePasse);
                        $oParticipant->setPassword($newEncodedPassword);
                    }

                    //On sauvegarde
                    $em->persist($oParticipant);
                    $em->flush();

                    //On affiche un message de succès et on redirige vers cette même page
                    $this->addFlash('success', 'Les modifications ont bien été enregistrées');
                    return $this->redirectToRoute('participant_show_profile');
                } else {//Si le mot de passe n'est pas le bon
                    $this->addFlash('danger', 'Mot de passe incorrect !');
                    return $this->redirectToRoute('participant_show_profile');
                }
            }

            $formView = $form->createView();
        } else { //Sinon on récupère le participant en fonction du pseudo fournit
            $title = $pseudoParticipant;
            $oParticipant = $participantRepository->findOneBy(['pseudo' => $pseudoParticipant]);
            //Si on ne trouve pas de participant
            if(!$oParticipant) {
                //Affichage d'un message d'erreur et redirection vers la liste des sorties
                $this->addFlash('danger', 'L\'utilisateur recherché n\'existe pas');
                return $this->redirectToRoute('sortie_get_list');
            }
            $isAuthorizedToModify = false;
            $formView = null;
        }

        return $this->render('participant/showProfile.html.twig', [
            'title' => $title,
            'oParticipant' => $oParticipant,
            'isAuthorizedToModify' => $isAuthorizedToModify,
            'form' => $formView
        ]);
    }

    /**
     * @Route("/getListJson", name="_get_list_json")
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function getListJson(Request $request)
    {
        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();
        //Récupération du repository de l'entité Sortie
        $sortieRepository = $em->getRepository(Sortie::class);

        //Récupération de l'identifiant de la sortie
        $idSortie = $request->get("idSortie");

        //Récupération de la sortie
        $oSortie = $sortieRepository->findOneBy(['id' => $idSortie]);
        //Si on ne trouve pas la sortie
        if (!$oSortie) {
            //On affiche un message d'erreur et on redirige vers la liste des sorties
            $this->addFlash('danger', "La sortie n'existe pas");
            return $this->redirectToRoute('sortie_get_list');
        }

        //Récupération des inscriptions à la sortie
        $toInscription = $oSortie->getInscriptions();
        $array= [];

        //Pour chaque inscription, on récupère les informations du participant
        foreach ($toInscription as $oInscription) {
            $oParticipant = $oInscription->getParticipant();

            $t = array();
            $t['pseudo'] =
                '<a type="button" href="'. $this->generateUrl('participant_show_profile', ['pseudoParticipant' => $oParticipant->getPseudo()]).'" class="btn p-0" title="Voir le profil">'
                . $oParticipant->getPseudo()
                .'</a>';
            $t['nom'] = $oParticipant->getNom() . " " . $oParticipant->getNom();

            $array[] = $t;
        }

        return new JsonResponse($array);
    }
}
