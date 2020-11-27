<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersGroupRepository")
 */
class UsersGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Group", inversedBy="usersGroups")
     */
    private $groupID;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Users", inversedBy="usersGroups")
     */
    private $userID;

    public function __construct()
    {
        $this->groupID = new ArrayCollection();
        $this->userID = new ArrayCollection();
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
