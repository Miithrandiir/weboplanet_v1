<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Users;
use Doctrine\ORM\EntityRepository;
use phpDocumentor\Reflection\Types\This;
use function PHPSTORM_META\type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdministrationCourseController extends AbstractController
{


    public function view(){

        $em = $this->getDoctrine()->getManager();
        $courses = $em->getRepository(Course::class)->findAll();
        // $coursbis = $em->getRepository(Course::class)->find($cours->getId());
        // récupération des donées stockés


        return $this->render('administration/course/view.html.twig', [
            'courses' => $courses

        ]);
    }

    public function create(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('name',TextType::class,[
                'label'=>'nom du cours',
                'attr'=>['placeholder'=>'nom du cours'],
                "required" => true,
                "constraints" => [
                    new NotBlank(),
                ]
            ])
            ->add('users', EntityType::class, [
                'class' => Users::class, 'label'=>'Professeur référent',
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')->where('u.roles LIKE \'%"ROLE_ADMIN"%\'')->orWhere('u.roles LIKE \'%"ROLE_ENSG"%\'');
            },
                'choice_label' => 'name',])
            ->add("isVisible", CheckboxType::class, [
               'required' => false,
                'label' => 'Est-il visible ?'
           ])
        ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){


            $data = $form->getData();



            $cours = new Course();

            $cours->setName($data["name"]);
            $cours->setIsVisible($data["isVisible"]);
            $cours->setUserID($data['users']);
            $cours->setCreationDate(new \DateTime());

            $em->persist($cours);
            $em->flush();


            return $this->redirectToRoute("administration_index");

        }
        return $this->render('administration/course/create.html.twig', [
            'form' => $form->createView()
            //Unique au formulaire
        ]);
    }

    public function edit(Request $request,Course $course)
    {
        $em = $this->getDoctrine()->getManager();


        $form = $this->get("form.factory")->createNamedBuilder("editCourseForm", FormType::class, $course)
            ->add('name', TextType::class, [
                'label' => 'nom du cours',

            ])
            ->add('userID', EntityType::class, [
                'class' => Users::class, 'label' => 'Professeur référent',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->where('u.roles LIKE \'%"ROLE_ADMIN"%\'')->orWhere('u.roles LIKE \'%"ROLE_ENSG"%\'');
                },
                'choice_label' => 'name',])
            ->getForm();



        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            return $this->redirectToRoute('administration_course_view');
        }
        return $this->render('administration/course/edit.html.twig', [
            'controller_name' => 'AdministrationCourseController',
            'form' => $form->createView(),
            'course' => $course,
        ]);
    }


}
