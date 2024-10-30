<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity(
    fields : ['pseudo'],
    message: 'Le pseudo est déjà utilisé, veuillez en choisir un autre')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[Assert\NotBlank(message:"rentrer un nom !!")]
    #[Assert\Length(min:4,max:50)]
    #[ORM\Column(length: 180)]
    private ?string $Lastname = null;

    #[Assert\NotBlank(message:"rentrer un prenom !!")]
    #[Assert\Length(min:4,max:50)]
    #[ORM\Column(length: 180)]
    private ?string $Firstname= null;

    #[Assert\NotBlank(message:"rentrer un numéro de téléphone !!")]
    #[Assert\Length(min:10, max:10, exactMessage: "la longueur doit être exactement de {{ limit }} chiffres")]
    #[ORM\Column]
    private ?string $telephone = null;

    #[Assert\NotBlank(message: "Rentrer une adresse email !!")]
    #[Assert\Email(message: "Le format de l'email '{{ value }}' n'est pas valide.")]
    #[Assert\Length(max: 180, maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères.")]
    #[ORM\Column(length: 180)]
    private ?string $email;

    /**
     * @var string The hashed password
     */
    #[Assert\Length(
        min: 8,
        max: 64,
        minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le mot de passe ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/",
        message: "Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial."
    )]
    #[Assert\NotBlank(message: "Rentrer votre mot de passe.")]
    #[ORM\Column]
    private ?string $password;

    #[ORM\Column]
    private ?bool $isActif = true;

    public function getIsActif(): ?bool
    {
        return $this->isActif;
    }

    public function setIsActif(?bool $isActif): void
    {
        $this->isActif = $isActif;
    }

    #[ORM\ManyToOne(inversedBy: 'participants')]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $inscrit;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $organisateur;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 4000, nullable: true)]
    private ?string $newPassword = null;

    #[ORM\Column(length: 4000, nullable: true)]
    private ?string $newPasswordConfirmation = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profileImageFilename = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $failedAttempts = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lockedUntil = null;

    public function __construct()
    {
        $this->inscrit = new ArrayCollection();
        $this->organisateur = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->Lastname;
    }

    public function setLastname(string $Lastname): static
    {
        $this->Lastname = $Lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->Firstname;
    }

    public function setFirstName(string $Firstname): static
    {
        $this->Firstname = $Firstname;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }



    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getInscrit(): Collection
    {
        return $this->inscrit;
    }

    public function addInscrit(Sortie $inscrit): static
    {
        if (!$this->inscrit->contains($inscrit)) {
            $this->inscrit->add($inscrit);
        }

        return $this;
    }

    public function removeInscrit(Sortie $inscrit): static
    {
        $this->inscrit->removeElement($inscrit);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getOrganisateur(): Collection
    {
        return $this->organisateur;
    }

    public function addOrganisateur(Sortie $organisateur): static
    {
        if (!$this->organisateur->contains($organisateur)) {
            $this->organisateur->add($organisateur);
            $organisateur->setParticipant($this);
        }

        return $this;
    }

    public function removeOrganisateur(Sortie $organisateur): static
    {
        if ($this->organisateur->removeElement($organisateur)) {
            // set the owning side to null (unless already changed)
            if ($organisateur->getParticipant() === $this) {
                $organisateur->setParticipant(null);
            }
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     *
     *
     */
    public function eraseCredentials(): void
    {
    }

 /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles= $this->roles;
        $roles[]='ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): static
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getNewPasswordConfirmation(): ?string
    {
        return $this->newPasswordConfirmation;
    }

    public function setNewPasswordConfirmation(?string $newPasswordConfirmation): static
    {
        $this->newPasswordConfirmation = $newPasswordConfirmation;

        return $this;
    }
    public function getProfileImageFilename(): ?string
    {
        return $this->profileImageFilename;
    }

    public function setProfileImageFilename(?string $profileImageFilename): self
    {
        $this->profileImageFilename = $profileImageFilename;
        return $this;
    }
    public function getFailedAttempts(): ?int
    {
        return $this->failedAttempts;
    }

    public function setFailedAttempts(?int $failedAttempts): self
    {
        $this->failedAttempts = $failedAttempts;
        return $this;
    }

    public function getLockedUntil(): ?\DateTimeInterface
    {
        return $this->lockedUntil;
    }

    public function setLockedUntil(?\DateTimeInterface $lockedUntil): self
    {
        $this->lockedUntil = $lockedUntil;
        return $this;
    }
}


