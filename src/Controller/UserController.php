<?php

namespace App\Controller;

use App\Entity\Mail;
use App\Entity\User;
use App\Form\EditUserType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("admin/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'nb' => count($userRepository->findAll()),
            'nbonline' => count($userRepository->findBy(['online' => '1']))
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash)
                ->setCreationLe(new \DateTime())
                ->setConnexionLe(new \DateTime())
                ->setRoles(['ROLE_USER'])
                ->setBlocage(0)
                ->setAvertissement(0)
                ->setAvatar('default_avatar.png')
                ->setOnline(0)
                ->setToken(bin2hex(random_bytes(32)));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }


    /**
     * @Route("/ban/{id}", name="user_ban")
     */
    public function ban(User $user, \Swift_Mailer $mailer, UserRepository $userRepository, ObjectManager $manager){
        $user->setBlocage(1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $message = (new \Swift_Message('Bannissement'))
            ->setFrom('battleofyggdrasil@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'emails/ban.html.twig',
                    ['name' => $user->getPrenom()]
                ),
                'text/html'
            );

        $mailer->send($message);

        $mail = new Mail();

        $mail->setExpediteur($userRepository->findOneBy(['id' => '1']))
            ->setDestinataire($user)
            ->setContenu($message->getBody())
            ->setEnvoyeLe(new \DateTime());

        $manager->persist($mail);
        $manager->flush();


        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/unban/{id}", name="user_unban")
     */
    public function unban(User $user, \Swift_Mailer $mailer, UserRepository $userRepository, ObjectManager $manager){
        $user->setBlocage(0);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $message = (new \Swift_Message('Debannissement'))
            ->setFrom('battleofyggdrasil@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'emails/unban.html.twig',
                    ['name' => $user->getPrenom()]
                ),
                'text/html'
            );

        $mailer->send($message);

        $mail = new Mail();

        $mail->setExpediteur($userRepository->findOneBy(['id' => '1']))
            ->setDestinataire($user)
            ->setContenu($message->getBody())
            ->setEnvoyeLe(new \DateTime());

        $manager->persist($mail);
        $manager->flush();

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/avert/{id}", name="user_avert")
     */
    public function avert(User $user, \Swift_Mailer $mailer, UserRepository $userRepository, ObjectManager $manager){
        $avert = $user->getAvertissement();
        $avert = $avert+1;
        $user->setAvertissement($avert);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $message = (new \Swift_Message('Avertissement'))
            ->setFrom('battleofyggdrasil@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'emails/avertissement.html.twig',
                    ['name' => $user->getPrenom()]
                ),
                'text/html'
            );

        $mailer->send($message);

        $mail = new Mail();

        $mail->setExpediteur($userRepository->findOneBy(['id' => '1']))
            ->setDestinataire($user)
            ->setContenu($message->getBody())
            ->setEnvoyeLe(new \DateTime());

        $manager->persist($mail);
        $manager->flush();

        return $this->redirectToRoute('user_index');
    }
}
