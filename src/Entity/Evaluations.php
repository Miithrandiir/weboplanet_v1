<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluationsRepository")
 */
class Evaluations
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
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="evaluations")
     */
    private $creatorID;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEval;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Chapter", inversedBy="evaluations")
     */
    private $chapterID;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\EvaluationsGroup", mappedBy="evaluationsID")
     */
    private $evaluationsGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationsQuestions", mappedBy="evaluationsID")
     */
    private $evaluationsQuestions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationsDatas", mappedBy="evaluationID")
     */
    private $evaluationsDatas;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationsNotes", mappedBy="evaluation", orphanRemoval=true)
     */
    private $evaluationsNotes;

    public function __construct()
    {
        $this->evaluationsGroups = new ArrayCollection();
        $this->evaluationsQuestions = new ArrayCollection();
        $this->evaluationsDatas = new ArrayCollection();
        $this->evaluationsNotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatorID(): ?Users
    {
        return $this->creatorID;
    }

    public function setCreatorID(?Users $creatorID): self
    {
        $this->creatorID = $creatorID;

        return $this;
    }

    public function getIsEval(): ?bool
    {
        return $this->isEval;
    }

    public function setIsEval(bool $isEval): self
    {
        $this->isEval = $isEval;

        return $this;
    }

    public function getChapterID(): ?Chapter
    {
        return $this->chapterID;
    }

    public function setChapterID(?Chapter $chapterID): self
    {
        $this->chapterID = $chapterID;

        return $this;
    }

    /**
     * @return Collection|EvaluationsGroup[]
     */
    public function getEvaluationsGroups(): Collection
    {
        return $this->evaluationsGroups;
    }

    public function addEvaluationsGroup(EvaluationsGroup $evaluationsGroup): self
    {
        if (!$this->evaluationsGroups->contains($evaluationsGroup)) {
            $this->evaluationsGroups[] = $evaluationsGroup;
            $evaluationsGroup->addEvaluationsID($this);
        }

        return $this;
    }

    public function removeEvaluationsGroup(EvaluationsGroup $evaluationsGroup): self
    {
        if ($this->evaluationsGroups->contains($evaluationsGroup)) {
            $this->evaluationsGroups->removeElement($evaluationsGroup);
            $evaluationsGroup->removeEvaluationsID($this);
        }

        return $this;
    }

    /**
     * @return Collection|EvaluationsQuestions[]
     */
    public function getEvaluationsQuestions(): Collection
    {
        return $this->evaluationsQuestions;
    }

    public function addEvaluationsQuestion(EvaluationsQuestions $evaluationsQuestion): self
    {
        if (!$this->evaluationsQuestions->contains($evaluationsQuestion)) {
            $this->evaluationsQuestions[] = $evaluationsQuestion;
            $evaluationsQuestion->setEvaluationsID($this);
        }

        return $this;
    }

    public function removeEvaluationsQuestion(EvaluationsQuestions $evaluationsQuestion): self
    {
        if ($this->evaluationsQuestions->contains($evaluationsQuestion)) {
            $this->evaluationsQuestions->removeElement($evaluationsQuestion);
            // set the owning side to null (unless already changed)
            if ($evaluationsQuestion->getEvaluationsID() === $this) {
                $evaluationsQuestion->setEvaluationsID(null);
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
            $evaluationsData->setEvaluationID($this);
        }

        return $this;
    }

    public function removeEvaluationsData(EvaluationsDatas $evaluationsData): self
    {
        if ($this->evaluationsDatas->contains($evaluationsData)) {
            $this->evaluationsDatas->removeElement($evaluationsData);
            // set the owning side to null (unless already changed)
            if ($evaluationsData->getEvaluationID() === $this) {
                $evaluationsData->setEvaluationID(null);
            }
        }

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
            $evaluationsNote->setEvaluation($this);
        }

        return $this;
    }

    public function removeEvaluationsNote(EvaluationsNotes $evaluationsNote): self
    {
        if ($this->evaluationsNotes->contains($evaluationsNote)) {
            $this->evaluationsNotes->removeElement($evaluationsNote);
            // set the owning side to null (unless already changed)
            if ($evaluationsNote->getEvaluation() === $this) {
                $evaluationsNote->setEvaluation(null);
            }
        }

        return $this;
    }

    public function getTotalNote() : float
    {
        $noteTotal=0;

        foreach($this->getEvaluationsQuestions() as $question) {
            $noteTotal+=$question->getPoints();
        }

        return $noteTotal;
    }
}
