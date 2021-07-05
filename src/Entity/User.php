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
 * @ORM\Table(indexes={@ORM\Index(columns={"email", "nick_name"}, flags={"fulltext"})})
 * @UniqueEntity(fields={"email"}, message="user.email_exists")
 * @UniqueEntity(fields={"nickName"}, message="user.nickname_in_use")
 */
class User implements UserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     *
     * @var array
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
     *
     * @var bool
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $helpers = true;

    /**
     * @ORM\ManyToMany(targetEntity=TaskList::class, mappedBy="shared", orphanRemoval=true)
     *
     * @var Collection<TaskList>
     */
    private $shared;

    /**
     * @ORM\OneToMany(targetEntity=TaskList::class, mappedBy="creator")
     *
     * @var Collection<TaskList>
     */
    private $taskLists;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     *
     * @var string|null
     */
    private $locale;

    /**
     * @var DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var Collection<Notification>
     *
     * @ORM\OneToMany(targetEntity=Notification::class, mappedBy="user", orphanRemoval=true)
     */
    private $notifications;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=32, nullable=true, unique=true)
     */
    private $nickName;

    /**
     * @var Collection<AdminNotification>
     *
     * @ORM\OneToMany(targetEntity=AdminNotification::class, mappedBy="user")
     */
    private $adminNotifications;

    /**
     * @ORM\ManyToMany(targetEntity=User::class)
     * @ORM\JoinTable(name="user_favourite",
     *      joinColumns={@ORM\JoinColumn(name="user", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="favourites", referencedColumnName="id")}
     * )
     *
     * @var Collection<User>
     */
    private $favouriteUsers;

    /**
     * @ORM\ManyToMany(targetEntity=User::class)
     * @ORM\JoinTable(name="user_banned",
     *      joinColumns={@ORM\JoinColumn(name="user", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="banned", referencedColumnName="id")}
     * )
     *
     * @var Collection<User>
     */
    private $bannedUsers;

    /**
     *
     */
    public function __construct()
    {
        $this->shared = new ArrayCollection();
        $this->taskLists = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->adminNotifications = new ArrayCollection();
        $this->favouriteUsers = new ArrayCollection();
        $this->bannedUsers = new ArrayCollection();
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
        $roles[] = self::ROLE_USER;

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
     * @param string $role
     *
     * @return $this
     */
    public function addRole(string $role): self
    {
        array_push($this->roles, $role);
        $this->roles = array_unique($this->roles);

        return $this;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
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
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
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
     * @return bool
     */
    public function getHelpers(): bool
    {
        return $this->helpers;
    }

    /**
     * @param bool $helpers
     *
     * @return $this
     */
    public function setHelpers(bool $helpers): self
    {
        $this->helpers = $helpers;

        return $this;
    }

    /**
     * @return Collection<TaskList>
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
     * @return Collection<TaskList>
     */
    public function getTaskLists(): Collection
    {
        return $this->taskLists;
    }

    /**
     * @param User $user
     *
     * @return Collection
     */
    public function getCommonTaskLists(User $user): Collection
    {
        return $this->taskLists->filter(
            function (TaskList $taskList) use ($user) {
                return $taskList->getShared()->contains($user);
            }
        );
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
     * @return Collection<Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    /**
     * @return string|null
     */
    public function getNickName(): ?string
    {
        return $this->nickName ?? substr($this->email, 0, strpos($this->email, '@') ?: null);
    }

    /**
     * @param string $nickName
     *
     * @return $this
     */
    public function setNickName(string $nickName): User
    {
        $this->nickName = $nickName;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        $colors = ['orange', 'green', 'purple'];
        return $colors[array_rand($colors)];
    }

    /**
     * @return Collection|AdminNotification[]
     */
    public function getAdminNotifications(): Collection
    {
        return $this->adminNotifications;
    }

    /**
     * @return Collection
     */
    public function getFavouriteUsers(): Collection
    {
        return $this->favouriteUsers;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isFavourite(User $user): bool
    {
        return $this->favouriteUsers->contains($user);
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function addFavouriteUser(User $user): self
    {
        if (!$this->favouriteUsers->contains($user)) {
            $this->favouriteUsers->add($user);
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function removeFromFavouriteUsers(User $user): self
    {
        $this->favouriteUsers->removeElement($user);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getBannedUsers(): Collection
    {
        return $this->bannedUsers;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function banUser(User $user): self
    {
        if (!$this->bannedUsers->contains($user)) {
            $this->bannedUsers->add($user);
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isBanned(User $user): bool
    {
        return $this->bannedUsers->contains($user);
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function removeFromBan(User $user): self
    {
        $this->bannedUsers->removeElement($user);

        return $this;
    }
}
