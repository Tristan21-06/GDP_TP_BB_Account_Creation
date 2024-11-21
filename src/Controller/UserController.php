<?php
    namespace App\Controller;

    use App\Entity\User;
    use App\Form\UserType;
    use App\Service\UserService;
    use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;


    class UserController extends AbstractController
    {
        #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
        public function register(
            Request $request,
            EntityManagerInterface $em,
            UserService $userService
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

                $request->getSession()->set('currentUser', $user);

                try {
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success', 'Vous avez été enregistré');
                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('error', 'Cet email est déjà utilisé');
                }

                // TODO: redirect to app_profile
                return $this->redirectToRoute('app_register');
            }

            return $this->render('user/registration.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }
    }
?>