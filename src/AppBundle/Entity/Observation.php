<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\User;

/**
 * Observation
 *
 * @ORM\Table(name="observation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ObservationRepository")
 */
class Observation
{

    const A_VALIDER = "A valider";
    const VALIDEE = "Validée";
    const A_MODIFIER = "A modifier";
    const SIGNALEE = "Signalée";
    const SUPPRIMEE = "Supprimée";
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OiseauTaxref")
    * @ORM\JoinColumn(nullable=false)
    * @Assert\NotBlank()
    */
    private $oiseau;


    /**
    * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User",inversedBy="observations")
    * @ORM\JoinColumn(nullable=false)
    * @Assert\NotBlank()
    */
    private $author;
    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\NotBlank()
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="commentForNoValidation", type="text", nullable=true)
     */
    private $commentForNoValidation;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal", precision=25, scale=20)
     * @Assert\NotBlank()
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal", precision=25, scale=20)
     * @Assert\NotBlank()
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     * @Assert\File(mimeTypes={ "image/jpeg", "image/png", "image/jpg"})
     * @Assert\Image
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="audio", type="string", length=255, nullable=true)
     */
    private $audio;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish", type="datetime", nullable=true)
     */
    private $publish;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;



    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $validateur;

    /**
     * @return mixed
     */
    public function getValidateur()
    {
        return $this->validateur;
    }

    /**
     * @param User $validateur
     */
    public function setValidateur(User $validateur)
    {
        $this->validateur = $validateur;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOiseau()
    {
        return $this->oiseau;
    }

    /**
     * @param mixed $oiseau
     */
    public function setOiseau($oiseau)
    {
        $this->oiseau = $oiseau;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    
    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Observation
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Observation
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set commentForNoValidation
     *
     * @param string $commentForNoValidation
     *
     * @return Observation
     */
    public function setCommentForNoValidation($commentForNoValidation)
    {
        $this->commentForNoValidation = $commentForNoValidation;

        return $this;
    }

    /**
     * Get commentForNoValidation
     *
     * @return string
     */
    public function getCommentForNoValidation()
    {
        return $this->commentForNoValidation;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Observation
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Observation
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set photo
     *
     * @param string $photo
     *
     * @return Observation
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set audio
     *
     * @param string $audio
     *
     * @return Observation
     */
    public function setAudio($audio)
    {
        $this->audio = $audio;

        return $this;
    }

    /**
     * Get audio
     *
     * @return string
     */
    public function getAudio()
    {
        return $this->audio;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Observation
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set publish
     *
     * @param \DateTime $publish
     *
     * @return Observation
     */
    public function setPublish($publish)
    {
        $this->publish = $publish;

        return $this;
    }

    /**
     * Get publish
     *
     * @return \DateTime
     */
    public function getPublish()
    {
        return $this->publish;
    }

    /**
     * Set deleted
     *
     * @param \DateTime $deleted
     *
     * @return Observation
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return \DateTime
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}

