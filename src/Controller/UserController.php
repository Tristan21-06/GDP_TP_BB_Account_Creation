<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\UserType;
use App\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class UserController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserService $userService,
        TransportInterface $transport
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            if(!$userService->isPasswordSecured($user->getPassword())) {
                $this->addFlash('error', "Le mot de passe n'est pas sécurisé");
                return $this->redirectToRoute('app_register');
            }

            $user = $userService->hashPassword($user);
            $user->setCreatedAt(new \DateTimeImmutable());

            try {
                $em->persist($user);
                $em->flush();
                
                $message = new Email();
                $message
                ->to($user->getEmail())
                ->from('noreply@ntcm.fr')
                ->text(
                    sprintf(
                        'Vous avez créé votre compte ! Pour accéder à votre profil, cliquez sur ce lien : %s',
                        $this->generateUrl('app_profile', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                        )
                    );
                    
                $transport->send($message);
                $request->getSession()->set('currentUser', $user);

                $this->addFlash('success', 'Vous avez été enregistré');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cet email est déjà utilisé');
                return $this->redirectToRoute('app_register');
            }

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/registration.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /*
    #[Route('/profile', name: 'app_profile')]
    public function profile(
        Request $request
    ): Response
    {
         
        $user = $request->getSession()->get('currentUser');

        if (!$user) {
            $this->addFlash('error', 'Vous devez vous inscrire pour accéder à cette page');
            return $this->redirectToRoute('app_register');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user, 
        ]);
    }
    */

    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request $request,
        EntityManagerInterface $em,
        UserService $userService,
        TransportInterface $transport,
    ): Response
    {
        $user = $request->getSession()->get('currentUser');

        if (!$user) {
            $this->addFlash('error', 'Vous devez vous inscrire pour accéder à cette page');
            return $this->redirectToRoute('app_register');
        }

        $form = $this->createForm(ProfileType::class, $user, [
            'allow_extra_fields' => true,  
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPassword() && !$userService->isPasswordSecured($user->getPassword())) {
                $this->addFlash('error', "Le mot de passe n'est pas sécurisé");
                return $this->redirectToRoute('app_profile');
            }
    
            if ($user->getPassword()) {
                $user = $userService->hashPassword($user);
            }
    
            $em->flush();

            try {
                $message = new Email();
                $message
                    ->to($user->getEmail())
                    ->from('noreply@ntcm.fr')
                    ->text(
                        sprintf(
                            'Votre profil a été mis à jour ! Pour voir vos nouvelles informations, vous pouvez visiter votre profil.',
                            $this->generateUrl('app_profile', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                        )
                    );
                
                $transport->send($message);
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Une erreur est survenue lors de l\'envoi de l\'email.');
            }
    
            $this->addFlash('success', 'Votre profil a été mis à jour');
            return $this->redirectToRoute('app_profile');
        }
    
        return $this->render('user/profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }
    

}