<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PartieRepository")
 */
class Partie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="partiesJ1")
     * @ORM\JoinColumn(nullable=false)
     */
    private $joueur1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="partiesJ2")
     * @ORM\JoinColumn(nullable=true)
     */
    private $joueur2;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationLe;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finieLe;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $des;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tour;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $plateauJ1;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $plateauJ2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $typeVictoire;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="partiesGagnees")
     */
    private $gagnant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="partiesPerdues")
     */
    private $perdant;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Chat", mappedBy="partie")
     */
    private $chats;

    public function __construct()
    {
        $this->chats = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJoueur1(): ?User
    {
        return $this->joueur1;
    }

    public function setJoueur1(?User $joueur1): self
    {
        $this->joueur1 = $joueur1;

        return $this;
    }

    public function getJoueur2(): ?User
    {
        return $this->joueur2;
    }

    public function setJoueur2(?User $joueur2): self
    {
        $this->joueur2 = $joueur2;

        return $this;
    }

    public function getCreationLe(): ?\DateTimeInterface
    {
        return $this->creationLe;
    }

    public function setCreationLe(\DateTimeInterface $creationLe): self
    {
        $this->creationLe = $creationLe;

        return $this;
    }

    public function getFinieLe(): ?\DateTimeInterface
    {
        return $this->finieLe;
    }

    public function setFinieLe(?\DateTimeInterface $finieLe): self
    {
        $this->finieLe = $finieLe;

        return $this;
    }

    public function getDes()
    {
        return $this->des;
    }

    public function setDes($des): self
    {
        $this->des = $des;

        return $this;
    }

    public function getTour(): ?int
    {
        return $this->tour;
    }

    public function setTour(int $tour): self
    {
        $this->tour = $tour;

        return $this;
    }

    public function getPlateauJ1(): ?array
    {
        return $this->plateauJ1;
    }

    public function setPlateauJ1(array $plateauJ1): self
    {
        $this->plateauJ1 = $plateauJ1;

        return $this;
    }

    public function getPlateauJ2(): ?array
    {
        return $this->plateauJ2;
    }

    public function setPlateauJ2(array $plateauJ2): self
    {
        $this->plateauJ2 = $plateauJ2;

        return $this;
    }

    public function getTypeVictoire(): ?string
    {
        return $this->typeVictoire;
    }

    public function setTypeVictoire(?string $typeVictoire): self
    {
        $this->typeVictoire = $typeVictoire;

        return $this;
    }


    public function getGagnant(): ?User
    {
        return $this->gagnant;
    }

    public function setGagnant(?User $gagnant): self
    {
        $this->gagnant = $gagnant;

        return $this;
    }

    public function getPerdant(): ?User
    {
        return $this->perdant;
    }

    public function setPerdant(?User $perdant): self
    {
        $this->perdant = $perdant;

        return $this;
    }

    public function getDuree(){

        if (!empty($this->finieLe)){
            $diff = date_diff($this->creationLe,$this->finieLe);

            return $diff->format('%a jours %h heures %i minutes');
        } else {
            $diff = date_diff($this->creationLe, new \DateTime());

            return $diff->format('%a jours %h heures %i minutes');
        }

    }

    /**
     * @return Collection|Chat[]
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chat $chat): self
    {
        if (!$this->chats->contains($chat)) {
            $this->chats[] = $chat;
            $chat->setPartie($this);
        }

        return $this;
    }

    public function removeChat(Chat $chat): self
    {
        if ($this->chats->contains($chat)) {
            $this->chats->removeElement($chat);
            // set the owning side to null (unless already changed)
            if ($chat->getPartie() === $this) {
                $chat->setPartie(null);
            }
        }

        return $this;
    }

}
