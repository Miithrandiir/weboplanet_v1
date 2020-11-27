<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluationsGroupRepository")
 */
class EvaluationsGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Group", inversedBy="evaluationsGroups")
     */
    private $groupID;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Evaluations", inversedBy="evaluationsGroups")
     */
    private $evaluationsID;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_end;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasBeenCheck = false;

    public function __construct()
    {
        $this->groupID = new ArrayCollection();
        $this->evaluationsID = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|group[]
     */
    public function getGroupID(): Collection
    {
        return $this->groupID;
    }

    public function addGroupID(group $groupID): self
    {
        if (!$this->groupID->contains($groupID)) {
            $this->groupID[] = $groupID;
        }

        return $this;
    }

    public function removeGroupID(group $groupID): self
    {
        if ($this->groupID->contains($groupID)) {
            $this->groupID->removeElement($groupID);
        }

        return $this;
    }

    /**
     * @return Collection|evaluations[]
     */
    public function getEvaluationsID(): Collection
    {
        return $this->evaluationsID;
    }

    public function addEvaluationsID(evaluations $evaluationsID): self
    {
        if (!$this->evaluationsID->contains($evaluationsID)) {
            $this->evaluationsID[] = $evaluationsID;
        }

        return $this;
    }

    public function removeEvaluationsID(evaluations $evaluationsID): self
    {
        if ($this->evaluationsID->contains($evaluationsID)) {
            $this->evaluationsID->removeElement($evaluationsID);
        }

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->date_start;
    }

    public function setDateStart(\DateTimeInterface $date_start): self
    {
        $this->date_start = $date_start;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(\DateTimeInterface $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getHasBeenCheck(): ?bool
    {
        return $this->hasBeenCheck;
    }

    public function setHasBeenCheck(bool $hasBeenCheck): self
    {
        $this->hasBeenCheck = $hasBeenCheck;

        return $this;
    }
}
