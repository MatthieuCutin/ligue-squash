<?php

namespace Bloom\MatchUpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rencontre
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Bloom\MatchUpBundle\Entity\RencontreRepository")
 */
class Rencontre
{
    /**
     * @ORM\ManyToOne(targetEntity="Bloom\UserBundle\Entity\User", inversedBy="rencontres", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdVainqueur", type="integer")
     */
    private $idVainqueur;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdPerdant", type="integer")
     */
    private $IdPerdant;

    /**
     * @var integer
     *
     * @ORM\Column(name="ScorePerdant", type="integer")
     */
    private $scorePerdant;


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
     * Set idVainqueur
     *
     * @param integer $idVainqueur
     * @return Rencontre
     */
    public function setIdVainqueur($idVainqueur)
    {
        $this->idVainqueur = $idVainqueur;
    
        return $this;
    }

    /**
     * Get idVainqueur
     *
     * @return integer 
     */
    public function getIdVainqueur()
    {
        return $this->idVainqueur;
    }

    /**
     * Set scorePerdant
     *
     * @param integer $scorePerdant
     * @return Rencontre
     */
    public function setScorePerdant($scorePerdant)
    {
        $this->scorePerdant = $scorePerdant;
    
        return $this;
    }

    /**
     * Get scorePerdant
     *
     * @return integer 
     */
    public function getScorePerdant()
    {
        return $this->scorePerdant;
    }

    /**
     * Set IdPerdant
     *
     * @param integer $idPerdant
     * @return Rencontre
     */
    public function setIdPerdant($idPerdant)
    {
        $this->IdPerdant = $idPerdant;
    
        return $this;
    }

    /**
     * Get IdPerdant
     *
     * @return integer 
     */
    public function getIdPerdant()
    {
        return $this->IdPerdant;
    }

    /**
     * Set user
     *
     * @param \Bloom\UserBundle\Entity\User $user
     * @return Rencontre
     */
    public function setUser(\Bloom\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Bloom\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
