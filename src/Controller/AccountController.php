<?php

namespace App\Controller;

use App\Entity\EvaluationsNotes;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    public function index()
    {

        return $this->render('dashboard/account.html.twig', [
            'moyenne' => $this->getUser()->getAverage(),
            'missedTests' => sizeof($this->getUser()->getMissedTests()),
            'testsDone' => sizeof($this->getUser()->getTestsDone()),
        ]);
    }

    public function changeEmail(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('emailAct', EmailType::class,['label'=>'Adresse E-Mail actuelle', 'attr'=>['placeholder'=>'Adresse E-Mail actuelle']])
            ->add('newEmail', EmailType::class,['label'=>'Nouvelle adresse E-Mail', 'attr'=>['placeholder'=>'Nouvelle adresse Email']])
            ->add('repeatEmail', EmailType::class, ['label'=>'Répétez l\'adresse Email', 'attr'=>['placeholder'=>'Répétez l\'adresse Email']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getdata();
            $user = $this->getUser();

            if($data['newEmail'] == $data['repeatEmail']) {
                $em = $this->getDoctrine()->getManager();
                $user->setEmail($data['newEmail']);

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('dashboard_account');
            } else {
                $form->addError( new FormError("Les adresses E-Mail ne correspondent pas !"));
            }

            return $this->redirectToRoute('dashboard_account');
        }
        return $this->render('dashboard/account.change.email.html.twig', [
            'controller_name' => 'AccountController',
            'form' => $form->createView()
        ]);
    }

    public function changePassword(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('passwordAct', PasswordType::class,['empty_data'=>'Répétez le nouveaux mot de passe','label'=>'Mot de passe actuel', 'attr'=>['placeholder'=>'Mot de passe actuel']])
            ->add('newPassword', PasswordType::class,['empty_data'=>'Répétez le nouveaux mot de passe','label'=>'Nouveau mot de passe', 'attr'=>['placeholder'=>'Nouveau mot de passe']])
            ->add('repeatPassword', PasswordType::class, ['empty_data'=>'Répétez le nouveaux mot de passe','label'=>'Répétez le mot de passe', 'attr'=>['placeholder'=>'Répétez le mot de passe']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $passwordAct = $data['passwordAct'];
            $user = $this->getUser();

            if(password_verify($passwordAct, $user->getPassword())) {
                //Le mot de passe est bon !
                if($data['newPassword'] == $data['repeatPassword']) {
                    //Modification du mot de passe !
                    $em = $this->getDoctrine()->getManager();
                    $user->setPassword(password_hash($data['newPassword'],PASSWORD_DEFAULT));

                    $em->persist($user);
                    $em->flush();

                    return $this->redirectToRoute('logout');
                } else {
                    $form->addError(new FormError("Les mots de passe ne correspondent pas !"));
                }
            } else {
                $form->addError(new FormError("Le mot de passe ne correspond pas au mot de passe actuel."));
            }

            //return $this->redirectToRoute('dashboard_account');
        }
        return $this->render('dashboard/account.change.password.html.twig', [
            'controller_name' => 'AccountController',
            'form' => $form->createView()
        ]);
    }

    public function anonChangeEmail(string $token, Request $request)
    {
        //@TODO
        return $this->render('email/newPassword.html.twig', ['token'=>'test']);
        //return $this->json($token);
    }

    public function viewNotes()
    {
        $notes = $this->getDoctrine()->getRepository(EvaluationsNotes::class)->findBy(['user' => $this->getUser()]);

        $final = [];

        foreach ($notes as $note) {
            foreach($note->getEvaluation()->getEvaluationsGroups() as $evaluationsGroup) {
                foreach($evaluationsGroup->getGroupID() as $group) {


                    $finalNote=[];
                    $moyenne=0;
                    foreach($group->getUsersGroups() as $usersGroup) {
                        foreach ($usersGroup->getUserID() as $user) {
                            if($user->getId() == $note->getUser()->getId()) {
                                //On remet tout sur 20
                                $moyenne+=$note->getNote()*20/$note->getEvaluation()->getTotalNote();
                                array_push($finalNote,['name' => $note->getEvaluation()->getName(), 'note' => $note]);
                            }
                        }
                    }

                    if(count($finalNote) === 0) {
                        $moyenne = 'Aucune note';
                    } else {
                        $moyenne = $moyenne/count($finalNote);
                    }

                    array_push($final, ['name' => $group->getName(), 'note' => $finalNote, 'moyenne' => $moyenne]);

                }
            }
        }

        dump($final);

        return $this->render('dashboard/account.notes.html.twig', ['notes'=>$final]);
    }
}
