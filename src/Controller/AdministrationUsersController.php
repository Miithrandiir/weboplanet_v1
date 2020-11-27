<?php

namespace App\Controller;

use App\Entity\Diploma;
use App\Entity\Group;
use App\Entity\GroupSection;
use App\Entity\Section;
use App\Entity\Users;
use App\Entity\UsersGroup;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class AdministrationUsersController extends AbstractController
{
    public function view()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(Users::class)->findAll();
        return $this->render('administration/users/view.html.twig', [
            'users' => $users
        ]);
    }

    public function edit(Users $users, Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('firstname', TextType::class, ['attr' => ['value' => $users->getFirstname()], 'label' => 'Prénom'])
            ->add('lastname', TextType::class, ['attr' => ['value' => $users->getLastname()], 'label' => 'Nom'])
            ->add('email', EmailType::class, ['attr' => ['value' => $users->getEmail()], 'label' => 'E-Mail'])
            //@TODO gérer le fait que le diplome peut être nul !
            ->add('diploma', EntityType::class, ['class' => Diploma::class,'attr' => ['label' => 'Diplôme préparé', 'required' => false], 'choice_label' => 'name', 'required' => false])
            ->add('username', TextType::class, ['attr' => ['value' => $users->getUsername()], 'label' => 'Nom d\'utilisateur'])
            ->add('roles', ChoiceType::class, ['choices' => [
                'Administrateur' => 'ROLE_ADMIN',
                'Enseignant' => 'ROLE_ENSG',
                'Bypass (Contourner la fermeture du site)' => 'ROLE_BYPASS',
                'Élève' => 'ROLE_USER'
            ],
                'preferred_choices' => $users->getRoles(),
                'attr' => ['class' => 'select2'],
                'multiple' =>true,
                'label' => 'Rôles'
            ])
            ->getForm();

        $groupForm = $this->createFormBuilder()
            ->add('groups', EntityType::class, ['class' => Group::class, 'choice_label' => 'name', 'label' => 'Groupes', 'multiple' => true])
            ->getForm();

        $form->setData(['roles'=>$users->getRoles(),'diploma'=>$users->getDegree()]);

        dump($request);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $continue=true;
            $em = $this->getDoctrine()->getManager();

            //If the username is different from the original username it's reveal that's we have change the username, we need to check if the username is already used
            if($data['username'] != $users->getUsername()) {
                $checkUsername = $em->getRepository(Users::class)->findBy(['username' => $data['username']]);

                if($checkUsername != null) {
                    $continue=false;
                }
            }


            if($continue) {

                if($data['diploma'] != null) {
                    $degree = $em->getRepository(Diploma::class)->find($data['diploma']);
                } else {
                    $degree = null;
                }


                    $users->setFirstname($data['firstname']);
                    $users->setLastname($data['lastname']);
                    $users->setUsername($data['username']);
                    $users->setEmail($data['email']);
                    $users->setRoles($data['roles']);
                    $users->setDegree($degree);
                    $em->persist($users);
                    $em->flush();
                    return $this->redirectToRoute('administration_user_edit', ['id' => $users->getId()]);
            } else {
                $form->addError(new FormError("Le nom d'utilisateur est déjà utilisé."));
            }
        }

        return $this->render('administration/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $users,
            'groupForm' => $groupForm->createView()
        ]);
    }

    public function sendNewPassword(Request $request, \Swift_Mailer $mailer)
    {
        if($request->isXmlHttpRequest()) {
            $userId = $request->get('userId');
            $em = $this->getDoctrine()->getManager();
            if(!is_numeric($userId)) {
                return $this->json(false);
            }

            $user = $em->getRepository(Users::class)->find($userId);

            $newTokenPassword = md5(uniqid(rand(), true));

            $user->setNewPasswordToken($newTokenPassword);

            $em->flush();

            $message = (new \Swift_Message('Weboplanet - Changement de mot de passe'))
                ->setFrom($this->getParameter('owner_email'))
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'email/newPassword.html.twig',
                        ['token' => $newTokenPassword]
                    ),
                    'text/html'
                );
            $mailer->send($message);
            return $this->json(true);
        }

        return $this->json(false);
    }

    public function addToGroup(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $userId = $request->get('userId');
            $groupId = $request->get('groupId');
            $em = $this->getDoctrine()->getManager();
            if(!is_numeric($userId)) {
                return $this->json(false);
            }

            //The goal is to display in HTML the group
            $groupAdd = array();

            foreach($groupId as $groupFor) {

                if(!is_numeric($groupFor)) {
                    return $this->json(false);
                }

                $group = $em->getRepository(Group::class)->find($groupFor);

                //We look up if the sql request has send to us something
                if ($group != null) {
                    $user = $em->getRepository(Users::class)->find($userId);
                    //same thing
                    if ($user != null) {

                        if($group->getUsersGroups()->count() == 0) {
                            $userGroup = new UsersGroup();

                            $userGroup->addGroupID($group);
                            $userGroup->addUserID($user);

                            $em->persist($userGroup);
                            $em->flush();
                            array_push($groupAdd, [$group->getId(), $group->getName()]);
                        } else {
                            $usersGroup = $group->getUsersGroups()[0];

                            $flag = false;
                            foreach ($usersGroup->getUserID() as $userSearch) {
                                if ($userSearch->getId() == $user->getId())
                                    $flag = true;
                            }

                            if (!$flag) {
                                $usersGroup->addUserID($user);
                                array_push($groupAdd, [$group->getId(), $group->getName()]);
                                $em->flush();
                            }

                        }
                    } else {
                        return $this->json(false);
                    }
                } else {
                    return $this->json(false);
                }
            }

            return $this->json(json_encode($groupAdd));
        }

        return $this->json(false);
    }

    public function removeFromGroup(Request $request)
    {
        if($request->isXmlHttpRequest()) {
            $userId = $request->get('userId');
            $groupId = $request->get('groupId');
            if(!is_numeric($userId) || !is_numeric($groupId)) {
                return $this->json(false);
            }
            $em = $this->getDoctrine()->getManager();

            $user = $em->getRepository(Users::class)->find($userId);
            $group = $em->getRepository(Group::class)->find($groupId);

            if($group != null && $user != null) {
                foreach ($group->getUsersGroups() as $usersGroup) {
                    $usersGroup->removeUserID($user);
                }
            }

            $em->flush();

            return $this->json(true);
        }

        return $this->json(false);
    }

    public function import(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('file', FileType::class, ['label' => 'Fichier (Format csv uniquement)'])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $file = file($form['file']->getData()->getPathname());
            $lineInError = [];
            $pseudo = 0;
            //Permet d'éviter la surchage de la BDD
            $isClassExist = [];
            $isGroupExist = [];
            $users = [];            //
            $usersGroup = [];       //  Les clé de tableau sont les mêmes pour les trois variables, ils sont liés
            $usersSection = [];     //
            $usersDegree = [];      //
            $knowDegree= [];
            $groups = [];
            $em = $this->getDoctrine()->getManager();

            /**
             * VERIFICATION DE L'ENTETE
             */
            //delete PHP_EOL
            $value = trim($file[0]);
            //Know if the file is good
            $separated = explode(';', $value);
            /*
             * [0] : E / D
             * [1] : LOGIN
             * [2] : MDP
             * [3] : Nom
             * [4] : Prénom
             * [5] : Classe
             * [6] : Groupe
             * [7] : E /D
             */
            $login = strtolower($separated[1]);
            $mdp = strtolower($separated[2]);
            $nom = strtolower($separated[3]);
            $prenom = strtolower(str_replace('é', 'e', $separated[4])); //prénom to prenom
            $classe = strtolower($separated[5]);
            $groupe = strtolower($separated[6]);
            //On regarde si l'entête du fichier est bien formée
            if ($login != "login" || $mdp != "mdp" || $nom != "nom" || $prenom != "prenom" || $classe != "classe" || $groupe != "groupe") {
                //Error !
                $form->addError(new FormError("Le fichier CSV est mal formé."));
            }
            /**
             * FIN
             */

            /**
             * Sauvegarde des classes & verification de l'existence des groupes
             */
            $error=false;
            foreach($file as $key => $value) {

                if($key == 0) //Entête !
                    continue;

                $separated = explode(';', $value);

                $login = strtolower($separated[1]);
                $mdp = strtolower($separated[2]);
                $nom = strtolower($separated[3]);
                $prenom = strtolower(str_replace('é', 'e', $separated[4])); //prénom to prenom
                $classe = strtolower($separated[5]);
                $groupe = strtolower($separated[6]);

                if(($id = array_key_exists($classe, $knowDegree)) !== FALSE) {
                    array_push($usersDegree,$knowDegree[$classe]);
                }

                if(($id = array_key_exists($classe,$isClassExist)) === false) {
                    $classeObj = $em->getRepository(Section::class)->findOneBy(['name' => $classe]);
                    if($classeObj != null) {
                        $isClassExist[$classe] = true;
                        array_push($usersDegree, $classeObj->getDiploma()->getId());
                        $knowDegree[$classe] = $classeObj->getDiploma()->getId();
                    } else {
                        $error=true;
                        $form->addError(new FormError("La classe ${classe} n'existe pas, vous devez la créer !"));
                        $isClassExist[$classe] = false;
                        break;
                    }
                }


                $query = $em->getRepository(Group::class)->findOneBy(['name' => $groupe]);

                if($query != null) {
                    //$isGroupExist[$groupe] = $classe;
                    if(array_key_exists($groupe, $isGroupExist) && is_array($isGroupExist[$groupe])) {
                        if(array_search(trim($classe), $isGroupExist[$groupe]) === FALSE) {
                            array_push($isGroupExist[$groupe], $classe);
                        }
                    } else {
                        $isGroupExist[$groupe] = [$classe];
                    }
                } else {
                    //creation du groupe
                    $newGroup = new Group();
                    $newGroup->setName($groupe);
                    $em->persist($newGroup);
                    $isGroupExist[$groupe] = $classe;
                }


                //on prépare l'insertion des utilisateurs
                $user = new Users();
                $user->setUsername($login);
                $user->setEmail("");
                $user->setPassword("");
                $user->setRoles(['ROLE_USER']);
                $user->setRegisterDate(new DateTime());
                $user->setFirstname($prenom);
                $user->setLastname($nom);
                array_push($users, $user);
                array_push($usersGroup, $groupe);

                $em->flush();
                $em->clear();
            }

            /**
             *
             */

            /**
             * Verification de la liaison groupe <-> classe
             */

            foreach($isGroupExist as $key => $value)
            {
                foreach($value as $item) {
                    //query
                    $query = $em->getRepository(Group::class)->findOneBy(['name' => $key]);
                    //Pas besoin de check si le groupe existe il est déjà créé
                    $classe = $em->getRepository(Section::class)->findOneBy(['name' => $item]);
                    if ($query->getGroupSections()->count() === 0) {
                        //Il n'est relié à rien !
                        $groupSection = new GroupSection();
                        $groupSection->addGroupID($query);
                        $groupSection->addSectionID($classe);
                        $em->persist($groupSection);
                    } else {
                        $flag = false;
                        foreach ($query->getGroupSections() as $groupSection) {
                            foreach ($groupSection->getSectionID() as $section) {
                                if ($section->getName() == $classe->getName()) {
                                    $flag = true;
                                }
                            }
                        }

                        if (!$flag) {
                            $groupSection = new GroupSection();
                            $groupSection->addGroupID($query);
                            $groupSection->addSectionID($classe);
                            $em->persist($groupSection);
                        }
                    }

                    if ($query->getUsersGroups()->count() === 0) {
                        $usersGroupQ = new UsersGroup();
                        $usersGroupQ->addGroupID($query);

                        $em->persist($usersGroupQ);
                    }

                    $em->flush();
                    $em->clear();
                }
            }


            /**
             *
             */

            /**
             * Creation de l'utilisateur
             */
            foreach($users as $key => $user) {
                $degree = $em->getRepository(Diploma::class)->find($usersDegree[$key]);
                $user->setDegree($degree);
                $em->persist($user);

                //insertion dans les bon groupe
                $groupeName = $usersGroup[$key];
                $group = $em->getRepository(Group::class)->findOneBy(['name' => $groupeName]);

                if($group !== null) {
                    $group->getUsersGroups()[0]->addUserID($user);
                } else {
                    $form->addError(new FormError("Le groupe n' pas pu être trouvé pour l'utilisateur ".$user->getUsername() .", groupe : ${groupeName}"));
                }

                $em->flush();
                $em->clear();
            }

            /**
             *
             */

        }
        return $this->render('administration/users/import.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function create(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createFormBuilder()
            ->add('lastname', TextType::class, ['label'=>'Nom de famille', 'required' => true])
            ->add('firstname', TextType::class, ['label'=>'Prénom', 'required' => true])
            ->add('email', EmailType::class, ['label' => 'Adresse E-Mail', 'required' => false])
            ->add('roles', ChoiceType::class, ['choices' => [
                'Administrateur' => 'ROLE_ADMIN',
                'Enseignant' => 'ROLE_ENSG',
                'Bypass (Contourner la fermeture du site)' => 'ROLE_BYPASS',
                'Élève' => 'ROLE_USER'
            ],
                'attr' => ['class' => 'select2'],
                'multiple' =>true,
                'label' => 'Rôles'
            ])
            ->add('diploma', EntityType::class, ['class' => Diploma::class, 'label' => 'Diplôme préparé', 'choice_label' => 'name'])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();

            $lastname = $data['lastname'];
            $firstname = $data['firstname'];
            $email = $data['email'];
            $roles = $data['roles'];
            $diploma = $data['diploma'];

            $user = new Users();

            $username = $lastname.explode($firstname,'')[0];

            $search = $em->getRepository(Users::class)->findOneBy(['username' => $username]);

            if($search != null) {
                $flag=false;
                $i=1;
                while($flag===false) {
                    $username=$username.$i;
                    $i++;
                    $search = $em->getRepository(Users::class)->findOneBy(['username' => $username]);
                    if($search ==null) {
                        $flag = true;
                    }
                }
            }

            $newTokenPassword = md5(uniqid(rand(), true));
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setUsername($username);
            $user->setNewPasswordToken($newTokenPassword);
            $user->setDegree($diploma);
            $user->setPassword("");
            $user->setRegisterDate(new DateTime());

            $em->persist($user);
            $em->flush();

            $message = (new \Swift_Message('[ULCO] Weboplanet - Création d\'un compte'))
                ->setFrom($this->getParameter('owner_email'))
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'email/create.user.html.twig',
                        [
                            'lastname' => $lastname,
                            'firstname' => $firstname,
                            'username' => $username,
                            'email' => $email,
                            'passwordURL' => $newTokenPassword
                        ]
                    ),
                    'text/html'
                );
            $mailer->send($message);

            return $this->redirectToRoute('administration_users_view');
        }

        return $this->render('administration/users/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
