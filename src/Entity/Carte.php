<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CarteRepository")
 */
class Carte
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
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $valeur;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $equipe;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(int $valeur): self
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getEquipe(): ?string
    {
        return $this->equipe;
    }

    public function setEquipe(string $equipe): self
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function isShogun(){
        return $this->valeur === 4;
    }

    public function getCouleurTexte(){
        if ($this->getType() == 'arc' ){
            return "red";
        } elseif ($this->getType() == 'bouclier' ){
            return "green";
        } elseif ($this->getType() == 'hache' ){
            return "blue";
        } else {
            return "shogun";
        }
    }

    public function type() {
        switch ($this->getType()) {
            case 'hache':
                return 'P';
            case 'arc':
                return 'C';
            case 'bouclier':
                return 'F';
            default:
                return 'Sh';
        }
    }

    public function isHache(){
        return $this->getType() === "hache";
    }

    public function isBouclier(){
        return $this->getType() === "bouclier";
    }

    public function isArc(){
        return $this->getType()=== "arc";
    }
}
