<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\Table(name="`group`")
 */
class Group
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
     * @ORM\ManyToMany(targetEntity="App\Entity\GroupSection", mappedBy="groupID")
     */
    private $groupSections;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\UsersGroup", mappedBy="groupID")
     */
    private $usersGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CourseLink", mappedBy="groupID")
     */
    private $courseLinks;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\EvaluationsGroup", mappedBy="groupID")
     */
    private $evaluationsGroups;

    public function __construct()
    {
        $this->groupSections = new ArrayCollection();
        $this->usersGroups = new ArrayCollection();
        $this->courseLinks = new ArrayCollection();
        $this->evaluationsGroups = new ArrayCollection();
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

    /**
     * @return Collection|GroupSection[]
     */
    public function getGroupSections(): Collection
    {
        return $this->groupSections;
    }

    public function addGroupSection(GroupSection $groupSection): self
    {
        if (!$this->groupSections->contains($groupSection)) {
            $this->groupSections[] = $groupSection;
            $groupSection->addGroupID($this);
        }

        return $this;
    }

    public function removeGroupSection(GroupSection $groupSection): self
    {
        if ($this->groupSections->contains($groupSection)) {
            $this->groupSections->removeElement($groupSection);
            $groupSection->removeGroupID($this);
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
            $usersGroup->addGroupID($this);
        }

        return $this;
    }

    public function removeUsersGroup(UsersGroup $usersGroup): self
    {
        if ($this->usersGroups->contains($usersGroup)) {
            $this->usersGroups->removeElement($usersGroup);
            $usersGroup->removeGroupID($this);
        }

        return $this;
    }

    /**
     * @return Collection|CourseLink[]
     */
    public function getCourseLinks(): Collection
    {
        return $this->courseLinks;
    }

    public function addCourseLink(CourseLink $courseLink): self
    {
        if (!$this->courseLinks->contains($courseLink)) {
            $this->courseLinks[] = $courseLink;
            $courseLink->setGroupID($this);
        }

        return $this;
    }

    public function removeCourseLink(CourseLink $courseLink): self
    {
        if ($this->courseLinks->contains($courseLink)) {
            $this->courseLinks->removeElement($courseLink);
            // set the owning side to null (unless already changed)
            if ($courseLink->getGroupID() === $this) {
                $courseLink->setGroupID(null);
            }
        }

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
            $evaluationsGroup->addGroupID($this);
        }

        return $this;
    }

    public function removeEvaluationsGroup(EvaluationsGroup $evaluationsGroup): self
    {
        if ($this->evaluationsGroups->contains($evaluationsGroup)) {
            $this->evaluationsGroups->removeElement($evaluationsGroup);
            $evaluationsGroup->removeGroupID($this);
        }

        return $this;
    }
}
