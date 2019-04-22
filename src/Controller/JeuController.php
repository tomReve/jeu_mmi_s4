<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Mail;
use App\Entity\Partie;
use App\Repository\CarteRepository;
use App\Repository\ChatRepository;
use App\Repository\ListeAmiRepository;
use App\Repository\PartieRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("partie")
 */

class JeuController extends AbstractController
{
    /**
     * @Route("/abandon_partie/{partie}", name="jeu_abandone_partie")
     */
    public function abandonPartie(Partie $partie, ObjectManager $manager){
        $user = $this->getUser();

        if ($user == $partie->getJoueur1() || $user == $partie->getJoueur2()){
             $partie->setFinieLe(new \DateTime())
                 ->setTypeVictoire('abandon');

             if($user == $partie->getJoueur1()){
                 $partie->setGagnant($partie->getJoueur2())
                     ->setPerdant($partie->getJoueur1());
             } else {
                 $partie->setGagnant($partie->getJoueur1())
                     ->setPerdant($partie->getJoueur2());
             }

             $manager->persist($partie);
             $manager->flush();
        }

        return $this->redirectToRoute("profile");

    }


    /**
     * @Route("/rejoindre/{partie}", name="jeu_rejoindre_partie")
     */
    public function rejoindrePartie(Partie $partie, ObjectManager $manager){

        if($partie->getJoueur2() == null){
            $partie->setJoueur2($this->getUser());

            $manager->persist($partie);
            $manager->flush();
        }

        return $this->redirectToRoute('profile');

    }

