<?php

namespace App\Controller;

use App\Entity\Mail;
use App\Entity\User;
use App\Form\InscriptionType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_inscription")
     */

    public function inscription(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, UserRepository $userRepository){
        $user = new User();

        $form = $this->createForm(InscriptionType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
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
            $manager->persist($user);
            $manager->flush();

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('battleofyggdrasil@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'emails/registration.html.twig',
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

            return $this->redirectToRoute("app_login");
        }

        return $this->render('security/inscription.html.twig', [
           'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/deconnexion", name="security_logout")
     */

    public function logout(){}

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/onlineMode", name="security_online_mode")
     */
    public function onlineMode(ObjectManager $manager){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $user->setConnexionLe(new \DateTime())
            ->setOnline(1);

        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute("profile");
    }

    /**
     * @Route("/offlineMode", name="security_offline_mode")
     */
    public function offlineMode(ObjectManager $manager){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $user->setOnline(0);

        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute("security_logout");
    }

    /**
     * @Route("/forgot_password", name="security_forgot_password")
     */

    public function forgotPassword(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer, UserRepository $userRepository, ObjectManager $manager){

            if ($request->isMethod('POST')) {

                $email = $request->request->get('email');

                $entityManager = $this->getDoctrine()->getManager();
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                /* @var $user User */

                if ($user === null) {
                    $this->addFlash('danger', 'Email Inconnu');
                    return $this->redirectToRoute('home');
                }
                $token = $user->getToken();

                $url = $this->generateUrl('security_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                $message = (new \Swift_Message('Mot de passe oublié - Battle of Yggdrasil'))
                    ->setFrom('battleofyggdrasil@gmail.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        "blablabla voici le token pour reseter votre mot de passe : " . $url,
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

                $this->addFlash('notice', 'Mail envoyé');

                return $this->redirectToRoute('app_login');
            }
        return $this->render('security/forgot_password.html.twig');
    }

    /**
     * @Route("/reset_password/{token}", name="security_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {

        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();

            $user = $entityManager->getRepository(User::class)->findOneBy(['token' => $token]);
            /* @var $user User */

            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('home');
            }

            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();

            $this->addFlash('notice', 'Mot de passe mis à jour');

            return $this->redirectToRoute('app_login');
        }else {

            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }

    }
}
