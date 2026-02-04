<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\OrganoContratanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrganoContratanteRepository::class)]
#[ORM\Table(name: 'organo_contratante')]
#[ORM\Index(columns: ['nif'], name: 'idx_organo_nif')]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['organo:read', 'organo:detail']]),
        new GetCollection(normalizationContext: ['groups' => ['organo:read']])
    ],
    order: ['nombre' => 'ASC'],
    paginationItemsPerPage: 50
)]
class OrganoContratante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['organo:read', 'licitacion:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Groups(['organo:read', 'licitacion:read'])]
    private ?string $nif = null;

    #[ORM\Column(length: 500)]
    #[Groups(['organo:read', 'licitacion:read'])]
    private ?string $nombre = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['organo:read', 'organo:detail'])]
    private ?string $dir3 = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['organo:read'])]
    private ?string $idPlataforma = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['organo:detail'])]
    private ?string $tipoAdministracion = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['organo:detail'])]
    private ?string $codigoActividad = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['organo:detail'])]
    private ?string $direccion = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['organo:detail'])]
    private ?string $municipio = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['organo:read'])]
    private ?string $provincia = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['organo:detail'])]
    private ?string $codigoPostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['organo:detail'])]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['organo:detail'])]
    private ?string $telefono = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['organo:detail'])]
    private ?string $urlPerfil = null;

    /**
     * @var Collection<int, Licitacion>
     */
    #[ORM\OneToMany(targetEntity: Licitacion::class, mappedBy: 'organoContratante')]
    private Collection $licitaciones;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->licitaciones = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(string $nif): static
    {
        $this->nif = $nif;
        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getDir3(): ?string
    {
        return $this->dir3;
    }

    public function setDir3(?string $dir3): static
    {
        $this->dir3 = $dir3;
        return $this;
    }

    public function getIdPlataforma(): ?string
    {
        return $this->idPlataforma;
    }

    public function setIdPlataforma(?string $idPlataforma): static
    {
        $this->idPlataforma = $idPlataforma;
        return $this;
    }

    public function getTipoAdministracion(): ?string
    {
        return $this->tipoAdministracion;
    }

    public function setTipoAdministracion(?string $tipoAdministracion): static
    {
        $this->tipoAdministracion = $tipoAdministracion;
        return $this;
    }

    public function getCodigoActividad(): ?string
    {
        return $this->codigoActividad;
    }

    public function setCodigoActividad(?string $codigoActividad): static
    {
        $this->codigoActividad = $codigoActividad;
        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): static
    {
        $this->direccion = $direccion;
        return $this;
    }

    public function getMunicipio(): ?string
    {
        return $this->municipio;
    }

    public function setMunicipio(?string $municipio): static
    {
        $this->municipio = $municipio;
        return $this;
    }

    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    public function setProvincia(?string $provincia): static
    {
        $this->provincia = $provincia;
        return $this;
    }

    public function getCodigoPostal(): ?string
    {
        return $this->codigoPostal;
    }

    public function setCodigoPostal(?string $codigoPostal): static
    {
        $this->codigoPostal = $codigoPostal;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;
        return $this;
    }

    public function getUrlPerfil(): ?string
    {
        return $this->urlPerfil;
    }

    public function setUrlPerfil(?string $urlPerfil): static
    {
        $this->urlPerfil = $urlPerfil;
        return $this;
    }

    /**
     * @return Collection<int, Licitacion>
     */
    public function getLicitaciones(): Collection
    {
        return $this->licitaciones;
    }

    public function addLicitacion(Licitacion $licitacion): static
    {
        if (!$this->licitaciones->contains($licitacion)) {
            $this->licitaciones->add($licitacion);
            $licitacion->setOrganoContratante($this);
        }
        return $this;
    }

    public function removeLicitacion(Licitacion $licitacion): static
    {
        if ($this->licitaciones->removeElement($licitacion)) {
            if ($licitacion->getOrganoContratante() === $this) {
                $licitacion->setOrganoContratante(null);
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