    /**
     * @Route("/relancer_joueur/{partie}", name="jeu_relancer_joueur_partie")
     */
    public function relancerJoueur(Partie $partie, \Swift_Mailer $mailer, ObjectManager $manager){
        $sender = $this->getUser();

        if($sender == $partie->getJoueur1() || $sender == $partie->getJoueur2()){
            if ($sender == $partie->getJoueur1()){
                $receiver = $partie->getJoueur2();
            } else {
                $receiver = $partie->getJoueur1();
            }

            $url = $this->generateUrl('jeu_relancer_joueur_partie', array('partie' => $partie->getId()), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('battleofyggdrasil@gmail.com')
                ->setTo($receiver->getEmail())
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'emails/relancer_partie.html.twig',
                        [
                            'name' => $receiver->getPrenom(),
                            'pseudo' => $sender->getPseudo(),
                            'url' => $url
                        ]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            $mail = new Mail();

            $mail->setExpediteur($sender)
                ->setDestinataire($receiver)
                ->setContenu($message->getBody())
                ->setEnvoyeLe(new \DateTime());

            $manager->persist($mail);
            $manager->flush();

        }

        return $this->redirectToRoute('profile');

    }


    /**
     * @Route("/distribue_partie", name="jeu_distribue_partie")
     */
    public function distribueCartes(Request $request, CarteRepository $carteRepository, UserRepository $userRepository, ObjectManager $manager, \Swift_Mailer $mailer){
        $joueur1 = $this->getUser();
        if($request->request->get('adversaire') != null){
            $joueur2 = $userRepository->find($request->request->get('adversaire'));
        } elseif ($request->request->get('ami') != null) {
            $joueur2 = $userRepository->find($request->request->get('ami'));

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('battleofyggdrasil@gmail.com')
                ->setTo($joueur2->getEmail())
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'emails/invitation_partie.html.twig',
                        [
                            'name' => $joueur2->getPrenom(),
                            'pseudo' => $joueur1->getPseudo()
                        ]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            $mail = new Mail();

            $mail->setExpediteur($joueur1)
                ->setDestinataire($joueur2)
                ->setContenu($message->getBody())
                ->setEnvoyeLe(new \DateTime());

            $manager->persist($mail);
            $manager->flush();
        } else {
            $joueur2 = null;
        }


        if($joueur1 !== null){
            $partie = new Partie();
            if($joueur2 !== null){
                $partie->setJoueur1($joueur1)
                    ->setJoueur2($joueur2)
                    ->setCreationLe(new \DateTime())
                    ->setTour($joueur1->getId())
                    ->setDes(null);
            } else {
                $partie->setJoueur1($joueur1)
                    ->setJoueur2(null)
                    ->setCreationLe(new \DateTime())
                    ->setTour($joueur1->getId())
                    ->setDes(null);
            }

            $cartes = $carteRepository->findAll();

            $cartesJ1 = [];
            $cartesJ2 = [];
            $shogunJ1 = null;
            $shogunJ2 = null;

            foreach ($cartes as $carte){
                if($carte->getEquipe() === 'J1'){
                    if($carte->isShogun()){
                        $shogunJ1 = $carte->getId();
                    }else{
                        $cartesJ1[]= $carte->getId();
                    }
                }

                if($carte->getEquipe() === 'J2'){
                    if($carte->isShogun()){
                        $shogunJ2 = $carte->getId();
                    }else{
                        $cartesJ2[]= $carte->getId();
                    }
                }
            }

            shuffle($cartesJ1);
            shuffle($cartesJ2);

            $terrainJ1 = [
              1 => [1 => $shogunJ1, 2 => $cartesJ1[0], 3 => $cartesJ1[1], 4 => $cartesJ1[2]],
              2 => [1 => $cartesJ1[3], 2 => $cartesJ1[4], 3 => $cartesJ1[5]],
              3 => [1 => $cartesJ1[6], 2 => $cartesJ1[7]],
              4 => [1 => $cartesJ1[8]],
              5 => [],
              6 => [],
              7 => [],
              8 => [],
              9 => [],
              10 => [],
              11 => []
            ];

            $terrainJ2 = [
                1 => [1 => $shogunJ2, 2 => $cartesJ2[0], 3 => $cartesJ2[1], 4 => $cartesJ2[2]],
                2 => [1 => $cartesJ2[3], 2 => $cartesJ2[4], 3 => $cartesJ2[5]],
                3 => [1 => $cartesJ2[6], 2 => $cartesJ2[7]],
                4 => [1 => $cartesJ2[8]],
                5 => [],
                6 => [],
                7 => [],
                8 => [],
                9 => [],
                10 => [],
                11 => []
            ];

            $partie->setPlateauJ1($terrainJ1);
            $partie->setPlateauJ2($terrainJ2);

            $manager->persist($partie);
            $manager->flush();

            if($partie->getJoueur2() != null){
                return $this->redirectToRoute('jeu_affiche_partie', ['partie' => $partie->getId()]);
            } else {
                return $this->redirectToRoute('profile');
            }
        }

    }

    /**
     * @Route("/affiche_partie/{partie}", name="jeu_affiche_partie")
     */
    public function affichePartie(Partie $partie, ChatRepository $chatRepository){
        $user = $this->getUser();

        $chats = $chatRepository->findBy(['partie' => $partie]);

        if ($user == $partie->getJoueur1() || $user == $partie->getJoueur2()){
            return $this->render('jeu/partie.html.twig',[
                'partie'=>$partie,
                'chats' => $chats
            ]);
        } else {
            return $this->redirectToRoute("profile");
        }

    }

    /**
     * @Route("/chat_partie/{partie}", name="jeu_chat_partie")
     */
    public function chatPartie(Partie $partie, ChatRepository $chatRepository){
        $chats = $chatRepository->findBy(['partie' => $partie]);

        $chat = [];
        foreach ($chats as $one){
            foreach ($one as $key => $value){
               $chat[$key] = $value;
            }
        }

        return $this->json($chat);
    }

    /**
     * @Route("/envoyer_chat_partie/{partie}", name="jeu__envoyer_chat_partie")
     */
    public function envoieChat(Partie $partie, ChatRepository $chatRepository, ObjectManager $manager, Request $request){
        $user = $this->getUser();

        if ($user == $partie->getJoueur1() || $user == $partie->getJoueur2()){
            if($request->request->get('message') != null) {
                $text = $request->request->get('message');

                $tchat = new Chat();
                $tchat->setExpediteur($user)
                    ->setEnvoyeLe(new \DateTime())
                    ->setMessage($text)
                    ->setPartie($partie);

                $manager->persist($tchat);
                $manager->flush();

                return $this->json('OK', Response::HTTP_OK);
            }
        }

        return $this->redirectToRoute('main_accueil');
    }

    /**
     * @Route("/plateau/{partie}", name="jeu_plateau_partie")
     */
    public function plateau(CarteRepository $carteRepository, Partie $partie){
        $cartes = $carteRepository->findAll();
        $tCartes = [];
        foreach ($cartes as $carte){
            $tCartes[$carte->getId()] = $carte;
        }

        if ($partie->getJoueur1()->getId() === $this->getUser()->getId()){
            $terrainJoueur = $partie->getPlateauJ1();
            $terrainAdversaire = $partie->getPlateauJ2();
        } else {
            $terrainJoueur = $partie->getPlateauJ2();
            $terrainAdversaire = $partie->getPlateauJ1();
        }

        return $this->render('plateau/plateau2.html.twig', [
           'tCartes' => $tCartes,
           'partie' => $partie,
            'terrainJoueur' => $terrainJoueur,
            'terrainAdversaire' => $terrainAdversaire
        ]);
    }

    /**
     * @Route("/ajax/sauvegarder/lance/des/{partie}", methods={"POST"}, name="sauvegarder_lance_des")
     */
    public function sauvegarderLanceDes(Request $request, Partie $partie) {
        $de1 = $request->request->get('de1');
        $de2 = $request->request->get('de2');
        $de3 = $request->request->get('de3');
        $statut1 = $request->request->get('statut1');
        $statut2 = $request->request->get('statut2');
        $statut3 = $request->request->get('statut3');
        $des = [
            "de1" => ["valeur" => $de1, "statut" => $statut1],
            "de2" => ["valeur" => $de2, "statut" => $statut2],
            "de3" => ["valeur" => $de3, "statut" => $statut3]
        ];
        $partie->setDes($des);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return $this->json('OK', Response::HTTP_OK);
    }


    /**
     * @Route("/ajax/changement_tour/{partie}", methods={"POST"}, name="change_tour_game")
     */
    public function changementTour(Request $request,Partie $partie)
    {
        $adversaire = $request->request->get('adversaire');
        $partie->setTour($adversaire)
            ->setDes(null);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return $this->json('OK', Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @Route("/deplacement-partie/{partie}", name="deplacement_game", methods={"POST"})
     */
    public function move(
        EntityManagerInterface $entityManager,
        CarteRepository $carteRepository,
        Request $request,
        Partie $partie
    )
    {
        $user = $this->getUser();

        $carte = $carteRepository->find($request->request->get('id'));
        if ($carte !== null) {
            $position = $request->request->get('position');
            $deplacement = $request->request->get('valeur');
            $pile = $request->request->get('pile');
            $t = $partie->getJoueur1()->getId() === $this->getUser()->getId() ? $partie->getPlateauJ1() : $partie->getPlateauJ2();
            $tAdv = $partie->getJoueur1()->getId() === $this->getUser()->getId() ? $partie->getPlateauJ2() : $partie->getPlateauJ1();
            $p = $t[$pile];
            $t[$pile] = [];
            $pileDest = $pile + $deplacement;
            $pileDestAdv = 11 - $pile - $deplacement + 1;
            $nbcartes = count($t[$pileDest]);
            $lastIndex = [];

            foreach ($t[$pileDest] as $key => $value) {
                $lastIndex[] = $key;
            }
            if (sizeof($lastIndex) > 0){
                $lastIndexPileDest = $lastIndex[sizeof($lastIndex) - 1];
            } else {
                $lastIndexPileDest = 0;
            }


            if ($nbcartes === 0) {
                $t[$pileDest] = [];
            }
            $pos = 1;
            foreach ($p as $k => $v) {
                if ($pos >= $position) {
                    $t[$pileDest][$k + $lastIndexPileDest] = $v;
                } else {
                    $t[$pile][$k] = $v;
                }
                $pos++;
            }
            if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                $partie->setPlateauJ1($t);
            } else {
                $partie->setPlateauJ2($t);
            }

            $entityManager->flush();
            if (count($tAdv[$pileDestAdv]) > 0) {

                $idDerniereCarte = $t[$pileDest];
                $arrayCartes = [];
                foreach ($idDerniereCarte as $key => $value){
                    $arrayCartes[]=$value;
                }
                $idDerniereCarte = $arrayCartes[sizeof($arrayCartes)-1];

                $derniereCarte = $carteRepository->findOneBy(['id' => $idDerniereCarte]);

                $idDerniereCarteAdversaire = $tAdv[$pileDestAdv];
                $arrayCartesAdv = [];
                foreach ($idDerniereCarteAdversaire as $key => $value){
                    $arrayCartesAdv[] = $value;
                }
                $idDerniereCarteAdversaire = $arrayCartesAdv[sizeof($arrayCartesAdv)-1];

                $derniereCarteAdversaire = $carteRepository->findOneBy(['id' => $idDerniereCarteAdversaire]);

                $nbCartesPileAdv = sizeof($tAdv[$pileDestAdv]);
                $nbcartesPile = sizeof($t[$pileDest]);

                return $this->json([
                        'etat' => 'conflit',
                    'idDerniereCarte' => $idDerniereCarte,
                    'idDerniereCarteAdv' => $idDerniereCarteAdversaire,
                    'carteJ2type' => $derniereCarteAdversaire->getType(),
                    'carteJ1type' => $derniereCarte->getType(),
                    'pileDest' => $pileDest,
                    'pileDestAdv' => $pileDestAdv,
                    'nbCartesPileAdv' => $nbCartesPileAdv,
                    'nbCartesPile' => $nbcartesPile
                    ]);
            } else {
                if($pileDest == 11) {
                    $partie->setGagnant($user)
                        ->setTypeVictoire('Camp détruit')
                        ->setFinieLe(new \DateTime());
                    if($user == $partie->getJoueur1()){
                        $partie->setPerdant($partie->getJoueur2());
                    } else {
                        $partie->setPerdant($partie->getJoueur1());
                    }

                    $entityManager->flush();

                    return $this->json(['etat' => 'win']);
                } else {
                    return $this->json(['etat' => 'ok']);
                }

            }
        }
    }

    /**
     * @param Request $request
     * @Route("/resolve-conflict/{partie}", name="resolve_conflict_game", methods={"POST"})
     */
    public function resolveConflict(CarteRepository $carteRepository, Request $request, Partie $partie, ObjectManager $manager)
    {
        $carteJ1 = $carteRepository->findOneBy(['id' => $request->request->get('carte')]);
        $carteJ2 = $carteRepository->findOneBy(['id' => $request->request->get('carteAdv')]);
        $pile = $request->request->get('pile');
        $pileAdv = $request->request->get('pileAdv');
        if ($carteJ1 && $carteJ2) {
            if ($carteJ1->getType() === $carteJ2->getType()) {
                //identique, donc on regarde le poid
                if ($carteJ1->getValeur() > $carteJ2->getValeur()) {
                    //c'est J1 qui gagne, on supprime la carte de J2

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $plateauJ2 = $partie->getPlateauJ2();
                    } else {
                        $plateauJ2 = $partie->getPlateauJ1();
                    }

                    $array = [];
                    foreach ($plateauJ2[$pileAdv] as $key)
                    {
                        $array[] = $key;
                    }
                    unset($array[sizeof($array) - 1]);

                    $plateauJ2[$pileAdv] = [];
                    $plateauJ2[$pileAdv] = $array;

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $partie->setPlateauJ2($plateauJ2);
                    } else {
                        $partie->setPlateauJ1($plateauJ2);
                    }

                    $manager->flush();

                    $carteRestantes = sizeof($plateauJ2[$pileAdv]);

                    $idDerniereCarteAdversaire = $plateauJ2[$pileAdv];
                    $arrayCartesAdv = [];
                    foreach ($idDerniereCarteAdversaire as $key => $value){
                        $arrayCartesAdv[] = $value;
                    }
                    if(sizeof($arrayCartesAdv)-1 >= 0){
                        $idDerniereCarteAdversaire = $arrayCartesAdv[sizeof($arrayCartesAdv)-1];
                    } else {
                        $idDerniereCarteAdversaire = 'plus de carte';
                    }

                    //cette action termine la partie ?
                    //type victoire 1
                    if($carteRestantes == 0 && $pileAdv == 1){
                        return $this->json(['resultat' => 'win']);
                    }
                    //type victoire 2
                    $isEmpty = 0;
                    foreach ($plateauJ2 as $pile){
                        $isEmpty += sizeof($pile);
                    }

                    if($isEmpty == 0){
                        return $this->json(['resultat' => 'win']);
                    }

                    return $this->json(
                        [
                            'resultat' => 'J1',
                            'nb' => $carteRestantes,
                            'lastcard' => $idDerniereCarteAdversaire,
                            'carteJ1type' => $carteJ1->getType(),
                            'carteJ2type' => $carteJ2->getType()
                        ]
                    );

                } elseif ($carteJ1->getValeur() < $carteJ2->getValeur()) {
                    //c'est J2 qui gagne, on supprime la carte de J1

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $plateauJ1 = $partie->getPlateauJ1();
                    } else {
                        $plateauJ1 = $partie->getPlateauJ2();
                    }

                    $array = [];
                    foreach ($plateauJ1[$pile] as $key)
                    {
                        $array[] = $key;
                    }
                    unset($array[sizeof($array) - 1]);

                    $plateauJ1[$pile] = [];
                    $plateauJ1[$pile] = $array;

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $partie->setPlateauJ1($plateauJ1);
                    } else {
                        $partie->setPlateauJ2($plateauJ1);
                    }

                    $manager->flush();

                    $carteRestantes = sizeof($plateauJ1[$pile]);

                    $idDerniereCarte = $plateauJ1[$pile];
                    $arrayCartes = [];
                    foreach ($idDerniereCarte as $key => $value){
                        $arrayCartes[]=$value;
                    }

                    if(sizeof($arrayCartes)-1 >= 0){
                        $idDerniereCarte = $arrayCartes[sizeof($arrayCartes)-1];
                    } else {
                        $idDerniereCarte = 'plus de carte';
                    }

                    // cela mene-t-il a une fin de partie ?
                    $isEmpty = 0;

                    foreach ($plateauJ1 as $pile){
                        $isEmpty += sizeof($pile);
                    }
                    if($isEmpty == 0){
                        return $this->json(['resultat' => 'loose']);
                    }

                    return $this->json(
                        [
                            'resultat' => 'J2',
                            'nb' => $carteRestantes,
                            'lastcard' => $idDerniereCarte,
                            'carteJ1type' => $carteJ1->getType(),
                            'carteJ2type' => $carteJ2->getType()
                        ]
                    );

                } else {

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $plateauJ1 = $partie->getPlateauJ1();
                        $plateauJ2 = $partie->getPlateauJ2();
                    } else {
                        $plateauJ1 = $partie->getPlateauJ2();
                        $plateauJ2 = $partie->getPlateauJ1();
                    }

                    // reculer la carte de J1
                    $lastIndexMe = [];

                    foreach ($plateauJ1[$pile-1] as $key => $value) {
                        $lastIndexMe[] = $key;
                    }
                    if (sizeof($lastIndexMe) > 0){
                        $lastIndexMe = $lastIndexMe[sizeof($lastIndexMe) - 1];
                    } else {
                        $lastIndexMe = 0;
                    }

                    if($pile != 1) {
                        foreach ($plateauJ1[$pile] as $key => $value)
                        {
                            if($value == $carteJ1->getId()){
                                $plateauJ1[$pile - 1][$key + $lastIndexMe] = $value;
                                unset($plateauJ1[$pile][$key]);
                            }
                        }
                    }
                    // reculer la carte de J2
                    $lastIndexAdv = [];

                    foreach ($plateauJ2[$pileAdv-1] as $key => $value) {
                        $lastIndexAdv[] = $key;
                    }
                    if (sizeof($lastIndexAdv) > 0){
                        $lastIndexAdv = $lastIndexAdv[sizeof($lastIndexAdv) - 1];
                    } else {
                        $lastIndexAdv = 0;
                    }

                    if($pileAdv != 1){
                        foreach ($plateauJ2[$pileAdv] as $key => $value)
                        {
                            if($value == $carteJ2->getId()){
                                $plateauJ2[$pileAdv - 1][$key+$lastIndexAdv] = $value;
                                unset($plateauJ2[$pileAdv][$key]);
                            }

                        }
                    }

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $partie->setPlateauJ1($plateauJ1);
                        $partie->setPlateauJ2($plateauJ2);
                    } else {
                        $partie->setPlateauJ1($plateauJ2);
                        $partie->setPlateauJ2($plateauJ1);
                    }

