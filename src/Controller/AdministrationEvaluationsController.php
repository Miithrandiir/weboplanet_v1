<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\Evaluations;
use App\Entity\EvaluationsAnswer;
use App\Entity\EvaluationsGroup;
use App\Entity\EvaluationsQuestions;
use App\Entity\EvaluationsTypes;
use App\Entity\Group;
use DateTime;
use http\Env\Response;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class AdministrationEvaluationsController extends AbstractController
{

    public function typeCreate(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['label' => 'Nom du type', 'attr' => ['placeholder' => 'CPP 14 par exemple'] ])
            ->add('language', TextType::class, ['label' => 'Langage utilisé', 'attr' => ['placeholder'=>'CPP'] ])
            ->add('command', TextType::class, ['label' => 'Commande qu\'executera le serveur'])
            ->add('bannedWords', TextType::class, ['label' => 'Mots interdits (Séparés par une virgule). Remplacer le nom du fichier par {fichier}'])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $bannedWords = array();
            $str = explode(',',$data['bannedWords']);

            foreach ($str as $words) {
                array_push($bannedWords, $words);
            }
            $evalType = new EvaluationsTypes();
            $evalType->setName($data['name']);
            $evalType->setLanguage($data['language']);
            $evalType->setCommand($data['command']);
            $evalType->setBannedWords($bannedWords);

            $em->persist($evalType);
            $em->flush();

            return $this->redirectToRoute('administration_evaluations_type_view');
        }

        return $this->render('administration/evaluations/type/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

    public function typeView()
    {
        $evalTypes = $this->getDoctrine()->getRepository(EvaluationsTypes::class)->findAll();

        return $this->render('administration/evaluations/type/view.html.twig',[
            'evalTypes' => $evalTypes
        ]);
    }

    public function typeEdit(EvaluationsTypes $evaluationsTypes, Request $request)
    {
        $bannedWordsStr = "";

        foreach($evaluationsTypes->getBannedWords() as $evaluationsType) {
            $bannedWordsStr .= $evaluationsType;
            $bannedWordsStr .= ",";
        }

        $bannedWordsStr = substr($bannedWordsStr, 0, -1);

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['label' => 'Nom du type', 'attr' => ['placeholder' => 'CPP 14 par exemple', 'value' => $evaluationsTypes->getName()] ])
            ->add('language', TextType::class, ['label' => 'Langage utilisé', 'attr' => ['placeholder'=>'CPP', 'value' => $evaluationsTypes->getLanguage()] ])
            ->add('command', TextType::class, ['label' => 'Commande qu\'executera le serveur', 'attr' => ['value' => $evaluationsTypes->getCommand()]])
            ->add('bannedWords', TextType::class, ['label' => 'Mots interdits (Séparés par une virgule).', 'attr' => ['value' => $bannedWordsStr]])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $bannedWords = array();
            $str = explode(',',$data['bannedWords']);

            foreach ($str as $words) {
                array_push($bannedWords, $words);
            }

            $evaluationsTypes->setName($data['name']);
            $evaluationsTypes->setLanguage($data['language']);
            $evaluationsTypes->setCommand($data['command']);
            $evaluationsTypes->setBannedWords($bannedWords);

            $em->flush();

            return $this->redirectToRoute('administration_evaluations_type_view');
        }

        return $this->render('administration/evaluations/type/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function deleteEvalType(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $id = $request->get('evalTypeId');
            if(!is_numeric($id)) {
                return $this->json([false, 'err:id']);
            }

            $em = $this->getDoctrine()->getManager();
            $evalType = $em->getRepository(EvaluationsTypes::class)->find($id);

            if($evalType->getEvaluationsQuestions()->count() > 0) {
                return $this->json([false, 'err:questions']);
            } elseif($evalType->getEvaluationsQuestions()->count() == 0) {
                $em->remove($evalType);
                $em->flush();

                return $this->json([true,null]);
            }
        }
        return $this->json(false);
    }

    public function create(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('chapter', EntityType::class, ['class' => Chapter::class, 'choice_label' => 'name','label' => 'Chapitre au quel est relié l\'évaluation'])
            ->add('name', TextType::class, ['label' => 'Nom de l\'évaluation'])
            ->add('isEval', CheckboxType::class, ['label' => 'Est ce une évaluation ?   '])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $eval = new Evaluations();

            $eval->setChapterID($data['chapter']);
            $eval->setIsEval($data['isEval']);
            $eval->setName($data['name']);
            $eval->setCreatorID($this->getUser());

            $em->persist($eval);
            $em->flush();

            return $this->redirectToRoute('administration_evaluations_edit', ['id' => $eval->getId()]);
        }


        return $this->render('administration/evaluations/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function edit(Evaluations $evaluations,Request $request)
    {

        $form = $this->createFormBuilder()
            ->add('chapter', EntityType::class, ['class' => Chapter::class,'choice_label' => 'name' ,'label' => 'Chapitre au quel est relié l\'évaluation', 'preferred_choices' => [$evaluations->getChapterID()]])
            ->add('name', TextType::class, ['label' => 'Nom de l\'évaluation', 'attr' => ['value' => $evaluations->getName()]])
            ->add('isEval', CheckboxType::class, ['label' => 'Est-ce une évaluation ?', 'attr' => ($evaluations->getIsEval() == true)  ? ['checked'=>'checked'] : [], 'required' => false])
            ->getForm();

        $addQuestion = $this->createFormBuilder()
            ->add('nameQ', TextType::class, ['label' => 'Nom de la question'])
            ->add('points', IntegerType::class, ['label' => 'Nombre de points que gagne l\'utilisateur'])
            ->add('correctionRules', ChoiceType::class, ['label' => 'Mode de correction', 'choices' => ['Strict' => 1, 'Doux' => 2]])
            ->add('testedKeys', TextType::class, ['label' => 'Jeux d\'essaies'])
            ->add('code', TextareaType::class, ['label' => 'Code, insérer {ici} pour désigner la partie que l\'utilisateur doit remplir', 'attr' => ['style' => 'resize: vertical;']])
            ->add('type', EntityType::class, ['class' => EvaluationsTypes::class,'choice_label' => 'name','label' => 'Remplir que si la question est du code', 'required' => false,])
            ->getForm();

        $addQuestion2 = $this->createFormBuilder()
            ->add('EnameQ', TextType::class, ['label' => 'Nom de la question'])
            ->add('Epoints', IntegerType::class, ['label' => 'Nombre de points que gagne l\'utilisateur'])
            ->add('EcorrectionRules', ChoiceType::class, ['label' => 'Mode de correction', 'choices' => ['Strict' => 1, 'Doux' => 2]])
            ->add('EtestedKeys', TextType::class, ['label' => 'Jeux d\'essaies'])
            ->add('Ecode', TextareaType::class, ['label' => 'Code, insérer {ici} pour désigner la partie que l\'utilisateur doit remplir', 'attr' => ['style' => 'resize: vertical;']])
            ->add('Etype', EntityType::class, ['class' => EvaluationsTypes::class,'choice_label' => 'name','label' => 'Remplir que si la question est du code', 'required' => false,])
            ->getForm();

        $addAnswer = $this->createFormBuilder()
            ->add('answer', CollectionType::class, ['entry_type' => TextareaType::class, 'attr' => ['class' => 'form-group'], 'entry_options' => ['attr' => ['class' => 'form-control', 'style' => 'resize: vertical;']], 'allow_add' => true, 'label' => 'Ajout  de réponses'])
            ->add('isTrue', CollectionType::class, ['entry_type' => CheckboxType::class, 'attr' => ['class' => 'form-group'], 'entry_options' => ['attr' => ['class' => 'form-control', 'style' => 'resize: vertical;']], 'allow_add' => true, 'label' => 'Ajout  de réponses'])
            ->getForm();

        $addAnswer2 = $this->createFormBuilder()
            ->add('Eanswer', CollectionType::class, ['entry_type' => TextareaType::class, 'attr' => ['class' => 'form-group'], 'entry_options' => ['attr' => ['class' => 'form-control', 'style' => 'resize: vertical;']], 'allow_add' => true, 'label' => 'Ajout  de réponses'])
            ->add('EisTrue', CollectionType::class, ['entry_type' => CheckboxType::class, 'attr' => ['class' => 'form-group'], 'entry_options' => ['attr' => ['class' => 'form-control', 'style' => 'resize: vertical;']], 'allow_add' => true, 'label' => 'Ajout  de réponses'])
            ->getForm();

        $addToGroup = $this->createFormBuilder()
            ->add('groups', EntityType::class, ['class' => Group::class, 'choice_label' => 'name', 'attr' => ['class' => 'form-control'], 'multiple' => true, 'label' => 'Sélectionner le/les groupe(s)'])
            ->add('date', DateTimeType::class, ['label' => 'Interval où le contrôle est autorisé', 'attr' => ['class' => 'form-control'], 'widget' => 'single_text', 'html5' => false])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            $evaluations->setName($data['name']);
            $evaluations->setChapterID($data['chapter']);
            $evaluations->setIsEval($data['isEval']);

            $em->flush();

            return $this->redirectToRoute('administration_evaluations_edit', ['id' => $evaluations->getId()]);
        }

        return $this->render('administration/evaluations/edit.html.twig', [
            'form' => $form->createView(),
            'addQuestion' => $addQuestion->createView(),
            'addQuestion2' => $addQuestion2->createView(),
            'addAnswer' => $addAnswer->createView(),
            'addAnswer2' => $addAnswer2->createView(),
            'addGroup' => $addToGroup->createView(),
            'evaluation' => $evaluations
        ]);
    }

    public function view()
    {
        $evaluations = $this->getDoctrine()->getRepository(Evaluations::class)->findAll();


        return $this->render('administration/evaluations/view.html.twig', [
           'evaluations' => $evaluations
        ]);
    }

    public function delete(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $evalId = $request->get('evalId');

            if(!is_numeric($evalId))
                return $this->json(false);

            $em = $this->getDoctrine()->getManager();

            $evaluation = $em->getRepository(Evaluations::class)->find($evalId);

            //Suppression des questions
            foreach($evaluation->getEvaluationsQuestions() as $question) {
                foreach($question->getEvaluationsAnswers() as $answer) {
                    $em->remove($answer);
                }
                $em->flush();
                $em->remove($question);
                $em->flush();
            }
            //Suppression des groupes
            foreach($evaluation->getEvaluationsGroups() as $group) {
                $em->remove($group);
            }
            $em->flush();
            //suppression des données
            foreach($evaluation->getEvaluationsDatas() as $data) {
                $em->remove($data);
            }
            $em->flush();
            //Suppression de l'évaluation
            $em->remove($evaluation);
            $em->flush();

            return $this->json(true);
        }

        return $this->json(false);
    }

    public function addQuestion(Request $request)
    {
        if($request->isXmlHttpRequest()) {

            $evalId = $request->get('evalId');

            if(!is_numeric($evalId))
                return $this->json(false);

            $question = $request->get('question');
            $points = $request->get('points');
            $typeId = $request->get('typeId');
            $code = $request->get('code');
            $answer = $request->get('answer');
            $correction_rules = $request->get('correctionRules');
            $testedKeys = explode(',',$request->get('testedKeys'));

            if((!is_numeric($typeId) && $typeId != null) || !is_numeric($points)) {
                return $this->json(false);
            }

            $em = $this->getDoctrine()->getManager();

            $eval = $em->getRepository(Evaluations::class)->find($evalId);
            $type = null;
            if($typeId != null) {
               $type = $em->getRepository(EvaluationsTypes::class)->find($typeId);
            }
            $evalQuestion = new EvaluationsQuestions();
            $evalQuestion->setEvaluationsID($eval);
            $evalQuestion->setQuestion($question);
            $evalQuestion->setPoints($points);
            $evalQuestion->setType($type);
            $evalQuestion->setCode($code);
            $evalQuestion->setCorrectionRule($correction_rules);
            $evalQuestion->setTestedKeys($testedKeys);

            $em->persist($evalQuestion);
            $em->flush();

            for($i=0;$i<sizeof($answer);$i++) {
                $answerQ = new EvaluationsAnswer();

                $answerQ->setEvalQuestionID($evalQuestion);
                $answerQ->setAnswer($answer[$i][0]);
                $tmp = ($answer[$i][1] == "true") ? true : false;
                $answerQ->setIsTrue($tmp);

                $em->persist($answerQ);
                $em->flush();
            }

            return $this->json($evalQuestion->getId());
        }

        return $this->json(false);
    }

    public function editQuestion(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $questionId = $request->get('questionId');

            if (!is_numeric($questionId))
                return $this->json(false);

            $questionN = $request->get('question');
            $points = $request->get('points');
            $typeId = $request->get('typeId');
            $code = $request->get('code');
            $answers = $request->get('answers');
            $correction_rules = $request->get('correctionRules');
            $testedKeys = explode(',', $request->get('testedKeys'));

            $em = $this->getDoctrine()->getManager();

            $question = $em->getRepository(EvaluationsQuestions::class)->find($questionId);
            $question->setQuestion($questionN);
            $question->setPoints($points);
            if($typeId != null) {
                $type = $em->getRepository(EvaluationsTypes::class)->find($typeId);
                $question->setType($type);
            } else {
                $question->setType(null);
            }
            $question->setCode($code);
            $question->setCorrectionRule($correction_rules);
            $question->setTestedKeys($testedKeys);

            $em->flush();

            foreach($answers as $answer) {
                if($answer[1] != null) {
                    $answerDb = $em->getRepository(EvaluationsAnswer::class)->find($answer[1]);
                    $answerDb->setAnswer($answer[0]);
                    $tmp = ($answer[2] == "true") ? true : false;
                    $answerDb->setIsTrue($tmp);
                    $em->flush();
                } else {
                    $answerDb = new EvaluationsAnswer();
                    $answerDb->setAnswer($answer[0]);
                    $answerDb->setIsTrue($answer[2]);
                    $answerDb->setEvalQuestionID($question);
                    $em->persist($answerDb);
                    $em->flush();
                }
            }

            return $this->json(true);
        }
        return $this->json(false);
    }

    public function deleteQuestion(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $questionId = $request->get('questionId');
            if(!is_numeric($questionId))
                return $this->json(false);

            $em = $this->getDoctrine()->getManager();

            $evalQ = $em->getRepository(EvaluationsQuestions::class)->find($questionId);

            if($evalQ != null) {
                foreach ($evalQ->getEvaluationsAnswers() as $answer) {
                    $em->remove($answer);
                }

                $em->flush();
                $em->remove($evalQ);
                $em->flush();

                return $this->json(true);
            }
        }

        return $this->json(false);
    }

    public function getAnswersOfAQuestion(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $evaluationQuestionId = $request->get('evaluationQuestionId');

            $question = $this->getDoctrine()->getRepository(EvaluationsQuestions::class)->find($evaluationQuestionId);

            $testedKeysStr = implode(',', $question->getTestedKeys());

            $dataRtn = [
                'id' => $question->getId(),
                'name' => $question->getQuestion(),
                'points' => $question->getPoints(),
                'rule' => $question->getCorrectionRule(),
                'tested_keys' => $testedKeysStr,
                'codeType' => ($question->getType() != null) ? $question->getType()->getId() : null,
                'code' => $question->getCode(),
                'answers' => []
            ];

            foreach($question->getEvaluationsAnswers() as $answer) {
                array_push($dataRtn['answers'], [$answer->getId(),$answer->getIsTrue(),$answer->getAnswer()]);
            }

            return $this->json(json_encode($dataRtn));
        }

        return $this->json(null);
    }

    public function addGroup(Request $request)
    {
        if($request->isXmlHttpRequest()) {

            try {
                $dateDeb = new DateTime($request->get('dateDeb'));
                $dateFin = new DateTime($request->get('dateFin'));
            } catch(\Exception $exception) {
                return $this->json($exception);
            }

            $groups = $request->get('groups');
            $evalId = $request->get('evalId');

            if(!is_numeric($evalId))
                return $this->json(false);

            //Init Var
            $evalGroup = new EvaluationsGroup();
            $atLeastOne=false;
            $resultPost = array();
            $tempGroup = array();

            $em = $this->getDoctrine()->getManager();
            $eval = $em->getRepository(Evaluations::class)->find($evalId);
            foreach($groups as $groupId) {
                $group = $em->getRepository(Group::class)->find($groupId);
                $flag = false;
                foreach($eval->getEvaluationsGroups() as $evaluationsGroup) {
                    foreach($evaluationsGroup->getGroupID() as $groupCmp) {
                        if($groupCmp->getId() === $group->getId()) {
                            $flag = true;
                        }
                    }
                }

                if(!$flag) {
                    $evalGroup->addGroupID($group);
                    $atLeastOne=true;
                    array_push($tempGroup, $group->getName());
                }
            }


            if($atLeastOne) {
                $evalGroup->setDateStart($dateDeb);
                $evalGroup->setDateEnd($dateFin);

                $evalGroup->addEvaluationsID($eval);

                $em->persist($evalGroup);
                $em->flush();

                array_push($resultPost, $evalGroup->getId());
                array_push($resultPost, $tempGroup);

                return $this->json(json_encode($resultPost));
            }
        }

        return $this->json(false);
    }

    public function deleteGroup(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $evalGroupId = $request->get('evalGroupId');

            if(!is_numeric($evalGroupId))
                return $this->json(false);

            $em = $this->getDoctrine()->getManager();

            $evalGroup = $em->getRepository(EvaluationsGroup::class)->find($evalGroupId);

            $em->remove($evalGroup);

            $em->flush();

            return $this->json(true);
        }
        return $this->json(false);
    }
}
