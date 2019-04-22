<?php

namespace App\Controller;

use App\Entity\Mail;
use App\Form\MailType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/mail", name="admin_mail")
     */
    public function mail(Request $request, ObjectManager $manager, \Swift_Mailer $mailer, UserRepository $userRepository){

        $mail = new Mail();

        $user = $this->getUser();

        $form = $this->createForm(MailType::class, $mail);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $mail->setEnvoyeLe(new \DateTime())
                ->setExpediteur($user);

            $manager->persist($mail);
            $manager->flush();

            $message = (new \Swift_Message('Contact personnalisé'))
                ->setFrom('battleofyggdrasil@gmail.com')
                ->setTo($mail->getDestinataire()->getEmail())
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'emails/contact_one.html.twig',
                        ['name' => $mail->getDestinataire()->getPrenom(),
                            'content' => $mail->getContenu()
                            ]
                    ),
                    'text/html'
                );

            $mailer->send($message);
        }

        return $this->render('admin/mail.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
