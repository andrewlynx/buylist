<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="user.email_exists")
 * @UniqueEntity(fields={"nickName"}, message="user.nickname_in_use")
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
     *
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
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $locale;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var Collection|Notification[]
     *
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="user", orphanRemoval=true)
     */
    private $notifications;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32, nullable=true, unique=true)
     */
    private $nickName;

    /**
     *
     */
    public function __construct()
    {
        $this->shared = new ArrayCollection();
        $this->taskLists = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function removeTaskList(TaskList $taskList): self
    {
        if ($this->taskLists->removeElement($taskList)) {
            $taskList->removeShared($this);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string|null $locale
     *
     * @return $this
     */
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastLogin(): ?DateTimeInterface
    {
        return $this->lastLogin;
    }

    /**
     * @param DateTimeInterface $lastLogin
     *
     * @return $this
     */
    public function setLastLogin(DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    /**
     * @return string
     */
    public function getNickName(): string
    {
        return $this->nickName ?? substr($this->email, 0, strpos($this->email, '@'));
    }

    /**
     * @param string $nickName
     *
     * @return User
     */
    public function setNickName(string $nickName): User
    {
        $this->nickName = $nickName;

        return $this;
    }
}
