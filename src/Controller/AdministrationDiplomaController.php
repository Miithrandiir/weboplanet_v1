<?php

namespace App\Controller;

use App\Entity\Diploma;
use App\Entity\Users;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class AdministrationDiplomaController extends AbstractController
{
    public function create(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('diplomaName', TextType::class, ['label' => 'Nom du diplôme', 'attr' => ['placeholder' => 'Nom du diplôme']])
            ->add('users', EntityType::class, ['class' => Users::class, 'label'=>'Professeur référent', 'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->where('u.roles LIKE \'%"ROLE_ADMIN"%\'')->orWhere('u.roles LIKE \'%"ROLE_ENSG"%\'');
                },
                'choice_label' => 'name',])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getdata();
            $em = $this->getDoctrine()->getManager();
            $diploma = new Diploma();

            $diploma->setName($data['diplomaName']);
            $diploma->setUser($data['users']);

            $em->persist($diploma);
            $em->flush();

            return $this->redirectToRoute('administration_diploma_view');
        }

        return $this->render('administration/diploma/create.html.twig', [
            'controller_name' => 'AdministrationDiplomaController',
            'form' => $form->createView()
        ]);
    }

    public function view()
    {
        $em = $this->getDoctrine()->getManager();

        $diploma = $em->getRepository(Diploma::class)->findAll();

        return $this->render('administration/diploma/view.html.twig', [
            'controller_name' => 'AdministrationDiplomaController',
            'diploma' => $diploma
        ]);
    }

    public function edit(int $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //Cjercje le diplome
        $diploma = $em->getRepository(Diploma::class)->find($id);

        //Cherche les enseignant
        $ensg = $em->getRepository(Users::class)->createQueryBuilder('u')->where('u.roles LIKE \'%"ROLE_ADMIN"%\'')->orWhere('u.roles LIKE \'%"ROLE_ENSG"%\'')->getQuery()->getResult();


        $form = $this->createFormBuilder()
            ->add('diplomaName', TextType::class, ['label' => 'Nom du diplôme', 'attr' => ['placeholder' => 'Nom du diplôme']])
            ->add('users', EntityType::class, ['class' => Users::class, 'label'=>'Professeur référent', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')->where('u.roles LIKE \'%"ROLE_ADMIN"%\'')->orWhere('u.roles LIKE \'%"ROLE_ENSG"%\'');
            },
                'choice_label' => 'name',
                'preferred_choices' => [$diploma->getUser()]])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getdata();

            $diploma->setName($data['diplomaName']);
            $diploma->setUser($data['users']);
            $em->flush();

            return $this->redirectToRoute('administration_diploma_view');
        }

        return $this->render('administration/diploma/edit.html.twig', [
            'controller_name' => 'AdministrationDiplomaController',
            'form' => $form->createView(),
            'diploma' => $diploma,
            'ensg' => $ensg
        ]);
    }

    public function delete(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $diploma = $em->getRepository(Diploma::class)->find($id);
        $users = $em->getRepository(Users::class)->findBy(['degree' => $diploma]);

        foreach($users as $user) {
            $em->remove($user);
        }

        $em->flush();
        $em->remove($diploma);
        $em->flush();

        return $this->redirectToRoute('administration_diploma_view');
    }
}
