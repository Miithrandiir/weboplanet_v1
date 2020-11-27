<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->isGranted(["IS_AUTHENTICATED_FULLY"])) {
            return $this->redirectToRoute('dashboard_account');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    public function logout()
    {

    }

    public function first(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('username', TextType::class, ['label' => 'Nom d\'utilisateur', 'required' => true, 'attr' => ['placeholder' => 'Nom d\'utilisateur']])
            ->add('email', EmailType::class, ['label' => 'Adresse E-Mail', 'required' => true, 'help' => 'Permet de reçevoir les alertes', 'attr' => ['placeholder' => 'Adresse E-Mail']])
            ->add('password', PasswordType::class, ['label' => 'Mot de passe','required' => true ,'attr' => ['placeholder' => 'Mot de passe']])
            ->add('passwordC', PasswordType::class, ['label' => 'Répétez le mot de passe','required' => true, 'attr' => ['placeholder' => 'Répétez le mot de passe']])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $username = $data['username'];
            $email = $data['email'];
            $password = $data['password'];
            $passwordC = $data['passwordC'];

            if($username == null || $email == null || $password == null || $passwordC == null) {
                $form->addError(new FormError('Tous les champs sont requierts !'));
            } else {
                //Check si l'utilisateur n'est pas déjà configuré !
                $em = $this->getDoctrine()->getManager();
                $user=$em->getRepository(Users::class)->findOneBy(['username' => $username]);
                if($user->getPassword() == null) {
                    if($password === $passwordC) {
                        $emailObj = $em->getRepository(Users::class)->findOneBy(['email' => $email]);
                        if($emailObj == null) {
                            $user->setPassword(password_hash($password, PASSWORD_ARGON2I));
                            $user->setEmail($email);

                            $em->flush();

                            return $this->redirectToRoute('login');
                        } else {
                            $form->addError(new FormError('L\'adresse E-Mail indiquée est déjà utilisée'));
                        }
                    } else {
                        $form->addError(new FormError('Les mots de passe ne correspondent pas !'));
                    }
                } else {
                    $form->addError(new FormError('Impossible de continuer, le profil est déjà configuré'));
                }
            }
        }

        return $this->render('security/firstConnection.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
