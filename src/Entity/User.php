<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints as Recaptcha;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Cet email est déja utilisé !"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $pseudo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationLe;

    /**
     * @ORM\Column(type="datetime")
     */
    private $connexionLe;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $avatar;

    /**
     * @ORM\Column(type="text")
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $online;

    /**
     * @ORM\Column(type="integer")
     */
    private $avertissement;

    /**
     * @ORM\Column(type="boolean")
     */
    private $blocage;

    /**
     * @Recaptcha\IsTrue
     */
    public $recaptcha;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ListeAmi", mappedBy="amiUser", orphanRemoval=true)
     */
    private $listeAmis;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mail", mappedBy="expediteur")
     */
    private $mailsExpedies;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Mail", mappedBy="destinataire")
     */
    private $mailsRecus;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Partie", mappedBy="joueur1")
     */
    private $partiesJ1;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Partie", mappedBy="joueur2")
     */
    private $partiesJ2;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Partie", mappedBy="gagnant")
     */
    private $partiesGagnees;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Partie", mappedBy="perdant")
     */
    private $partiesPerdues;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Chat", mappedBy="expediteur")
     */
    private $chats;


    public function __construct()
    {
        $this->listeAmis = new ArrayCollection();
        $this->mailsExpedies = new ArrayCollection();
        $this->mailsRecus = new ArrayCollection();
        $this->partiesJ1 = new ArrayCollection();
        $this->partiesJ2 = new ArrayCollection();
        $this->chats = new ArrayCollection();
        $this->partiesGagnees = new ArrayCollection();
        $this->partiesPerdues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

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
        return (string)$this->email;
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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return (string)$this->password;
    }

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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

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

    public function getConnexionLe(): ?\DateTimeInterface
    {
        return $this->connexionLe;
    }

    public function setConnexionLe(\DateTimeInterface $connexionLe): self
    {
        $this->connexionLe = $connexionLe;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function getAvertissement(): ?int
    {
        return $this->avertissement;
    }

    public function setAvertissement(int $avertissement): self
    {
        $this->avertissement = $avertissement;

        return $this;
    }

    public function getBlocage(): ?bool
    {
        return $this->blocage;
    }

    public function setBlocage(bool $blocage): self
    {
        $this->blocage = $blocage;

        return $this;
    }

    /**
     * @return Collection|ListeAmi[]
     */
    public function getListeAmis(): Collection
    {
        return $this->listeAmis;
    }

    public function addListeAmi(ListeAmi $listeAmi): self
    {
        if (!$this->listeAmis->contains($listeAmi)) {
            $this->listeAmis[] = $listeAmi;
            $listeAmi->setAmiUser($this);
        }

        return $this;
    }

    public function removeListeAmi(ListeAmi $listeAmi): self
    {
        if ($this->listeAmis->contains($listeAmi)) {
            $this->listeAmis->removeElement($listeAmi);
            // set the owning side to null (unless already changed)
            if ($listeAmi->getAmiUser() === $this) {
                $listeAmi->setAmiUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Mail[]
     */
    public function getMailsExpedies(): Collection
    {
        return $this->mailsExpedies;
    }

    public function addMailsExpedy(Mail $mailsExpedy): self
    {
        if (!$this->mailsExpedies->contains($mailsExpedy)) {
            $this->mailsExpedies[] = $mailsExpedy;
            $mailsExpedy->setExpediteur($this);
        }

        return $this;
    }

    public function removeMailsExpedy(Mail $mailsExpedy): self
    {
        if ($this->mailsExpedies->contains($mailsExpedy)) {
            $this->mailsExpedies->removeElement($mailsExpedy);
            // set the owning side to null (unless already changed)
            if ($mailsExpedy->getExpediteur() === $this) {
                $mailsExpedy->setExpediteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Mail[]
     */
    public function getMailsRecus(): Collection
    {
        return $this->mailsRecus;
    }

    public function addMailsRecus(Mail $mailsRecus): self
    {
        if (!$this->mailsRecus->contains($mailsRecus)) {
            $this->mailsRecus[] = $mailsRecus;
            $mailsRecus->setDestinataire($this);
        }

        return $this;
    }

    public function removeMailsRecus(Mail $mailsRecus): self
    {
        if ($this->mailsRecus->contains($mailsRecus)) {
            $this->mailsRecus->removeElement($mailsRecus);
            // set the owning side to null (unless already changed)
            if ($mailsRecus->getDestinataire() === $this) {
                $mailsRecus->setDestinataire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Partie[]
     */
    public function getPartiesJ1(): Collection
    {
        return $this->partiesJ1;
    }

    public function addPartiesJ1(Partie $partiesJ1): self
    {
        if (!$this->partiesJ1->contains($partiesJ1)) {
            $this->partiesJ1[] = $partiesJ1;
            $partiesJ1->setJoueur1($this);
        }

        return $this;
    }

    public function removePartiesJ1(Partie $partiesJ1): self
    {
        if ($this->partiesJ1->contains($partiesJ1)) {
            $this->partiesJ1->removeElement($partiesJ1);
            // set the owning side to null (unless already changed)
            if ($partiesJ1->getJoueur1() === $this) {
                $partiesJ1->setJoueur1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Partie[]
     */
    public function getPartiesJ2(): Collection
    {
        return $this->partiesJ2;
    }

    public function addPartiesJ2(Partie $partiesJ2): self
    {
        if (!$this->partiesJ2->contains($partiesJ2)) {
            $this->partiesJ2[] = $partiesJ2;
            $partiesJ2->setJoueur2($this);
        }

        return $this;
    }

    public function removePartiesJ2(Partie $partiesJ2): self
    {
        if ($this->partiesJ2->contains($partiesJ2)) {
            $this->partiesJ2->removeElement($partiesJ2);
            // set the owning side to null (unless already changed)
            if ($partiesJ2->getJoueur2() === $this) {
                $partiesJ2->setJoueur2(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|Partie[]
     */
    public function getPartiesGagnees(): Collection
    {
        return $this->partiesGagnees;
    }

    public function addPartiesGagnee(Partie $partiesGagnee): self
    {
        if (!$this->partiesGagnees->contains($partiesGagnee)) {
            $this->partiesGagnees[] = $partiesGagnee;
            $partiesGagnee->setGagnant($this);
        }

        return $this;
    }

    public function removePartiesGagnee(Partie $partiesGagnee): self
    {
        if ($this->partiesGagnees->contains($partiesGagnee)) {
            $this->partiesGagnees->removeElement($partiesGagnee);
            // set the owning side to null (unless already changed)
            if ($partiesGagnee->getGagnant() === $this) {
                $partiesGagnee->setGagnant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Partie[]
     */
    public function getPartiesPerdues(): Collection
    {
        return $this->partiesPerdues;
    }

    public function addPartiesPerdue(Partie $partiesPerdue): self
    {
        if (!$this->partiesPerdues->contains($partiesPerdue)) {
            $this->partiesPerdues[] = $partiesPerdue;
            $partiesPerdue->setPerdant($this);
        }

        return $this;
    }

    public function removePartiesPerdue(Partie $partiesPerdue): self
    {
        if ($this->partiesPerdues->contains($partiesPerdue)) {
            $this->partiesPerdues->removeElement($partiesPerdue);
            // set the owning side to null (unless already changed)
            if ($partiesPerdue->getPerdant() === $this) {
                $partiesPerdue->setPerdant(null);
            }
        }

        return $this;
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
            $chat->setExpediteur($this);
        }

        return $this;
    }

    public function removeChat(Chat $chat): self
    {
        if ($this->chats->contains($chat)) {
            $this->chats->removeElement($chat);
            // set the owning side to null (unless already changed)
            if ($chat->getExpediteur() === $this) {
                $chat->setExpediteur(null);
            }
        }

        return $this;
    }
}