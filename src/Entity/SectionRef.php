<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SectionRefRepository")
 */
class SectionRef
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Section", mappedBy="sectionRef")
     */
    private $sectionID;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Users", inversedBy="sectionRefs")
     */
    private $userID;

    public function __construct()
    {
        $this->sectionID = new ArrayCollection();
        $this->userID = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|section[]
     */
    public function getSectionID(): Collection
    {
        return $this->sectionID;
    }

    public function addSectionID(section $sectionID): self
    {
        if (!$this->sectionID->contains($sectionID)) {
            $this->sectionID[] = $sectionID;
            $sectionID->setSectionRef($this);
        }

        return $this;
    }

    public function removeSectionID(section $sectionID): self
    {
        if ($this->sectionID->contains($sectionID)) {
            $this->sectionID->removeElement($sectionID);
            // set the owning side to null (unless already changed)
            if ($sectionID->getSectionRef() === $this) {
                $sectionID->setSectionRef(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|users[]
     */
    public function getUserID(): Collection
    {
        return $this->userID;
    }

    public function addUserID(users $userID): self
    {
        if (!$this->userID->contains($userID)) {
            $this->userID[] = $userID;
        }

        return $this;
    }

    public function removeUserID(users $userID): self
    {
        if ($this->userID->contains($userID)) {
            $this->userID->removeElement($userID);
        }

        return $this;
    }
}
