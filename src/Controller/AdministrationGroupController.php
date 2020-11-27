<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\GroupSection;
use App\Entity\Section;
use App\Entity\UsersGroup;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdministrationGroupController extends AbstractController
{
    public function create(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['label' => 'Nom du groupe'])
            ->add('referToSection', EntityType::class, ['class'=>Section::class, 'label'=>'Classe', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u');
            },
                'choice_label' => 'name','multiple'=>true])->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            $group = new Group();
            $groupSection = new GroupSection();
            $userGroup = new UsersGroup();
            foreach ($data['referToSection'] as $section) {
                $groupSection->addSectionID($section);
            }

            $em->persist($groupSection);
            $em->flush();

            $em->persist($userGroup);
            $em->flush();

            $group->setName($data['name']);
            $em->persist($group);

            $userGroup->addGroupID($group);

            $em->flush();

            $group->addGroupSection($groupSection);



            $em->flush();

            return $this->redirectToRoute('administration_group_view');
        }

        return $this->render('administration/group/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
    public function view()
    {
        $em = $this->getDoctrine()->getManager();

        $group = $em->getRepository(Group::class)->findAll();

        return $this->render('administration/group/view.html.twig', [
            'groups' => $group
        ]);
    }

    public function edit(Group $group, Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['label' => 'Nom du groupe', 'attr' => ['value'=>$group->getName()]])
            ->getForm();

        $addClass = $this->createFormBuilder()
            ->add('classes', EntityType::class, ['label' => 'Nom de la classe','choice_label' => 'name', 'class' => Section::class, 'multiple' =>true])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $group->setName($data['name']);
            $em->persist($group);
            $em->flush();

            return $this->redirectToRoute('administration_group_edit', ['id' => $group->getId()]);
        }

        return $this->render('administration/group/edit.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
            'addClasses' => $addClass->createView()
        ]);
    }

    public function deleteClass(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $sectionId = $request->get('sectionId');
            $groupId = $request->get('groupId');
            if(!is_numeric($sectionId) || !is_numeric($groupId)) {
                return $this->json(false);
            }
            $em = $this->getDoctrine()->getManager();
            $group = $em->getRepository(Group::class)->find($groupId);
            $section = $em->getRepository(Section::class)->find($sectionId);

            foreach($group->getGroupSections() as $groupSection) {
                $groupSection->removeSectionID($section);
            }

            $em->flush();

            return $this->json(true);
        }

        return $this->json(false);
    }

    public function addClass(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $sectionsId = $request->get('sectionsId');
            $groupId = $request->get('groupId');
            if(!is_numeric($groupId) || $sectionsId == null) {
                return $this->json(false);
            }
            $em = $this->getDoctrine()->getManager();
            $group = $em->getRepository(Group::class)->find($groupId);
            $response = array();

            foreach ($sectionsId as $sectionId) {
                $section = $em->getRepository(Section::class)->find($sectionId);
                $flag = false;
                foreach ($group->getGroupSections() as $groupSection) {
                        //we need to know if this section already exist !
                        //it simplify the ajax algorithm :P
                        foreach($groupSection->getSectionID() as $sectionIt) {
                            if($sectionIt->getId() == $section->getId()) {
                                $flag = true;
                            }
                        }
                        if(!$flag) {
                            $groupSection->addSectionID($section);
                            array_push($response, array($section->getId(), $section->getName(), $section->getDiploma()->getName()));
                        }
                }
            }

            $em->flush();
            return $this->json(json_encode($response));
        }
        return $this->json(false);
    }

    public function delete(Request $request) {
        if($request->isXmlHttpRequest()) {
            $groupId = $request->get('groupId');
            if(!is_numeric($groupId)) {
                return $this->json(false);
            }
            $em = $this->getDoctrine()->getManager();
            $group = $em->getRepository(Group::class)->find($groupId);

            if($group != null) {
                foreach ($group->getUsersGroups() as $usersGroup) {
                    foreach ($usersGroup->getGroupID() as $group) {
                        $usersGroup->removeGroupID($group);
                    }

                    foreach ($usersGroup->getUserID() as $users) {
                        $usersGroup->removeUserID($users);
                    }
                    $em->remove($usersGroup);
                }

                foreach ($group->getGroupSections() as $groupSection) {
                    foreach ($groupSection->getGroupID() as $group) {
                        $groupSection->removeGroupID($group);
                    }

                    foreach ($groupSection->getSectionID() as $section) {
                        $groupSection->removeSectionID($section);
                    }
                    $em->remove($groupSection);
                }

                $em->remove($group);

                $em->flush();

                return $this->json(true);
            }
        }

        return $this->json(false);
    }
}
