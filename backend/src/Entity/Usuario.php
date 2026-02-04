<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\Table(name: 'usuario')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['usuario:read']]),
        new GetCollection(normalizationContext: ['groups' => ['usuario:read']]),
        new Post(denormalizationContext: ['groups' => ['usuario:write']]),
        new Patch(denormalizationContext: ['groups' => ['usuario:write']]),
        new Delete()
    ],
    paginationItemsPerPage: 25
)]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['usuario:read', 'alerta:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['usuario:read', 'usuario:write', 'alerta:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['usuario:read', 'usuario:write'])]
    private ?string $nombre = null;

    /**
     * @var list<string>
     */
    #[ORM\Column]
    #[Groups(['usuario:read'])]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['usuario:write'])]
    private ?string $plainPassword = null;

    #[ORM\Column]
    #[Groups(['usuario:read'])]
    private bool $activo = true;

    #[ORM\Column]
    #[Groups(['usuario:read'])]
    private bool $notificacionesEmail = true;

    /**
     * @var Collection<int, Alerta>
     */
    #[ORM\OneToMany(targetEntity: Alerta::class, mappedBy: 'usuario', orphanRemoval: true)]
    private Collection $alertas;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->alertas = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;
        return $this;
    }

    public function isNotificacionesEmail(): bool
    {
        return $this->notificacionesEmail;
    }

    public function setNotificacionesEmail(bool $notificacionesEmail): static
    {
        $this->notificacionesEmail = $notificacionesEmail;
        return $this;
    }

    /**
     * @return Collection<int, Alerta>
     */
    public function getAlertas(): Collection
    {
        return $this->alertas;
    }

    public function addAlerta(Alerta $alerta): static
    {
        if (!$this->alertas->contains($alerta)) {
            $this->alertas->add($alerta);
            $alerta->setUsuario($this);
        }
        return $this;
    }

    public function removeAlerta(Alerta $alerta): static
    {
        if ($this->alertas->removeElement($alerta)) {
            if ($alerta->getUsuario() === $this) {
                $alerta->setUsuario(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
