<?php

namespace App\Command;

use App\Entity\Evaluations;
use App\Entity\EvaluationsGroup;
use App\Entity\EvaluationsNotes;
use App\Entity\EvaluationsQuestions;
use App\Entity\EvaluationsTypes;
use App\Entity\Users;
use App\Utils\Compile;
use phpDocumentor\Reflection\Element;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CronCommand extends Command
{
    protected static $defaultName = 'app:cron';
    protected $doctrine;
    protected $swiftMailer;
    protected $container;

    public function __construct(RegistryInterface $doctrine, \Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine = $doctrine;
        $this->swiftMailer = $mailer;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Permet de lancer la tâche cron')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->doctrine->getManager();
        $users = $em->getRepository(Users::class)->findAll();
        $evaluations = $em->getRepository(Evaluations::class)->findAll();

        /**
         * ENVOIE DES EMAILS POUR LES EVALUATIONS
         */

        foreach($users as $user) {

            $userGroupId=[];
            $evalWarning=array(); //Tableau qui contient toutes les evaluations qui auront lieux dans 5jours, 3jours et 1jours, pour pouvoir l'envoyer dans un email

            //On récupère l'id de tous ses groupes
            foreach($user->getUsersGroups() as $usersGroup) {
                foreach($usersGroup->getGroupID() as $group) {
                    array_push($userGroupId, $group->getId());
                }
            }

            foreach($user->getEvaluations() as $evaluation) {
                foreach($evaluation->getEvaluationsGroups() as $evaluationsGroup) {
                    foreach($evaluationsGroup->getGroupID() as $groupEval) {
                        if(array_search($groupEval->getId(), $userGroupId) !== false) {

                            /*
                             * Normalement, si tout ce passe à merveille, l'étudiant ne peut être inscrit deux fois dans une évaluation,
                             * c'est à dire qu'il faudra faire attention à ce qu'il ne soit pas inscrit dans deux groupes qui sont eux mêmes inscrit dans la mêmes évaluations !
                             */

                            $getLastTime = (int) date('d', strtotime($evaluationsGroup->getDateEnd()->format('Y-m-d H:i:s')) - time());
                            if($getLastTime == 10 || $getLastTime == 3 || $getLastTime == 1) {
                                array_push($evalWarning, [strtotime($evaluationsGroup->getDateEnd()->format('Y-m-d H:i:s')), $evaluation]);
                            }
                        }
                    }
                }

            }

            if($evalWarning != []) {
                //@TODO enlevé
                //$this->sendEmailEval($evalWarning, $user);
            }
        }


        /**
         * CORRECTION DES EVALUATIONS
         */
        foreach($evaluations as $evaluation) {
            foreach($evaluation->getEvaluationsGroups() as $evaluationsGroup) {
                //Interro pas corrigé
                if(!$evaluationsGroup->getHasBeenCheck() && (strtotime($evaluationsGroup->getDateEnd()->format('Y-m-d H:i:s')) < time())) {
                    foreach ($evaluationsGroup->getGroupID() as $group) {
                        foreach($group->getUsersGroups() as $usersGroup) {
                            foreach ($usersGroup->getUserID() as $user) {
                                $finalNote=0;
                                foreach($evaluation->getEvaluationsQuestions() as $evaluationQuestion) {
                                    if($evaluationQuestion->getType() != null) {
                                        //Get the command to compile
                                        foreach($user->getEvaluationsDatas() as $datas) {
                                            if($datas->getEvaluationID()->getId() == $evaluation->getId()) {
                                                $note = 0;
                                                 $userResponse = Compile::compile($evaluationQuestion->getType(), $datas->getEvaluationsQ()->getCode(), $evaluationQuestion->getTestedKeys(), $datas->getCodeResponse());
                                                 $evalResponse = Compile::compile($evaluationQuestion->getType(), $evaluationQuestion->getEvaluationsAnswers()[0]->getAnswer(), $evaluationQuestion->getTestedKeys());

                                                 if(count(array_diff($userResponse,$evalResponse)) == 0) {
                                                     //success !
                                                     $note = $evaluationQuestion->getPoints();
                                                     //Set data to true
                                                     $datas->setIsCorrect(true);
                                                     $em->persist($datas);
                                                     $em->flush();
                                                 } else {
                                                     //Error !
                                                     $datas->setIsCorrect(false);
                                                     $em->persist($datas);
                                                     $em->flush();
                                                 }

                                                 $finalNote+= $note;
                                            }
                                        }
                                    }
                                    else {
                                        //type QCM QCU
                                        $note=0;
                                        $nbRight=0;//Nombre de réponse bonne donné par l'utilisateur
                                        $nbCheckRight=0;
                                        //On check déjà les bonnes réponses
                                        foreach ($evaluationQuestion->getEvaluationsAnswers() as $answer) {
                                            if($answer->getIsTrue()) {
                                                $nbCheckRight++;
                                            }
                                        }

                                        foreach($user->getEvaluationsDatas() as $datas) {
                                            if($datas->getEvaluationID()->getId() == $evaluation->getId()) {
                                                foreach ($datas->getEvaluationsA() as $userAnswer) {
                                                    if($userAnswer->getEvalQuestionID()->getId() == $evaluationQuestion->getId() && $userAnswer->getIsTrue()) {
                                                        $nbRight++;
                                                        $datas->setIsCorrect(true);
                                                        $em->persist($datas);
                                                        $em->flush();
                                                    }
                                                }
                                            }
                                        }

                                        if($evaluationQuestion->getCorrectionRule() == EvaluationsQuestions::RULES_STRICT) {
                                            if($nbRight != $nbCheckRight) {
                                                $note=0;
                                            } else {
                                                $note += $evaluationQuestion->getPoints();
                                            }
                                        } elseif($evaluationQuestion->getCorrectionRule() == EvaluationsQuestions::RULES_SOFT) {
                                            //On va éviter une division par 0
                                            if($nbCheckRight > 0) {
                                                $note+=($evaluationQuestion->getPoints()/$nbCheckRight)*$nbRight;
                                            }
                                        }

                                        $finalNote+=$note;
                                    }

                                }

                                $evalNote = new EvaluationsNotes();
                                $evalNote->setUser($user);
                                $evalNote->setEvaluation($evaluation);
                                $evalNote->setNote($finalNote);
                                $evalNote->setSkipCoherence(false);
                                $em->persist($evalNote);
                                $em->flush();
                            }
                        }
                    }
                    $evaluationsGroup->setHasBeenCheck(true);
                    $em->persist($evaluationsGroup);
                    $em->flush();
                }
            }
        }
        $io->success('Tâche cron réalisée avec succès !');
    }

    public function sendEmailEval(array $evaluations, Users $user)
    {

        $message = (new \Swift_Message('[WEBOPLANET] Rappel d\'évaluation'))
            //@TODO change
            ->setFrom('contact@ferenost.fr')
            ->setTo($user->getEmail())
            ->setBody(
                $this->container->get('twig')->render('email/rappel.eval.html.twig',
                 [
                     'evaluations' => $evaluations
                 ]),
                'text/html'
            );

        $this->swiftMailer->send($message);
    }
}
