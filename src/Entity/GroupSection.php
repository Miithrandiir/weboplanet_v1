<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupSectionRepository")
 */
class GroupSection
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Section", inversedBy="groupSections")
     */
    private $sectionID;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Group", inversedBy="groupSections")
     */
    private $groupID;

    public function __construct()
    {
        $this->sectionID = new ArrayCollection();
        $this->groupID = new ArrayCollection();
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
        }

        return $this;
    }

    public function removeSectionID(section $sectionID): self
    {
        if ($this->sectionID->contains($sectionID)) {
            $this->sectionID->removeElement($sectionID);
        }

        return $this;
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
}
