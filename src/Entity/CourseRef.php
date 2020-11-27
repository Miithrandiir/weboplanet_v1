<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseRefRepository")
 */
class CourseRef
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Course", inversedBy="courseRefs")
     */
    private $courseID;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Users", inversedBy="courseRefs")
     */
    private $userID;

    public function __construct()
    {
        $this->userID = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourseID(): ?course
    {
        return $this->courseID;
    }

    public function setCourseID(?course $courseID): self
    {
        $this->courseID = $courseID;

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
