<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    public function index()
    {
        $user = new Users();

        $courses = $this->getDoctrine()->getRepository(Course::class)->findAll();

        return $this->render('course/index.html.twig', [
            'courses' => $courses
        ]);
    }
}