                    $manager->flush();
                    //données a renvoyer pour le coté bas
                    $carteRestantesJ1 = sizeof($plateauJ1[$pile]);

                    $idDerniereCarteJ1 = $plateauJ1[$pile];
                    $arrayCartesJ1 = [];
                    foreach ($idDerniereCarteJ1 as $key => $value){
                        $arrayCartesJ1[]=$value;
                    }

                    if(sizeof($arrayCartesJ1)-1 >= 0){
                        $idDerniereCarteJ1 = $arrayCartesJ1[sizeof($arrayCartesJ1)-1];
                    } else {
                        $idDerniereCarteJ1 = 'plus de carte';
                    }

                    //données a renvoyer pour le plateau haut
                    $carteRestantesJ2 = sizeof($plateauJ2[$pileAdv]);

                    $idDerniereCarteJ2 = $plateauJ2[$pileAdv];
                    $arrayCartesAdvJ2 = [];
                    foreach ($idDerniereCarteJ2 as $key => $value){
                        $arrayCartesAdvJ2[] = $value;
                    }
                    if(sizeof($arrayCartesAdvJ2)-1 >= 0){
                        $idDerniereCarteJ2 = $arrayCartesAdvJ2[sizeof($arrayCartesAdvJ2)-1];
                    } else {
                        $idDerniereCarteJ2 = 'plus de carte';
                    }

