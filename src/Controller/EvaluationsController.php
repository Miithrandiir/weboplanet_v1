<?php

namespace App\Controller;

use App\Entity\Evaluations;
use App\Entity\EvaluationsAnswer;
use App\Entity\EvaluationsDatas;
use App\Entity\EvaluationsGroup;
use App\Entity\EvaluationsNotes;
use App\Entity\EvaluationsQuestions;
use App\Entity\Users;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

class EvaluationsController extends AbstractController
{
    public function index()
    {
        $user = $this->getDoctrine()->getRepository(Users::class)->find($this->getUser()->getId());

        $eval = array();

        foreach($user->getUsersGroups() as $usersGroup) {
            foreach($usersGroup->getGroupID() as $group) {
                foreach($group->getEvaluationsGroups() as $evaluationsGroup) {
                    foreach($evaluationsGroup->getEvaluationsID() as $evaluation) {
                        if($evaluation->getIsEval()) {

                            $data = $this->getDoctrine()->getRepository(EvaluationsDatas::class)->findBy(['evaluationID' => $evaluation, 'userID' => $user]);
                            $isDone = false;
                            $counter = 0;

                            foreach($user->getEvaluationsDatas() as $evaluationsData) {
                                if($evaluationsData->getEvaluationID()->getId() == $evaluation->getId()) {
                                    $counter++;
                                }
                            }
                            $total =0;
                            foreach($evaluation->getEvaluationsQuestions() as $question) {
                                $total += $question->getPoints();
                            }

                            if($counter == $evaluation->getEvaluationsQuestions()->count()) {
                                $isDone = true;
                            }

                            $evaluationNote = $this->getDoctrine()->getRepository(EvaluationsNotes::class)->findOneBy(['evaluation' => $evaluation, 'user' => $user]);

                            array_push($eval, [
                                $evaluationsGroup->getDateStart(), //Date de début [0]
                                $evaluationsGroup->getDateEnd(),  //Date de fin [1]
                                $evaluation, //Object: Evaluation [2]
                                $data, //Les réponses fournies [3]
                                $isDone, //A-t-il fini l'évaluation [4]
                                $evaluationNote, // [5]
                                $total // [6]
                            ]);
                        }
                    }
                }
            }
        }

        //need to create an array which has this order: warning ( < 3 days) to danger( < 0 days)
        usort($eval, function (array $a, array $b){
            $diffa = strtotime($a[1]->format('Y-m-d H:i:s')) - time();
            $diffb = strtotime($b[1]->format('Y-m-d H:i:s')) - time();

            if($a[4] == true && $b[4] == true) {
                return 0;
            } else if($b[4] == true && $a[4] == false) {
                return 0;
            } else if($b[4] == false && $a == true) {
                return 1;
            }


            if($diffa == $diffb)
                return 0;

            elseif($diffa < 0)
                return 1;

            elseif($diffb < 0)
                return -1;
            else
            return ($diffa > $diffb) ? 1 : -1;
        });

        return $this->render('evaluations/index.html.twig', [
            'evaluations' => $eval
        ]);
    }

