<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SectionRepository")
 */
class Section
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
     * @ORM\ManyToOne(targetEntity="App\Entity\SectionRef", inversedBy="sectionID")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sectionRef;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\GroupSection", mappedBy="sectionID")
     */
    private $groupSections;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Diploma", inversedBy="section")
     */
    private $diploma;

    public function __construct()
    {
        $this->groupSections = new ArrayCollection();
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

    public function getSectionRef(): ?SectionRef
    {
        return $this->sectionRef;
    }

    public function setSectionRef(?SectionRef $sectionRef): self
    {
        $this->sectionRef = $sectionRef;

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
            $groupSection->addSectionID($this);
        }

        return $this;
    }

    public function removeGroupSection(GroupSection $groupSection): self
    {
        if ($this->groupSections->contains($groupSection)) {
            $this->groupSections->removeElement($groupSection);
            $groupSection->removeSectionID($this);
        }

        return $this;
    }

    public function getDiploma(): ?Diploma
    {
        return $this->diploma;
    }

    public function setDiploma(?Diploma $diploma): self
    {
        $this->diploma = $diploma;

        return $this;
    }
}