                    return $this->json(['resultat' => 'Egalite les deux joueurs reculent', 'nb' => $carteRestantesJ1, 'lastcard' => $idDerniereCarteJ1, 'nb2' => $carteRestantesJ2, 'lastcard2' => $idDerniereCarteJ2, 'carteJ1type' => $carteJ1->getType(),
                        'carteJ2type' => $carteJ2->getType() ]);
                }
            } else {
                //cartes différentes on fait le pierre-feuille-ciseau
                //la pierre bat les ciseaux (en les émoussant),
                // les ciseaux battent la feuille (en la coupant),
                // la feuille bat la pierre (en l'enveloppant).

                if($carteJ1->isShogun()){
                    //get hp left shogun
                    $hpLeftShogun = $request->request->get('hpLeftShogun1');

                    //recup plateau users
                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $plateauJ2 = $partie->getPlateauJ2();
                        $plateauJ1 = $partie->getPlateauJ1();
                    } else {
                        $plateauJ2 = $partie->getPlateauJ1();
                        $plateauJ1 = $partie->getPlateauJ2();
                    }

                    //si shogun plus fort que carte delete carte
                    if ($hpLeftShogun > $carteJ2->getValeur()){
                        $array = [];
                        foreach ($plateauJ2[$pileAdv] as $key)
                        {
                            $array[] = $key;
                        }
                        unset($array[sizeof($array) - 1]);

                        $plateauJ2[$pileAdv] = [];
                        $plateauJ2[$pileAdv] = $array;

                        if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                            $partie->setPlateauJ2($plateauJ2);
                        } else {
                            $partie->setPlateauJ1($plateauJ2);
                        }

                        $hpLeftShogun = $hpLeftShogun - $carteJ2->getValeur();
                    //si shogun plus assez fort delete shogun
                    } else {
                        $array = [];

                        foreach ($plateauJ1[$pile] as $key)
                        {
                            $array[] = $key;
                        }
                        unset($array[sizeof($array) - 1]);

                        $plateauJ1[$pile] = [];
                        $plateauJ1[$pile] = $array;

                        if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                            $partie->setPlateauJ1($plateauJ1);
                        } else {
                            $partie->setPlateauJ2($plateauJ1);
                        }

                        $hpLeftShogun = 0;
                    }

                    $manager->flush();

                    //recup nb carte et last carte adv
                    $carteRestantesAdv = sizeof($plateauJ2[$pileAdv]);

                    //recup dernière carte adv
                    $idDerniereCarteAdversaire = $plateauJ2[$pileAdv];
                    $arrayCartesAdv = [];
                    foreach ($idDerniereCarteAdversaire as $key => $value){
                        $arrayCartesAdv[] = $value;
                    }

                    if(sizeof($arrayCartesAdv)-1 >= 0){
                        $idDerniereCarteAdversaire = $arrayCartesAdv[sizeof($arrayCartesAdv)-1];
                    } else {
                        $idDerniereCarteAdversaire = 'plus de carte';
                    }

                    //recup last carte et nb carte moi
                    $carteRestantes = sizeof($plateauJ1[$pile]);

                    //recup dernière carte moi
                    $idDerniereCarte = $plateauJ1[$pile];
                    $arrayCartes = [];
                    foreach ($idDerniereCarte as $key => $value){
                        $arrayCartes[]=$value;
                    }
                    if(sizeof($arrayCartes)-1 >= 0){
                        $idDerniereCarte = $arrayCartes[sizeof($arrayCartes)-1];
                    } else {
                        $idDerniereCarte = 'plus de carte';
                    }

                    //cette action termine la partie ?
                    if($carteRestantesAdv == 0 && $pileAdv == 1){
                        return $this->json(['resultat' => 'win']);
                    }
                    //deuxieme possiblité
                    $isEmptyAdv = 0;
                    foreach ($plateauJ2 as $pile){
                        $isEmptyAdv += sizeof($pile);
                    }

                    if($isEmptyAdv == 0){
                        return $this->json(['resultat' => 'win']);
                    }

                    $isEmpty = 0;
                    foreach ($plateauJ1 as $pile){
                        $isEmpty += sizeof($pile);
                    }

                    if($isEmpty == 0){
                        return $this->json(['resultat' => 'loose']);
                    }

                    return $this->json(
                        [
                            'resultat' => 'SHOGUNJ1',
                            'nb' => $carteRestantes,
                            'lastcard' => $idDerniereCarte,
                            'nb2' => $carteRestantesAdv,
                            'lastcard2' => $idDerniereCarteAdversaire,
                            'carteJ1type' => $carteJ1->getType(),
                            'carteJ2type' => $carteJ2->getType(),
                            'hpLeftShogun' => $hpLeftShogun
                        ]
                    );
                }

                if($carteJ2->isShogun()){
                    //get hp shogun ADV
                    $hpLeftShogunAdv = $request->request->get('hpLeftShogunAdv2');

                    //get les plateaux
                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $plateauJ1 = $partie->getPlateauJ1();
                        $plateauJ2 = $partie->getPlateauJ2();
                    } else {
                        $plateauJ1 = $partie->getPlateauJ2();
                        $plateauJ2 = $partie->getPlateauJ1();
                    }

                    //si hp suffisant supprime carte
                    if($hpLeftShogunAdv > $carteJ1->getValeur()){
                        $array = [];
                        foreach ($plateauJ1[$pile] as $key)
                        {
                            $array[] = $key;
                        }
                        unset($array[sizeof($array) - 1]);

                        $plateauJ1[$pile] = [];
                        $plateauJ1[$pile] = $array;

                        if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                            $partie->setPlateauJ1($plateauJ1);
                        } else {
                            $partie->setPlateauJ2($plateauJ1);
                        }

                        $hpLeftShogunAdv = $hpLeftShogunAdv-$carteJ1->getValeur();

                    //si hp pas suffisant supprime shogun
                    } else {

                        $array = [];
                        foreach ($plateauJ2[$pileAdv] as $key)
                        {
                            $array[] = $key;
                        }
                        unset($array[sizeof($array) - 1]);

                        $plateauJ2[$pileAdv] = [];
                        $plateauJ2[$pileAdv] = $array;

                        if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                            $partie->setPlateauJ2($plateauJ2);
                        } else {
                            $partie->setPlateauJ1($plateauJ2);
                        }

                        $hpLeftShogunAdv = 0;
                    }

                    $manager->flush();

                    //recup nb carte eet last carte moi
                    $carteRestantes = sizeof($plateauJ1[$pile]);

                    $idDerniereCarte = $plateauJ1[$pile];
                    $arrayCartes = [];
                    foreach ($idDerniereCarte as $key => $value){
                        $arrayCartes[]=$value;
                    }
                    if(sizeof($arrayCartes)-1 >= 0){
                        $idDerniereCarte = $arrayCartes[sizeof($arrayCartes)-1];
                    } else {
                        $idDerniereCarte = 'plus de carte';
                    }

                    //recup nb carte et last carte adv
                    $carteRestantesAdv = sizeof($plateauJ2[$pileAdv]);

                    //recup dernière carte adv
                    $idDerniereCarteAdversaire = $plateauJ2[$pileAdv];
                    $arrayCartesAdv = [];
                    foreach ($idDerniereCarteAdversaire as $key => $value){
                        $arrayCartesAdv[] = $value;
                    }

                    if(sizeof($arrayCartesAdv)-1 >= 0){
                        $idDerniereCarteAdversaire = $arrayCartesAdv[sizeof($arrayCartesAdv)-1];
                    } else {
                        $idDerniereCarteAdversaire = 'plus de carte';
                    }

                    //cette action termine la partie ?
                    if($carteRestantesAdv == 0 && $pileAdv == 1){
                        return $this->json(['resultat' => 'win']);
                    }
                    //deuxieme possibilité
                    $isEmptyAdv = 0;
                    foreach ($plateauJ2 as $pile){
                        $isEmptyAdv += sizeof($pile);
                    }

                    if($isEmptyAdv == 0){
                        return $this->json(['resultat' => 'win']);
                    }

                    $isEmpty = 0;
                    foreach ($plateauJ1 as $pile){
                        $isEmpty += sizeof($pile);
                    }

                    if($isEmpty == 0){
                        return $this->json(['resultat' => 'loose']);
                    }

                    return $this->json(
                        [
                            'resultat' => 'SHOGUN2',
                            'nb' => $carteRestantes,
                            'lastcard' => $idDerniereCarte,
                            'nb2' => $carteRestantesAdv,
                            'lastcard2' => $idDerniereCarteAdversaire,
                            'carteJ1type' => $carteJ1->getType(),
                            'carteJ2type' => $carteJ2->getType(),
                            'hpLeftShogunAdv' => $hpLeftShogunAdv
                        ]
                    );
                }

                if (($carteJ1->isHache() && $carteJ2->isBouclier()) || ($carteJ1->isBouclier() && $carteJ2->isArc()) ||
                    ($carteJ1->isArc() && $carteJ2->isHache())) {

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $plateauJ2 = $partie->getPlateauJ2();
                    } else {
                        $plateauJ2 = $partie->getPlateauJ1();
                    }

                    $array = [];
                    foreach ($plateauJ2[$pileAdv] as $key)
                    {
                        $array[] = $key;
                    }
                    unset($array[sizeof($array) - 1]);

                    $plateauJ2[$pileAdv] = [];
                    $plateauJ2[$pileAdv] = $array;

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $partie->setPlateauJ2($plateauJ2);
                    } else {
                        $partie->setPlateauJ1($plateauJ2);
                    }

                    $manager->flush();

                    $carteRestantes = sizeof($plateauJ2[$pileAdv]);

                    $idDerniereCarteAdversaire = $plateauJ2[$pileAdv];
                    $arrayCartesAdv = [];
                    foreach ($idDerniereCarteAdversaire as $key => $value){
                        $arrayCartesAdv[] = $value;
                    }

                    if(sizeof($arrayCartesAdv)-1 >= 0){
                        $idDerniereCarteAdversaire = $arrayCartesAdv[sizeof($arrayCartesAdv)-1];
                    } else {
                        $idDerniereCarteAdversaire = 'plus de carte';
                    }

                    //cette action termine la partie ?
                    if($carteRestantes == 0 && $pileAdv == 1){
                        return $this->json(['resultat' => 'win']);
                    }
                    //type victoire 2
                    $isEmpty = 0;
                    foreach ($plateauJ2 as $pile){
                        $isEmpty += sizeof($pile);
                    }

                    if($isEmpty == 0){
                        return $this->json(['resultat' => 'win']);
                    }


                    return $this->json(
                        [
                            'resultat' => 'J1',
                            'nb' => $carteRestantes,
                            'lastcard' => $idDerniereCarteAdversaire,
                            'carteJ1type' => $carteJ1->getType(),
                            'carteJ2type' => $carteJ2->getType()
                        ]
                    );

                } else {

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $plateauJ1 = $partie->getPlateauJ1();
                    } else {
                        $plateauJ1 = $partie->getPlateauJ2();
                    }

                    $array = [];
                    foreach ($plateauJ1[$pile] as $key)
                    {
                        $array[] = $key;
                    }
                    unset($array[sizeof($array) - 1]);

                    $plateauJ1[$pile] = [];
                    $plateauJ1[$pile] = $array;

                    if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                        $partie->setPlateauJ1($plateauJ1);
                    } else {
                        $partie->setPlateauJ2($plateauJ1);
                    }

                    $manager->flush();

                    $carteRestantes = sizeof($plateauJ1[$pile]);

                    $idDerniereCarte = $plateauJ1[$pile];
                    $arrayCartes = [];
                    foreach ($idDerniereCarte as $key => $value){
                        $arrayCartes[]=$value;
                    }
                    if(sizeof($arrayCartes)-1 >= 0){
                        $idDerniereCarte = $arrayCartes[sizeof($arrayCartes)-1];
                    } else {
                        $idDerniereCarte = 'plus de carte';
                    }
                    // cela mene-t-il a une fin de partie ?
                    $isEmpty = 0;

                    foreach ($plateauJ1 as $pile){
                        $isEmpty += sizeof($pile);
                    }
                    if($isEmpty == 0){
                        return $this->json(['resultat' => 'loose']);
                    }

                    return $this->json(
                        [
                            'resultat' => 'J2',
                            'nb' => $carteRestantes,
                            'lastcard' => $idDerniereCarte,
                            'carteJ1type' => $carteJ1->getType(),
                            'carteJ2type' => $carteJ2->getType()
                        ]
                    );

                }
            }
        }
    }

        /**
         * @param Request $request
         * @Route("/refresh-terrain/{partie}", name="refresh_game")
         */
        public function refreshTerrain(CarteRepository $carteRepository, Partie $partie)
        {
            if ($partie->getJoueur1()->getId() === $this->getUser()->getId()) {
                //en base c'est J1, adversaire = J2;
                $terrainJoueur = $partie->getPlateauJ1();
                $terrainAdversaire = $partie->getPlateauJ2();
            } else {
                $terrainAdversaire = $partie->getPlateauJ1();
                $terrainJoueur = $partie->getPlateauJ2();
            }
            return $this->render('plateau/plateau2.html.twig', [
                'partie'            => $partie,
                'terrainAdversaire' => $terrainAdversaire,
                'terrainJoueur'     => $terrainJoueur,
                'tCartes'           => $carteRepository->findByArrayId()
            ]);
        }
}
