<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
        $form = $this->createForm(RegistrationType::class, $user);

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

    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request $request,
        EntityManagerInterface $em,
        UserService $userService,
        TransportInterface $transport,
    ): Response
    {
        /** @var User $user */
        $user = $request->getSession()->get('currentUser');

        if (!$user) {
            $this->addFlash('error', 'Vous devez vous inscrire pour accéder à cette page');
            return $this->redirectToRoute('app_register');
        }

        $oldPassword = $user->getPassword();

        $form = $this->createForm(ProfileType::class, $user);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPassword() !== null) {
                if (!$userService->isPasswordSecured($user->getPassword())) {
                    $this->addFlash('error', "Le mot de passe n'est pas sécurisé");
                    return $this->redirectToRoute('app_profile');
                }

                $user = $userService->hashPassword($user);
            } else {
                $user->setPassword($oldPassword);
            }

            try {
                $em->flush();

                $message = new Email();
                $message
                    ->to($user->getEmail())
                    ->from('noreply@ntcm.fr')
                    ->text('Votre profil a été mis à jour ! Pour voir vos nouvelles informations, vous pouvez visiter votre profil.');
                
                $transport->send($message);

                $this->addFlash('success', 'Votre profil a été mis à jour');
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('warning', 'Une erreur est survenue lors de l\'envoi de l\'email.');
            }

            return $this->redirectToRoute('app_profile');
        }
    
        return $this->render('user/profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }
    
    #[Route('/history', name: 'app_history', methods: ['GET', 'POST'])]
    public function history(
        Request $request,
        UserRepository $userRepository,
    ): Response{
        /** @var User $user */
        $user = $request->getSession()->get('currentUser');

        if (!$user) {
            $this->addFlash('error', 'Vous devez vous inscrire pour accéder à cette page');
            return $this->redirectToRoute('app_register');
        }

        return $this->render('user/history.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
}