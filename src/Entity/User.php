<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\ManyToMany(targetEntity=TaskList::class, mappedBy="shared", orphanRemoval=true)
     */
    private $shared;

    /**
     * @ORM\OneToMany(targetEntity=TaskList::class, mappedBy="creator")
     */
    private $taskLists;

    /**
     *
     */
    public function __construct()
    {
        $this->shared = new ArrayCollection();
        $this->taskLists = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @param bool $isVerified
     *
     * @return User
     */
    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection|TaskList[]
     */
    public function getShared(): Collection
    {
        return $this->shared;
    }

    /**
     * @param TaskList $shared
     *
     * @return User
     */
    public function addShared(TaskList $shared): self
    {
        if (!$this->shared->contains($shared)) {
            $this->shared[] = $shared;
            $shared->setCreator($this);
        }

        return $this;
    }

    /**
     * @param TaskList $shared
     *
     * @return User
     */
    public function removeShared(TaskList $shared): self
    {
        if ($this->shared->removeElement($shared)) {
            // set the owning side to null (unless already changed)
            if ($shared->getCreator() === $this) {
                $shared->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TaskList[]
     */
    public function getTaskLists(): Collection
    {
        return $this->taskLists;
    }

    /**
     * @param TaskList $taskList
     *
     * @return User
     */
    public function addTaskList(TaskList $taskList): self
    {
        if (!$this->taskLists->contains($taskList)) {
            $this->taskLists[] = $taskList;
            $taskList->addShared($this);
        }

        return $this;
    }

    /**
     * @param TaskList $taskList
     *
     * @return User
     */
    public function removeTaskList(TaskList $taskList): self
    {
        if ($this->taskLists->removeElement($taskList)) {
            $taskList->removeShared($this);
        }

        return $this;
    }
}