    public function do(Evaluations $evaluation, Request $request)
    {

        $user = $this->getDoctrine()->getRepository(Users::class)->find($this->getUser()->getId());

        $isRegister=false;
        $date_end=null;

        if($evaluation->getIsEval() == false)
            return $this->redirectToRoute('training');

        foreach ($evaluation->getEvaluationsGroups() as $evaluationsGroup) {
            foreach ($evaluationsGroup->getGroupID() as $group) {
                foreach($user->getUsersGroups() as $usersGroup) {
                    foreach($usersGroup->getGroupID() as $groupSearch) {
                        if($group->getId() == $groupSearch->getId()) {
                            $isRegister=true;
                            $date_end = strtotime($evaluationsGroup->getDateEnd()->format('Y-m-d H:i:s'));
                        }
                    }
                }
            }
        }

        if($date_end < time() || !$isRegister) {
            return $this->redirectToRoute('dashboard_evaluations');
        }
        $countQuestion = 0;
        $alreadyAnswer = [];
        foreach($evaluation->getEvaluationsDatas() as $datas) {
            if($datas->getUserID()->getId() == $user->getId()) {
                $countQuestion++;
                array_push($alreadyAnswer, $datas->getEvaluationsQ());
            }
        }

        $questions = [];
        foreach($evaluation->getEvaluationsQuestions() as $question) {
            array_push($questions, $question);
        }

        if($evaluation->getEvaluationsQuestions()->count() == $countQuestion) {
            //he has already answer to all questions !
            //@TODO alerte ?
            return $this->redirectToRoute('dashboard_evaluations');
        }


        $flag = false;
        $random=0;

        while(!$flag) {
            $flag=true;
            $random = rand(0, count($questions)-1);

            foreach($alreadyAnswer as $value) {
                if($value->getId() == $questions[$random]->getId()) {
                    $flag=false;
                }
            }
        }


        //Check what is the type of the evaluation

        $evalSelected = $questions[$random];

        /**
         *  CODE SECTION
         */
        if($evalSelected->getEvaluationsAnswers()->count() == 1 && $evalSelected->getType() != null) {
            $form = $this->createFormBuilder()
                ->add('answer', TextareaType::class, [ 'attr' => ['class' => 'form_group']])
                ->getForm();


            return $this->render('evaluations/eval.code.html.twig', [
                'evaluation' => $evaluation,
                'question' => $evalSelected,
                'form' => $form->createView()
            ]);

        } /**
         * QCM SECTION
         */
        else {
            $goodAnswer=0;
            foreach($evalSelected->getEvaluationsAnswers() as $answer) {
                if ($answer->getIsTrue() == true)
                    $goodAnswer++;
            }

            //checkbox !
            if($goodAnswer > 1) {
                $form = $this->createFormBuilder()
                    ->add('answer', EntityType::class, ['class' => EvaluationsAnswer::class,'query_builder' => function(EntityRepository $er) use ($evalSelected) {
                        return $er->createQueryBuilder('u')
                            ->where('u.evalQuestionID = :id')->setParameter('id',$evalSelected->getId());
                    } ,'choice_label' => 'answer','multiple' => true,'expanded' => true, 'choice_attr' => ['class' => 'form_group'], 'attr' => ['class' => 'form_group']])
                    ->getForm();

            } else {
                $form = $this->createFormBuilder()
                    ->add('answer', EntityType::class, ['class' => EvaluationsAnswer::class,'query_builder' => function(EntityRepository $er) use ($evalSelected) {
                        return $er->createQueryBuilder('u')
                            ->where('u.evalQuestionID = :id')->setParameter('id',$evalSelected->getId());
                    } ,'choice_label' => 'answer','multiple' => false,'expanded' => true, 'choice_attr' => ['class' => 'form_group'], 'attr' => ['class' => 'form_group']])
                    ->getForm();
            }
            return $this->render('evaluations/eval.qcm.html.twig', [
                'question' => $evalSelected,
                'form' => $form->createView()
            ]);
        }
    }

    public function saveData(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $evaluationQId = $request->get('evalQId');
            $responses = $request->get('responses');

            if(!is_numeric($evaluationQId))
                return $this->json([false, ["Erreur: L'id ne peut être null."]]);

            $em = $this->getDoctrine()->getManager();
            $evaluationQ = $em->getRepository(EvaluationsQuestions::class)->find($evaluationQId);

            //QCM / QCU Type
            $evaluationData = new EvaluationsDatas();
            $evaluationData->setUserID($this->getUser());
            $evaluationData->setDate(new \DateTime());
            $evaluationData->setEvaluationID($evaluationQ->getEvaluationsID());
            $evaluationData->setEvaluationsQ($evaluationQ);

            if($evaluationQ->getType() != null) {
                //CODE TYPE
                $evaluationData->setCodeResponse($responses);
            } else {
                //QCM
                if(is_array($responses)) {
                    foreach($responses as $response) {
                        $answer = $em->getRepository(EvaluationsAnswer::class)->find($response);
                        if($answer != null) {
                            $evaluationData->addEvaluationsA($answer);
                        }
                    }
                    //QCU
                } else {
                    $answer = $em->getRepository(EvaluationsAnswer::class)->find($responses);
                    if(!$answer) {
                        $evaluationData->addEvaluationsA($answer);
                    }
                }
            }

            $em->persist($evaluationData);
            $em->flush();

            return $this->json(true);
        }
    }

    public function viewResult(Evaluations $evaluation)
    {
        $answers = [];

        foreach($evaluation->getEvaluationsDatas() as $evaluationsData) {
            if($evaluationsData->getUserID()->getId() == $this->getUser()->getId()) {
                array_push($answers, $evaluationsData);
            }
        }

        dump($answers);


        return $this->render('evaluations/eval.note.html.twig', [
            'evaluation' => $evaluation,
            'answers' => $answers
        ]);
    }
}
