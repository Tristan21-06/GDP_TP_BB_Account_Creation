<?php
    namespace App\Controller;

    use App\Form\UserType;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;


    class UserController extends AbstractController
    {
        #[Route('/register', name: 'app_register')]
        public function register(): Response
        {
            $form = $this->createForm(UserType::class);

            return $this->render('user/registration.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }
    }
?>