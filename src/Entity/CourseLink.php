<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseLinkRepository")
 */
class CourseLink
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Course", inversedBy="courseLinks")
     */
    private $courseID;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="courseLinks")
     */
    private $groupID;

    public function __construct()
    {
        $this->courseID = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|course[]
     */
    public function getCourseID(): Collection
    {
        return $this->courseID;
    }

    public function addCourseID(course $courseID): self
    {
        if (!$this->courseID->contains($courseID)) {
            $this->courseID[] = $courseID;
        }

        return $this;
    }

    public function removeCourseID(course $courseID): self
    {
        if ($this->courseID->contains($courseID)) {
            $this->courseID->removeElement($courseID);
        }

        return $this;
    }

    public function getGroupID(): ?group
    {
        return $this->groupID;
    }

    public function setGroupID(?group $groupID): self
    {
        $this->groupID = $groupID;

        return $this;
    }
}
