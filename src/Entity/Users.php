<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 */
class Users implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;


    /**
     * @ORM\Column(type="datetime")
     */
    private $register_date;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\SectionRef", mappedBy="userID")
     */
    private $sectionRefs;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\UsersGroup", mappedBy="userID")
     */
    private $usersGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Course", mappedBy="userID")
     */
    private $courses;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CourseRef", mappedBy="userID")
     */
    private $courseRefs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Evaluations", mappedBy="creatorID")
     */
    private $evaluations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationsDatas", mappedBy="userID")
     */
    private $evaluationsDatas;

    /**
     * @ORM\Column(type="json_array")
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Articles", mappedBy="creator")
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Diploma", mappedBy="user")
     */
    private $diplomas;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Diploma", inversedBy="users")
     */
    private $degree;

    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $newPasswordToken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationsNotes", mappedBy="user", orphanRemoval=true)
     */
    private $evaluationsNotes;

    public function getName() : string
    {
        return $this->lastname." ".$this->firstname;
    }

    public function __construct()
    {
        $this->sectionRefs = new ArrayCollection();
        $this->usersGroups = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->courseRefs = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->evaluationsDatas = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->diplomas = new ArrayCollection();
        $this->evaluationsNotes = new ArrayCollection();
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRegisterDate(): ?\DateTimeInterface
    {
        return $this->register_date;
    }

    public function setRegisterDate(\DateTimeInterface $register_date): self
    {
        $this->register_date = $register_date;

        return $this;
    }

    /**
     * @return Collection|SectionRef[]
     */
    public function getSectionRefs(): Collection
    {
        return $this->sectionRefs;
    }

    public function addSectionRef(SectionRef $sectionRef): self
    {
        if (!$this->sectionRefs->contains($sectionRef)) {
            $this->sectionRefs[] = $sectionRef;
            $sectionRef->addUserID($this);
        }

        return $this;
    }

    public function removeSectionRef(SectionRef $sectionRef): self
    {
        if ($this->sectionRefs->contains($sectionRef)) {
            $this->sectionRefs->removeElement($sectionRef);
            $sectionRef->removeUserID($this);
        }

        return $this;
    }

    /**
     * @return Collection|UsersGroup[]
     */
    public function getUsersGroups(): Collection
    {
        return $this->usersGroups;
    }

    public function addUsersGroup(UsersGroup $usersGroup): self
    {
        if (!$this->usersGroups->contains($usersGroup)) {
            $this->usersGroups[] = $usersGroup;
            $usersGroup->addUserID($this);
        }

        return $this;
    }

    public function removeUsersGroup(UsersGroup $usersGroup): self
    {
        if ($this->usersGroups->contains($usersGroup)) {
            $this->usersGroups->removeElement($usersGroup);
            $usersGroup->removeUserID($this);
        }

        return $this;
    }

    /**
     * @return Collection|Course[]
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses[] = $course;
            $course->setUserID($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->contains($course)) {
            $this->courses->removeElement($course);
            // set the owning side to null (unless already changed)
            if ($course->getUserID() === $this) {
                $course->setUserID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CourseRef[]
     */
    public function getCourseRefs(): Collection
    {
        return $this->courseRefs;
    }

    public function addCourseRef(CourseRef $courseRef): self
    {
        if (!$this->courseRefs->contains($courseRef)) {
            $this->courseRefs[] = $courseRef;
            $courseRef->addUserID($this);
        }

        return $this;
    }

    public function removeCourseRef(CourseRef $courseRef): self
    {
        if ($this->courseRefs->contains($courseRef)) {
            $this->courseRefs->removeElement($courseRef);
            $courseRef->removeUserID($this);
        }

        return $this;
    }

    /**
     * @return Collection|Evaluations[]
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    public function addEvaluation(Evaluations $evaluation): self
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations[] = $evaluation;
            $evaluation->setCreatorID($this);
        }

        return $this;
    }

    public function removeEvaluation(Evaluations $evaluation): self
    {
        if ($this->evaluations->contains($evaluation)) {
            $this->evaluations->removeElement($evaluation);
            // set the owning side to null (unless already changed)
            if ($evaluation->getCreatorID() === $this) {
                $evaluation->setCreatorID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EvaluationsDatas[]
     */
    public function getEvaluationsDatas(): Collection
    {
        return $this->evaluationsDatas;
    }

    public function addEvaluationsData(EvaluationsDatas $evaluationsData): self
    {
        if (!$this->evaluationsDatas->contains($evaluationsData)) {
            $this->evaluationsDatas[] = $evaluationsData;
            $evaluationsData->setUserID($this);
        }

        return $this;
    }

    public function removeEvaluationsData(EvaluationsDatas $evaluationsData): self
    {
        if ($this->evaluationsDatas->contains($evaluationsData)) {
            $this->evaluationsDatas->removeElement($evaluationsData);
            // set the owning side to null (unless already changed)
            if ($evaluationsData->getUserID() === $this) {
                $evaluationsData->setUserID(null);
            }
        }

        return $this;
    }

    /**
     * Retourne les rôles de l'user
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // Afin d'être sûr qu'un user a toujours au moins 1 rôle
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Retour le salt qui a servi à coder le mot de passe
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
        // we're using bcrypt in security.yml to encode the password, so
        // the salt value is built-in and you don't have to generate one

        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // Nous n'avons pas besoin de cette methode car nous n'utilions pas de plainPassword
        // Mais elle est obligatoire car comprise dans l'interface UserInterface
        // $this->plainPassword = null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|Articles[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Articles $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setCreator($this);
        }

        return $this;
    }

    public function removeArticle(Articles $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getCreator() === $this) {
                $article->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Diploma[]
     */
    public function getDiplomas(): Collection
    {
        return $this->diplomas;
    }

    public function addDiploma(Diploma $diploma): self
    {
        if (!$this->diplomas->contains($diploma)) {
            $this->diplomas[] = $diploma;
            $diploma->setUser($this);
        }

        return $this;
    }

    public function removeDiploma(Diploma $diploma): self
    {
        if ($this->diplomas->contains($diploma)) {
            $this->diplomas->removeElement($diploma);
            // set the owning side to null (unless already changed)
            if ($diploma->getUser() === $this) {
                $diploma->setUser(null);
            }
        }

        return $this;
    }

    public function getDegree(): ?diploma
    {
        return $this->degree;
    }

    public function setDegree(?diploma $degree): self
    {
        $this->degree = $degree;

        return $this;
    }

    public function getNewPasswordToken(): ?string
    {
        return $this->newPasswordToken;
    }

    public function setNewPasswordToken(string $newPasswordToken): self
    {
        $this->newPasswordToken = $newPasswordToken;

        return $this;
    }

    /**
     * @return Collection|EvaluationsNotes[]
     */
    public function getEvaluationsNotes(): Collection
    {
        return $this->evaluationsNotes;
    }

    public function addEvaluationsNote(EvaluationsNotes $evaluationsNote): self
    {
        if (!$this->evaluationsNotes->contains($evaluationsNote)) {
            $this->evaluationsNotes[] = $evaluationsNote;
            $evaluationsNote->setUser($this);
        }

        return $this;
    }

    public function removeEvaluationsNote(EvaluationsNotes $evaluationsNote): self
    {
        if ($this->evaluationsNotes->contains($evaluationsNote)) {
            $this->evaluationsNotes->removeElement($evaluationsNote);
            // set the owning side to null (unless already changed)
            if ($evaluationsNote->getUser() === $this) {
                $evaluationsNote->setUser(null);
            }
        }

        return $this;
    }

    /**
     * CUSTOM
     */

    public function getAverage() : ?float
    {
        $average = null;

        foreach($this->getEvaluationsNotes() as $evaluationsNote) {
            $average+=$evaluationsNote->getNote()*20/$evaluationsNote->getEvaluation()->getTotalNote();
        }

        if($this->getEvaluationsNotes()->count() != 0)
            $average=$average/$this->getEvaluationsNotes()->count();
        else
            $average=null;

        return $average;
    }

    public function getMissedTests() : array
    {
        $missedTests = [];

        foreach($this->getUsersGroups() as $usersGroup) {
            foreach($usersGroup->getGroupID() as $group) {
                foreach($group->getEvaluationsGroups() as $evaluationsGroup) {
                    if(strtotime($evaluationsGroup->getDateEnd()->format('Y-m-d H:i:s')) < time()) {
                        foreach ($evaluationsGroup->getEvaluationsID() as $evaluation) {
                            $isDone=false;
                            foreach($this->getEvaluationsDatas() as $evaluationsData) {
                                if($evaluation->getId() == $evaluationsData->getEvaluationID()->getId()) {
                                    $isDone=true;
                                }
                            }
                            if(!$isDone) {
                                array_push($missedTests, $evaluation);
                            }
                        }
                    }
                }
            }
        }

        return $missedTests;
    }

    public function getTestsDone() : array
    {
        $missedTests = [];

        foreach($this->getUsersGroups() as $usersGroup) {
            foreach($usersGroup->getGroupID() as $group) {
                foreach($group->getEvaluationsGroups() as $evaluationsGroup) {
                    if(strtotime($evaluationsGroup->getDateEnd()->format('Y-m-d H:i:s')) < time()) {
                        foreach ($evaluationsGroup->getEvaluationsID() as $evaluation) {
                            $isDone=false;
                            foreach($this->getEvaluationsDatas() as $evaluationsData) {
                                if($evaluation->getId() == $evaluationsData->getEvaluationID()->getId()) {
                                    $isDone=true;
                                }
                            }
                            if($isDone) {
                                array_push($missedTests, $evaluation);
                            }
                        }
                    }
                }
            }
        }

        return $missedTests;
    }

}
