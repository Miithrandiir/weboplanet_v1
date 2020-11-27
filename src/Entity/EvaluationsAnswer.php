<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluationsAnswerRepository")
 */
class EvaluationsAnswer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EvaluationsQuestions", inversedBy="evaluationsAnswers")
     */
    private $evalQuestionID;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isTrue;

    /**
     * @ORM\Column(type="text")
     */
    private $answer;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\EvaluationsDatas", mappedBy="evaluations_a")
     */
    private $evaluationsDatas;

    public function __construct()
    {
        $this->evaluationsDatas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvalQuestionID(): ?evaluationsQuestions
    {
        return $this->evalQuestionID;
    }

    public function setEvalQuestionID(?evaluationsQuestions $evalQuestionID): self
    {
        $this->evalQuestionID = $evalQuestionID;

        return $this;
    }

    public function getIsTrue(): ?bool
    {
        return $this->isTrue;
    }

    public function setIsTrue(bool $isTrue): self
    {
        $this->isTrue = $isTrue;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

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
            $evaluationsData->addEvaluationsA($this);
        }

        return $this;
    }

    public function removeEvaluationsData(EvaluationsDatas $evaluationsData): self
    {
        if ($this->evaluationsDatas->contains($evaluationsData)) {
            $this->evaluationsDatas->removeElement($evaluationsData);
            $evaluationsData->removeEvaluationsA($this);
        }

        return $this;
    }
}
