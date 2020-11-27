<?php

namespace App\Controller;

use App\Entity\Evaluations;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdministrationController extends AbstractController
{
    public function index()
    {
        $em = $this->getDoctrine()->getManager();

        //Get Number of users
        $nbUsers = $em->getRepository(Users::class)->count([]);
        $nbEval = $em->getRepository(Evaluations::class)->count([]);

        return $this->render('administration/index.html.twig', [
            'nbUsers' => $nbUsers,
            'nbEval' => $nbEval
        ]);
    }
}
