<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\Course;
use App\Entity\Evaluations;
use App\Entity\EvaluationsQuestions;
use App\Entity\Users;
use App\Utils\Compile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrainingController extends AbstractController
{
    public function index(Request $request)
    {

        $form = $this->createFormBuilder()
            ->add('course', EntityType::class, ['class' => Course::class, 'choice_label' => 'name', 'label' => 'Choisissez le cours'])
            ->add('chapter', EntityType::class, ['class' => Chapter::class, 'choice_label' => 'name', 'label' => 'Choisissez le chapitre '])
            ->getForm();

        $em = $this->getDoctrine()->getManager();

        $courses = $em->getRepository(Course::class)->findBy(['isVisible' => true]);

        $form->handleRequest($request);

        $training = null;

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $chapterObj = $data['chapter'];

            if( !($chapterObj instanceof Chapter))
                return $this->json('Erreur');

            $training = $em->getRepository(Evaluations::class)->findBy(['chapterID' => $data['chapter'], 'isEval' => false]);
        }

        return $this->render('training/index.html.twig', [
            'courses' => $courses,
            'form' => $form->createView(),
            'training' => $training
        ]);
    }

    public function do(Evaluations $evaluations)
    {
        if($evaluations == null)
            return $this->json('error');

        //Check si c'est une évaluation
        if($evaluations->getIsEval() == true)
            return $this->redirectToRoute('dashboard_evaluations');

        $questions = [];

        foreach($evaluations->getEvaluationsQuestions() as $evaluationsQuestion) {
            $temp = [];
            $temp['questionId']      = $evaluationsQuestion->getId();
            $temp['question']   = $evaluationsQuestion->getQuestion();
            $temp['isCode']     = ($evaluationsQuestion->getCode() != null) ? true : false;
            $temp['code']       = $evaluationsQuestion->getCode();

            if($evaluationsQuestion->getType() != null)
                $temp['type']   = ['language' => $evaluationsQuestion->getType()->getLanguage()];
            else
                $temp['type']   = null;

            $temp['answer']     = [];
            $temp['jeu']        = $evaluationsQuestion->getTestedKeys();
            foreach($evaluationsQuestion->getEvaluationsAnswers() as $answer) {
                $temp2 = [];
                $temp2['answer'] = $answer->getAnswer();
                $temp2['isTrue'] = $answer->getIsTrue();
                array_push($temp['answer'], $temp2);
                unset($temp2);
            }
            array_push($questions, $temp);

        }

        return $this->render('training/do.html.twig', [
            'data' => $questions
        ]);
    }

    public function compile(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            //TODO sécuriser cette entrée !!!

            //get Informations
            $id = $request->get('questionId');

            if(!is_numeric($id))
                return $this->json('C\'est pas bien !'); //FIXME trouvé une autre phrase

            $code = $request->get('code');

            $question =  $this->getDoctrine()->getRepository(EvaluationsQuestions::class)->find($id);

            if($question->getType() != null) {
                $userAnswer = Compile::compile($question->getType(),$code,$question->getTestedKeys());
                $goodAnswer = Compile::compile($question->getType(), $question->getEvaluationsAnswers()[0]->getAnswer
                (), $question->getTestedKeys());

                if(count(array_diff($userAnswer,$goodAnswer)) == 0) {
                    //Success
                    return $this->json(json_encode([true, 'Bonne réponse !', null]));
                } else {
                    return $this->json(json_encode([false, 'Le programme est erronné',
                        $question->getEvaluationsAnswers()[0]->getAnswer()]));
                }
            } else {
                return $this->json(json_encode([false ,'La question n\'est pas une question de type code', null]));
            }
        }

        return $this->json(json_encode([false,'Impossible de traiter votre demande', null]));
    }
}
