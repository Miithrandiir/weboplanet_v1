<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DiplomaRepository")
 */
class Diploma
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
     * @ORM\OneToMany(targetEntity="App\Entity\Section", mappedBy="diploma")
     */
    private $section;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="diplomas")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Users", mappedBy="degree")
     */
    private $users;

    public function __construct()
    {
        $this->section = new ArrayCollection();
        $this->users = new ArrayCollection();
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
     * @return Collection|section[]
     */
    public function getSection(): Collection
    {
        return $this->section;
    }

    public function addSection(section $section): self
    {
        if (!$this->section->contains($section)) {
            $this->section[] = $section;
            $section->setDiploma($this);
        }

        return $this;
    }

    public function removeSection(section $section): self
    {
        if ($this->section->contains($section)) {
            $this->section->removeElement($section);
            // set the owning side to null (unless already changed)
            if ($section->getDiploma() === $this) {
                $section->setDiploma(null);
            }
        }

        return $this;
    }

    public function getUser(): ?users
    {
        return $this->user;
    }

    public function setUser(?users $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Users[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(Users $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setDegree($this);
        }

        return $this;
    }

    public function removeUser(Users $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getDegree() === $this) {
                $user->setDegree(null);
            }
        }

        return $this;
    }
}
