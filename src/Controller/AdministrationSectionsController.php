<?php

namespace App\Controller;

use App\Entity\Diploma;
use App\Entity\Section;
use App\Entity\SectionRef;
use App\Entity\Users;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdministrationSectionsController extends AbstractController
{

    public function create(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['label' => 'Nom de la classe', 'attr' => ['placeholder' => 'Nom de la classe (Ex: Licence 1 Informatique)']])
            ->add('diploma', EntityType::class, ['class' => Diploma::class, 'label'=>'Diplôme concerné', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u');
            },
                'choice_label' => 'name'])
            ->add('users', EntityType::class, ['class'=>Users::class, 'label' => 'Professeurs référents', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u');
            },
                'choice_label' => 'name',
                'multiple' => true,
                'attr' => array('data-placeholder' => 'Professeurs référents')
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            $sectionRef = new SectionRef();
            foreach($data['users'] as $user) {
                $sectionRef->addUserID($user);
            }
            $em->persist($sectionRef);
            $em->flush();

            $section = new Section();
            $section->setName($data['name']);
            $section->setDiploma($data['diploma']);
            $section->setSectionRef($sectionRef);
            $em->persist($section);
            $em->flush();

            //@TODO modifier (Je sais pas quoi ?)
            return $this->redirectToRoute('administration_section_view');
        }

        return $this->render('administration/sections/create.html.twig', [
            'controller_name' => 'AdministrationSectionsController',
            'form' => $form->createView()
        ]);
    }

    public function view()
    {
        $em = $this->getDoctrine()->getManager();

        $section = $em->getRepository(Section::class)->findAll();

        return $this->render('administration/sections/view.html.twig', [
            'sections' => $section
        ]);
    }

    public function edit(int $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $section = $em->getRepository(Section::class)->find($id);

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['label' => 'Nom de la classe', 'constraints'=>new NotBlank() ,'attr' => ['placeholder' => 'Nom de la classe (Ex: Licence 1 Informatique)', 'value' => $section->getName()]])
            ->add('diploma', EntityType::class, ['class' => Diploma::class, 'constraints'=>new NotBlank(),'label'=>'Diplôme concerné', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u');
            },
                'choice_label' => 'name',
                'preferred_choices' => [$section->getDiploma()]])
            ->getForm();

        $addProf = $this->createFormBuilder()
            ->add('users', EntityType::class, ['class' => Users::class, 'label'=>'Professeur', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')->where('u.roles LIKE \'%"ROLE_ADMIN"%\'')->orWhere('u.roles LIKE \'%"ROLE_ENSG"%\'');
            },
                'choice_label' => 'name',
                'preferred_choices' => [$section->getDiploma()],
                'multiple' => true,
                'attr' => ['placeholder'=>'Professeurs'],
                'allow_extra_fields' => true
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $section->setName($data['name']);
            $section->setDiploma($data['diploma']);

            $em->flush();

            return $this->redirectToRoute('administration_section_edit', ['id'=>$section->getId()]);
        }

        return $this->render('administration/sections/edit.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
            'formProf' => $addProf->createView()
        ]);
    }

    public function deleteUser(Request $request)
    {

        if($request->isXmlHttpRequest()) {
            $sectionID = $request->get('sectionID');
            $userID = $request->get('user');
            $em = $this->getDoctrine()->getManager();
            $section = $em->getRepository(Section::class)->find($sectionID);
            $user = $em->getRepository(Users::class)->find($userID);
            $section->getSectionRef()->removeUserID($user);
            $em->flush();

            return $this->json('true');
        } else {
            return $this->json('false');
        }
    }

    public function addProf(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $sectionID = $request->get('sectionID');
            if(!is_numeric($sectionID)) {
                return $this->json(false);
            }
            $users = $request->get('users');
            $section = $em->getRepository(Section::class)->find($sectionID);

            $user_add = array();

            foreach($users as $userid) {
                $user = $em->getRepository(Users::class)->find($userid);
                if(array_search('ROLE_ENSG',$user->getRoles()) !== FALSE OR array_search('ROLE_ADMIN',$user->getRoles()) !== FALSE) {
                    $flag=false;
                    foreach ($section->getSectionRef()->getUserID() as $users) {
                        if($users->getId() == $user->getId())
                            $flag=true;
                    }

                    if($flag === false) {
                        //The user is not already in section
                        $section->getSectionRef()->addUserID($user);
                        array_push($user_add, array($user->getId(),$user->getName(),$user->getSectionRefs()->count()));
                        $em->flush();
                    }
                }
            }
            return $this->json(json_encode($user_add));

        }

        return $this->json('false');
    }

    public function delete(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $sectionID = $request->get('sectionID');
            if(!is_numeric($sectionID)) {
                return $this->json('false');
            }
            $section = $em->getRepository(Section::class)->find($sectionID);

            //We get all users that we need, the goal is to delete all user object in the database
            foreach($section->getSectionRef()->getUserID() as $userID) {
                $user = $em->getRepository(Users::class)->find($userID);

                $section->getSectionRef()->removeUserID($user);

            }

            $em->flush();
            $em->remove($section);
            $em->flush();
            $em->remove($section->getSectionRef());
            $em->flush();

        }

        return $this->json('true');
    }
}
