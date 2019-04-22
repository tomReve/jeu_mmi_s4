<?php

namespace App\Controller;

use App\Entity\ListeAmi;
use App\Entity\Mail;
use App\Form\ModifInfosType;
use App\Form\ModifMdpType;
use App\Repository\ListeAmiRepository;
use App\Repository\MailRepository;
use App\Repository\PartieRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index(UserRepository $userRepository, ListeAmiRepository $amiRepository, PartieRepository $partieRepository)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $amis =  $amiRepository->findBy(['user'=>$user]);

        $users = $userRepository->findAll();

        $cle="";

        foreach ($users as $key => $value){
            if($user->getPseudo() == $value->getPseudo()){
                $cle = $key;
            }
        }

        $amiTab = [];

        foreach ($amis as $ami){
            array_push($amiTab, $ami->getAmiUser());
        }

        unset($users[$cle], $users[0]);

        shuffle($users);

        $partiesJ1 = $user->getPartiesJ1();
        $partiesJ2 = $user->getPartiesJ2();

        $partieAttente = $partieRepository->findBy(["joueur2" => null]);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'users' => $users,
            'amisTab' => $amiTab,
            'amis' => $amis,
            'partiesJ1' => $partiesJ1,
            'partiesJ2' => $partiesJ2,
            'partieAttente' => $partieAttente,
        ]);
    }

    /**
     * @Route("/profile/update", name="profile_update")
     */

    public function updateInfos(Request $request, ObjectManager $manager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $form = $this->createForm(ModifInfosType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute("profile");
        }

        return $this->render('profile/modification.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/updateMdp", name="profile_mdp")
     */

    public function updateMdp(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $form = $this->createForm(ModifMdpType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute("profile");
        }

        return $this->render('profile/modificationmdp.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/notifications", name="profile_notification")
     */
    public function notifications(MailRepository $mailRepository){

        $user = $this->getUser();

        $mails = $mailRepository->findBy(['destinataire' => $user ]);

        return $this->render('profile/notifications.html.twig', [
            'mails' => $mails
        ]);
    }


    /**
     * @Route("/profile/amis/ajouter/{user}", name="profile_amis_ajouter")
     */

    public function addFriend(User $user, ObjectManager $manager, \Swift_Mailer $mailer){
        $receiver = $user;
        $sender = $this->getUser();

        $friend = new ListeAmi();

        if($receiver != $sender) {

            $friend->setUser($sender)
                ->setAmiUser($user);

            $manager->persist($friend);
            $manager->flush();

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('battleofyggdrasil@gmail.com')
                ->setTo($receiver->getEmail())
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'emails/demande_ami.html.twig',
                        [
                            'name' => $receiver->getPrenom(),
                            'pseudo' => $sender->getPseudo(),
                            'id' => $sender->getId()
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

        return $this->redirectToRoute("profile");
    }

    /**
     * @Route("/profile/amis/accepter/{user}", name="profile_amis_accepter")
     */

    public function acceptAmi(User $user, ObjectManager $manager){
        $receiver = $user;
        $sender = $this->getUser();

        if ($receiver != $sender) {
            $friend = new ListeAmi();

            $friend->setUser($sender)
                ->setAmiUser($receiver);

            $manager->persist($friend);
            $manager->flush();
        }
        return $this->redirectToRoute("profile");
    }

    /**
     * @Route("/profile/amis/delete/{id}", name="profile_ami_supprimer")
     */
    public function removeFriend(ListeAmi $listeAmi)
    {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($listeAmi);
            $entityManager->flush();

        return $this->redirectToRoute('profile');
    }
}
