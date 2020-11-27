<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Chapter;
use App\Entity\Course;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();

        $getCours = $em->getRepository(Course::class)->findAll();
        $getChapter = $em->getRepository(Chapter::class)->findAll();
        $getLastArticle = $em->getRepository(Articles::class)->findBy([],["id"=>"DESC"],2);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'articles' => $getLastArticle,
            'courses' => $getCours,
            'chapters' => $getChapter

        ]);

    }

    public function viewPDF(Request $request, Chapter $chapter){
        return new Response(stream_get_contents($chapter->getFile()), 200,
            array('Content-Type' => 'application/pdf', "")
        );
    }
}
