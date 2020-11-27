<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluationsDatasRepository")
 */
class EvaluationsDatas
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluations", inversedBy="evaluationsDatas")
     */
    private $evaluationID;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="evaluationsDatas")
     */
    private $userID;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EvaluationsQuestions", inversedBy="evaluationsDatas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $evaluations_q;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\EvaluationsAnswer", inversedBy="evaluationsDatas")
     */
    private $evaluations_a;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $codeResponse;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isCorrect;

    public function __construct()
    {
        $this->evaluations_a = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvaluationID(): ?evaluations
    {
        return $this->evaluationID;
    }

    public function setEvaluationID(?evaluations $evaluationID): self
    {
        $this->evaluationID = $evaluationID;

        return $this;
    }

    public function getUserID(): ?users
    {
        return $this->userID;
    }

    public function setUserID(?users $userID): self
    {
        $this->userID = $userID;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getEvaluationsQ(): ?EvaluationsQuestions
    {
        return $this->evaluations_q;
    }

    public function setEvaluationsQ(?EvaluationsQuestions $evaluations_q): self
    {
        $this->evaluations_q = $evaluations_q;

        return $this;
    }

    /**
     * @return Collection|EvaluationsAnswer[]
     */
    public function getEvaluationsA(): Collection
    {
        return $this->evaluations_a;
    }

    public function addEvaluationsA(EvaluationsAnswer $evaluationsA): self
    {
        if (!$this->evaluations_a->contains($evaluationsA)) {
            $this->evaluations_a[] = $evaluationsA;
        }

        return $this;
    }

    public function removeEvaluationsA(EvaluationsAnswer $evaluationsA): self
    {
        if ($this->evaluations_a->contains($evaluationsA)) {
            $this->evaluations_a->removeElement($evaluationsA);
        }

        return $this;
    }

    public function getCodeResponse(): ?string
    {
        return $this->codeResponse;
    }

    public function setCodeResponse(?string $codeResponse): self
    {
        $this->codeResponse = $codeResponse;

        return $this;
    }

    public function getIsCorrect(): ?bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(?bool $isCorrect): self
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }
}
