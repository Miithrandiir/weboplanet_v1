<?php

namespace App\Controller;

use App\Entity\Evaluations;
use App\Entity\EvaluationsNotes;
use App\Entity\EvaluationsQuestions;
use App\Utils\Compile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdministrationsResultatController extends AbstractController
{
    public function view(Evaluations $evaluation)
    {
        /**
         * [
         *   [n] => ['note'] Note,
         *          ['occ'] Nombre d'occurence
         * ]
         */
        $note = []; //Renseigne toutes les notes
        $noteSumTotal = 0;//Permet de calculer la moyenne
        $total = 0; //Total des points de l'évaluation
        $noteMoyenne = 0; //Variable qui stocke la moyenne

        foreach($evaluation->getEvaluationsNotes() as $evaluationsNote) {
            $flag = false;
            foreach($note as $search) {
                if($search['note'] == $evaluationsNote->getNote()) {
                    $flag = true;
                    break;
                }
            }

            if(!$flag) {
                array_push($note, ['note' => $evaluationsNote->getNote(), 'occ' => 1]);
            } else {
                foreach($note as $key => $value) {
                    if($note[$key]['note'] == $evaluationsNote->getNote()) {
                        $note[$key]['occ']+=1;
                    }
                }
            }
        }

        foreach($evaluation->getEvaluationsQuestions() as $question) {
            $total += $question->getPoints();
        }

        usort($note, function ($a, $b) {
            if($a > $b) {
                return 1;
            } elseif ($a < $b) {
                return -1;
            } else {
                return 0;
            }
        });

        if(array_sum(array_column($note, 'occ')) > 0) {
            $noteMoyenne = array_sum(array_column($note, 'note')) / array_sum(array_column($note, 'occ'));
        } else {
            $noteMoyenne = "N.A";
        }
        //On divise par le nombre de note !

        return $this->render('administration/resultats/view.html.twig', [
            'notes' => $note,
            'evaluations' => $evaluation,
            'total' => $total,
            'noteMoyenne' => $noteMoyenne
        ]);
    }

    public function checkCoherence(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $id = $request->get('evalId');
            if(!is_numeric($id))
                return $this->json([false,null]);
            $em = $this->getDoctrine()->getManager();

            $evaluation = $em->getRepository(Evaluations::class)->find($id);

            $response = [];

            foreach($evaluation->getEvaluationsGroups() as $evaluationsGroup) {
                //Interro pas corrigé
                if((strtotime($evaluationsGroup->getDateEnd()->format('Y-m-d H:i:s')) < time())) {
                    foreach ($evaluationsGroup->getGroupID() as $group) {
                        foreach($group->getUsersGroups() as $usersGroup) {
                            foreach ($usersGroup->getUserID() as $user) {
                                $dbNote = $em->getRepository(EvaluationsNotes::class)->findOneBy(['user' => $user, 'evaluation' => $evaluation]);
                                if($dbNote != null && !$dbNote->getSkipCoherence()) {
                                    $finalNote=0;
                                    foreach ($evaluation->getEvaluationsQuestions() as $evaluationQuestion) {
                                        if ($evaluationQuestion->getType() != null) {
                                            //Get the command to compile
                                            foreach ($user->getEvaluationsDatas() as $datas) {
                                                if ($datas->getEvaluationID()->getId() == $evaluation->getId()) {
                                                    $note = 0;
                                                    $userResponse = Compile::compile($evaluationQuestion->getType(), $datas->getEvaluationsQ()->getCode(), $evaluationQuestion->getTestedKeys(), $datas->getCodeResponse());
                                                    $evalResponse = Compile::compile($evaluationQuestion->getType(), $evaluationQuestion->getEvaluationsAnswers()[0]->getAnswer(), $evaluationQuestion->getTestedKeys());

                                                    if (count(array_diff($userResponse, $evalResponse)) == 0) {
                                                        //success !
                                                        $note = $evaluationQuestion->getPoints();
                                                        //Set data to true
                                                        if($datas->getIsCorrect() != true) {
                                                            array_push($response, [$user->getName(), $evaluationQuestion->getQuestion(), 'N\'est pas compté comme bon']);
                                                        }
                                                    } else {
                                                        //Error !
                                                        if($datas->getIsCorrect() != false) {
                                                            array_push($response, [$user->getName(), $evaluationQuestion->getQuestion(), 'Est compté comme bon']);
                                                        }
                                                    }

                                                    $finalNote += $note;
                                                }
                                            }
                                        } else {
                                            //type QCM QCU
                                            $note = 0;
                                            $nbRight = 0;//Nombre de réponse bonne donné par l'utilisateur
                                            $nbCheckRight = 0;
                                            //On check déjà les bonnes réponses
                                            foreach ($evaluationQuestion->getEvaluationsAnswers() as $answer) {
                                                if ($answer->getIsTrue()) {
                                                    $nbCheckRight++;
                                                }
                                            }

                                            foreach ($user->getEvaluationsDatas() as $datas) {
                                                if ($datas->getEvaluationID()->getId() == $evaluation->getId()) {
                                                    foreach ($datas->getEvaluationsA() as $userAnswer) {
                                                        if ($userAnswer->getEvalQuestionID()->getId() == $evaluationQuestion->getId() && $userAnswer->getIsTrue()) {
                                                            $nbRight++;
                                                            if($datas->getIsCorrect() != true) {
                                                                array_push($response, [$user->getName(), $evaluationQuestion->getQuestion(), 'Est compté comme bon']);
                                                            }
                                                        } else {
                                                            if($datas->getIsCorrect() != false) {
                                                                array_push($response, [$user->getName(), $evaluationQuestion->getQuestion(), 'Est compté comme bon']);
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            if ($evaluationQuestion->getCorrectionRule() == EvaluationsQuestions::RULES_STRICT) {
                                                if ($nbRight != $nbCheckRight) {
                                                    $note = 0;
                                                } else {
                                                    $note += $evaluationQuestion->getPoints();
                                                }
                                            } elseif ($evaluationQuestion->getCorrectionRule() == EvaluationsQuestions::RULES_SOFT) {
                                                //On va éviter une division par 0
                                                if ($nbCheckRight > 0) {
                                                    $note += ($evaluationQuestion->getPoints() / $nbCheckRight) * $nbRight;
                                                }
                                            }

                                            $finalNote += $note;
                                        }

                                    }


                                    if($finalNote != $dbNote->getNote()) {
                                        array_push($response, [$user->getName(), 'note', "Note calculée :".$finalNote."\n Note stockée: ".$dbNote->getNote()]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $json = json_encode([true,$response]);
            return $this->json($json);
        }

        return $this->json([false, null]);
    }
}
