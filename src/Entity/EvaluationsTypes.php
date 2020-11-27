<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluationsTypesRepository")
 */
class EvaluationsTypes
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
     * @ORM\Column(type="string", length=255)
     */
    private $command;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $language;

    /**
     * @ORM\Column(type="json")
     */
    private $bannedWords = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationsQuestions", mappedBy="type")
     */
    private $evaluationsQuestions;

    public function __construct()
    {
        $this->evaluationsQuestions = new ArrayCollection();
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

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getBannedWords(): ?array
    {
        return $this->bannedWords;
    }

    public function setBannedWords(array $bannedWords): self
    {
        $this->bannedWords = $bannedWords;

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
            $evaluationsQuestion->setType($this);
        }

        return $this;
    }

    public function removeEvaluationsQuestion(EvaluationsQuestions $evaluationsQuestion): self
    {
        if ($this->evaluationsQuestions->contains($evaluationsQuestion)) {
            $this->evaluationsQuestions->removeElement($evaluationsQuestion);
            // set the owning side to null (unless already changed)
            if ($evaluationsQuestion->getType() === $this) {
                $evaluationsQuestion->setType(null);
            }
        }

        return $this;
    }
}
