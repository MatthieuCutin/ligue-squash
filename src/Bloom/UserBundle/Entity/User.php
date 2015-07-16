<?php
// src/Bloom/UserBundle/Entity/User.php

namespace Bloom\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\ArrayCollection;

/**
* @ORM\Entity
* @ORM\Table(name="bloom_user")
* @ORM\Entity(repositoryClass="Bloom\UserBundle\Entity\UserRepository")
* @Vich\Uploadable
*/

class User extends BaseUser
{
    /**
     * @ORM\OneToMany(targetEntity="Bloom\MatchUpBundle\Entity\Rencontre", mappedBy="user", cascade={"persist"})
     */
    protected $rencontres;

     /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

     /**
     * @var integer $age
     *
     * @ORM\column(name="age", type="integer", nullable=true)
     * @Assert\Range(min = 1, minMessage = "Vous devez avoir au moins 1 an")
    */
    private $age;

     /**
     * @var integer $poule
     *
     * @ORM\column(name="poule", type="integer", nullable=true)
     * @Assert\Range(min = 1)
    */
    private $poule;

     /**
     * @var integer $nouvellepoule
     *
     * @ORM\column(name="nouvellepoule", type="integer", nullable=true)
     * @Assert\Range(min = 0)
    */
    private $nouvellepoule;    

     /**
     * @var integer $victoires
     *
     * @ORM\column(name="victoires", type="integer", nullable=true)
     * @Assert\Range(min = 0)
    */
    private $victoires;

