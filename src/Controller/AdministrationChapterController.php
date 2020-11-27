<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\Course;
use App\Entity\Diploma;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class AdministrationChapterController extends AbstractController
{
    public function create(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Nom du chapitre',
                'attr' => ['placeholder' => ' nom du chapitre'],
                'required' => true,
                'constraints' => [new NotBlank()],

            ])
            ->add('course', EntityType::class, ['class' => Course::class, 'label'=>'Cours concerné', 'choice_label' => 'name'])

            ->add("isVisible", CheckboxType::class, [
                'required' => false,
                'label' => 'Est-il visible ?'
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier PDF à envoyer ',
                "required" => true,
                'constraints' => [
                    new NotNull(),
                    new File([
                        "mimeTypes" => ["application/pdf"],
                        "maxSize" => "100m",
                    ])
                ]


            ])
            ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            $data = $form->getData();

            $chapter = new Chapter();
            $chapter->setName($data['name']);
            $chapter->setCourseID($data['course']);
            $chapter->setIsVisible($data["isVisible"]);
            $chapter->setCreationDate(new \DateTime());
            $file = $request->files->get("form")["file"];
            $chapter->setFile(file_get_contents($file));
            $em->persist($chapter);
            $em->flush();

            return $this->redirectToRoute("administration_index");

        }
        return $this->render('administration/chapter/create.html.twig', [
            'form' => $form->createView()
            //Unique au formulaire

        ]);
    }

    public function view()
    {
        $em = $this->getDoctrine()->getManager();

        $chapters = $em->getRepository(Chapter::class)->findAll();


        return $this->render('administration/chapter/view.html.twig', [
            "chapters" => $chapters
        ]);
    }


    public function edit(Request $request, Chapter $chapter)
    {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'nom du chapitre',
                'attr' => ['placeholder' => ' nom du chapitre'],
                'required' => true,
                'constraints' => [new NotBlank()],

            ])
            ->add('course', EntityType::class, ['class' => Course::class, 'label'=>'Cours concerné', 'choice_label' => 'name'])
            ->add("isVisible", CheckboxType::class, [
                'required' => false,
                'label' => 'Est-il visible ?'
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier PDF à envoyer ',


            ])
            ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            $data = $form->getData();

            $chapter->setName($data['name']);
            $chapter->setCourseID($data['course']);
            $chapter->setIsVisible($data["isVisible"]);
            $chapter->setCreationDate(new \DateTime());
            $file = $request->files->get("form")["file"];

            $chapter->setFile(file_get_contents($file));
            $em->persist($chapter);
            $em->flush();

            //TODO : redirect
        }
        return $this->render('administration/chapter/edit.html.twig', [
            "chapters" => $chapter,
            "form" => $form->createView(),
        ]);

    }

    public function viewPDF(Request $request, Chapter $chapter){
        return new Response(stream_get_contents($chapter->getFile()), 200,
            array('Content-Type' => 'application/pdf', "")
        );
    }

}
