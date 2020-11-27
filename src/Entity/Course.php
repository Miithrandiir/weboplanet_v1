<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CourseRepository")
 */
class Course
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
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVisible;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="courses")
     */
    private $userID;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Chapter", mappedBy="courseID")
     */
    private $chapters;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\CourseLink", mappedBy="courseID")
     */
    private $courseLinks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CourseRef", mappedBy="courseID")
     */
    private $courseRefs;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
        $this->courseLinks = new ArrayCollection();
        $this->courseRefs = new ArrayCollection();
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

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;

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

    /**
     * @return Collection|Chapter[]
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): self
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters[] = $chapter;
            $chapter->setCourseID($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        if ($this->chapters->contains($chapter)) {
            $this->chapters->removeElement($chapter);
            // set the owning side to null (unless already changed)
            if ($chapter->getCourseID() === $this) {
                $chapter->setCourseID(null);
            }
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
            $courseLink->addCourseID($this);
        }

        return $this;
    }

    public function removeCourseLink(CourseLink $courseLink): self
    {
        if ($this->courseLinks->contains($courseLink)) {
            $this->courseLinks->removeElement($courseLink);
            $courseLink->removeCourseID($this);
        }

        return $this;
    }

    /**
     * @return Collection|CourseRef[]
     */
    public function getCourseRefs(): Collection
    {
        return $this->courseRefs;
    }

    public function addCourseRef(CourseRef $courseRef): self
    {
        if (!$this->courseRefs->contains($courseRef)) {
            $this->courseRefs[] = $courseRef;
            $courseRef->setCourseID($this);
        }

        return $this;
    }

    public function removeCourseRef(CourseRef $courseRef): self
    {
        if ($this->courseRefs->contains($courseRef)) {
            $this->courseRefs->removeElement($courseRef);
            // set the owning side to null (unless already changed)
            if ($courseRef->getCourseID() === $this) {
                $courseRef->setCourseID(null);
            }
        }

        return $this;
    }
}