     /**
     * @var integer $sets
     *
     * @ORM\column(name="sets", type="integer", nullable=true)
     * @Assert\Range(min = 0)
    */
    private $sets;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min="6",
     *                max="14",
     *                minMessage="Votre numéro doit être sous la forme 0XXXXXXXXX",
     *                maxMessage="Votre numéro doit être sous la forme 0XXXXXXXXX")
     */
    private $tel;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min="0",
     *                max="80",
     *                maxMessage="Votre message doit comporter moins de 80 charactères")
     */
    private $lundi;

    /**
     * @var string $mardi
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min="0",
     *                max="80",
     *                maxMessage="Votre message doit comporter moins de 80 charactères")     
     */
    private $mardi;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min="0",
     *                max="80",
     *                maxMessage="Votre message doit comporter moins de 80 charactères")     
     */
    private $mercredi;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min="0",
     *                max="80",
     *                maxMessage="Votre message doit comporter moins de 80 charactères")     
     */
    private $jeudi;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min="0",
     *                max="80",
     *                maxMessage="Votre message doit comporter moins de 80 charactères")     
     */
    private $vendredi;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min="0",
     *                max="80",
     *                maxMessage="Votre message doit comporter moins de 80 charactères")     
     */
    private $samedi;

    /**
     * @ORM\Column(type="boolean")
     */
    private $participation;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * 
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="imageName")
     * @Assert\Image(maxSize="5000000")
     * 
     * @var File $imageFile
     */
    protected $imageFile;

    /**
     * @ORM\Column(type="string", length=255, name="image_name", nullable=true)
     *
     * @var string $imageName
     */
    protected $imageName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime $updatedAt
     */
    protected $updatedAt;


     public function __construct()
    {
        parent::__construct();
        $this->imageName = null;
        $this->participation = true;
        $this->rencontres = new ArrayCollection();
        $this->sets = 0;
        $this->victoires = 0;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime('now');
        }
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param string $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set points
     *
     * @param integer $points
     * @return User
     */
    public function setPoints($points)
    {
        $this->points = $points;
    
        return $this;
    }

    /**
     * Get points
     *
     * @return integer 
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set niveau
     *
     * @param string $niveau
     * @return User
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;
    
        return $this;
    }

    /**
     * Get niveau
     *
     * @return string 
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * Set tel
     *
     * @param string $tel
     * @return User
     */
    public function setTel($tel)
    {
        $this->tel = $tel;
    
        return $this;
    }

    /**
     * Get tel
     *
     * @return string 
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set lundi
     *
     * @param string $lundi
     * @return User
     */
    public function setLundi($lundi)
    {
        $this->lundi = $lundi;
    
        return $this;
    }

    /**
     * Get lundi
     *
     * @return string 
     */
    public function getLundi()
    {
        return $this->lundi;
    }

    /**
     * Set mardi
     *
     * @param string $mardi
     * @return User
     */
    public function setMardi($mardi)
    {
        $this->mardi = $mardi;
    
        return $this;
    }

    /**
     * Get mardi
     *
     * @return string 
     */
    public function getMardi()
    {
        return $this->mardi;
    }

    /**
     * Set mercredi
     *
     * @param string $mercredi
     * @return User
     */
    public function setMercredi($mercredi)
    {
        $this->mercredi = $mercredi;
    
        return $this;
    }

    /**
     * Get mercredi
     *
     * @return string 
     */
    public function getMercredi()
    {
        return $this->mercredi;
    }

    /**
     * Set jeudi
     *
     * @param string $jeudi
     * @return User
     */
    public function setJeudi($jeudi)
    {
        $this->jeudi = $jeudi;
    
        return $this;
    }

    /**
     * Get jeudi
     *
     * @return string 
     */
    public function getJeudi()
    {
        return $this->jeudi;
    }

    /**
     * Set vendredi
     *
     * @param string $vendredi
     * @return User
     */
    public function setVendredi($vendredi)
    {
        $this->vendredi = $vendredi;
    
        return $this;
    }

    /**
     * Get vendredi
     *
     * @return string 
     */
    public function getVendredi()
    {
        return $this->vendredi;
    }

    /**
     * Set samedi
     *
     * @param string $samedi
     * @return User
     */
    public function setSamedi($samedi)
    {
        $this->samedi = $samedi;
    
        return $this;
    }

    /**
     * Get samedi
     *
     * @return string 
     */
    public function getSamedi()
    {
        return $this->samedi;
    }

    /**
     * Set age
     *
     * @param integer $age
     * @return User
     */
    public function setAge($age)
    {
        $this->age = $age;
    
        return $this;
    }

    /**
     * Get age
     *
     * @return integer 
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set poule
     *
     * @param integer $poule
     * @return User
     */
    public function setPoule($poule)
    {
        $this->poule = $poule;
    
        return $this;
    }

    /**
     * Get poule
     *
     * @return integer 
     */
    public function getPoule()
    {
        return $this->poule;
    }

    /**
     * Set victoires
     *
     * @param integer $victoires
     * @return User
     */
    public function setVictoires($victoires)
    {
        $this->victoires = $victoires;
    
        return $this;
    }

    /**
     * Get victoires
     *
     * @return integer 
     */
    public function getVictoires()
    {
        return $this->victoires;
    }

    /**
     * Set sets
     *
     * @param integer $sets
     * @return User
     */
    public function setSets($sets)
    {
        $this->sets = $sets;
    
        return $this;
    }

    /**
     * Set nouvellepoule
     *
     * @param integer $nouvellepoule
     * @return User
     */
    public function setNouvellepoule($nouvellepoule)
    {
        $this->nouvellepoule = $nouvellepoule;
    
        return $this;
    }

    /**
     * Get nouvellepoule
     *
     * @return integer 
     */
    public function getNouvellepoule()
    {
        return $this->nouvellepoule;
    }

    /**
     * Get sets
     *
     * @return integer 
     */
    public function getSets()
    {
        return $this->sets;
    }

    /**
     * Add rencontres
     *
     * @param \Bloom\MatchUpBundle\Entity\Rencontre $rencontres
     * @return User
     */
    public function addRencontre(\Bloom\MatchUpBundle\Entity\Rencontre $rencontres)
    {
        $this->rencontres[] = $rencontres;
        $rencontres->setUser($this);
    
        return $this;
    }

    /**
     * Remove rencontres
     *
     * @param \Bloom\MatchUpBundle\Entity\Rencontre $rencontres
     */
    public function removeRencontre(\Bloom\MatchUpBundle\Entity\Rencontre $rencontres)
    {
        $this->rencontres->removeElement($rencontres);
    }

    /**
     * Get rencontres
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRencontres()
    {
        return $this->rencontres;
    }

    /**
     * Set participation
     *
     * @param boolean $participation
     * @return User
     */
    public function setParticipation($participation)
    {
        $this->participation = $participation;
    
        return $this;
    }

    /**
     * Get participation
     *
     * @return boolean 
     */
    public function getParticipation()
    {
        return $this->participation;
    }
}
