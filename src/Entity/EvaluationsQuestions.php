<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluationsQuestionsRepository")
 */
class EvaluationsQuestions
{
    public const RULES_STRICT = 1;
    public const RULES_SOFT = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluations", inversedBy="evaluationsQuestions")
     */
    private $evaluationsID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $question;

    /**
     * @ORM\Column(type="float")
     */
    private $points;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationsAnswer", mappedBy="evalQuestionID")
     */
    private $evaluationsAnswers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EvaluationsTypes", inversedBy="evaluationsQuestions")
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationsDatas", mappedBy="evaluations_q", orphanRemoval=true)
     */
    private $evaluationsDatas;

    /**
     * @ORM\Column(type="integer")
     */
    private $correction_rule = 1;

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    private $tested_keys = [];


    public function __construct()
    {
        $this->evaluationsAnswers = new ArrayCollection();
        $this->evaluationsDatas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvaluationsID(): ?evaluations
    {
        return $this->evaluationsID;
    }

    public function setEvaluationsID(?evaluations $evaluationsID): self
    {
        $this->evaluationsID = $evaluationsID;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getPoints(): ?float
    {
        return $this->points;
    }

    public function setPoints(float $points): self
    {
        $this->points = $points;

        return $this;
    }

    /**
     * @return Collection|EvaluationsAnswer[]
     */
    public function getEvaluationsAnswers(): Collection
    {
        return $this->evaluationsAnswers;
    }

    public function addEvaluationsAnswer(EvaluationsAnswer $evaluationsAnswer): self
    {
        if (!$this->evaluationsAnswers->contains($evaluationsAnswer)) {
            $this->evaluationsAnswers[] = $evaluationsAnswer;
            $evaluationsAnswer->setEvalQuestionID($this);
        }

        return $this;
    }

    public function removeEvaluationsAnswer(EvaluationsAnswer $evaluationsAnswer): self
    {
        if ($this->evaluationsAnswers->contains($evaluationsAnswer)) {
            $this->evaluationsAnswers->removeElement($evaluationsAnswer);
            // set the owning side to null (unless already changed)
            if ($evaluationsAnswer->getEvalQuestionID() === $this) {
                $evaluationsAnswer->setEvalQuestionID(null);
            }
        }

        return $this;
    }

    public function getType(): ?EvaluationsTypes
    {
        return $this->type;
    }

    public function setType(?EvaluationsTypes $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

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
            $evaluationsData->setEvaluationsQ($this);
        }

        return $this;
    }

    public function removeEvaluationsData(EvaluationsDatas $evaluationsData): self
    {
        if ($this->evaluationsDatas->contains($evaluationsData)) {
            $this->evaluationsDatas->removeElement($evaluationsData);
            // set the owning side to null (unless already changed)
            if ($evaluationsData->getEvaluationsQ() === $this) {
                $evaluationsData->setEvaluationsQ(null);
            }
        }

        return $this;
    }

    public function getCorrectionRule(): ?int
    {
        return $this->correction_rule;
    }

    public function setCorrectionRule(int $correction_rule): self
    {
        $this->correction_rule = $correction_rule;

        return $this;
    }

    public function getTestedKeys(): ?array
    {
        return $this->tested_keys;
    }

    public function setTestedKeys(?array $tested_keys): self
    {
        $this->tested_keys = $tested_keys;

        return $this;
    }
}
