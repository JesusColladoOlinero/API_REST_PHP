<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Partida
 *
 * @ORM\Table(name="partida")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PartidaRepository")
 */
class Partida
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\OneToMany(targetEntity="Jugada", mappedBy="id")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Nombre", type="string", length=255)
     */
    private $nombre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="FechaAccion", type="datetime")
     */
    private $fechaAccion;

    /**
     * @var string
     *
     * @ORM\Column(name="Combinacion", type="string", length=255)
     */
    private $combinacion;

    /**
     * @var int
     *
     * @ORM\Column(name="Estado", type="integer")
     */
    private $estado;


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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Partida
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set fechaAccion
     *
     * @param \DateTime $fechaAccion
     *
     * @return Partida
     */
    public function setFechaAccion($fechaAccion)
    {
        $this->fechaAccion = $fechaAccion;

        return $this;
    }

    /**
     * Get fechaAccion
     *
     * @return \DateTime
     */
    public function getFechaAccion()
    {
        return $this->fechaAccion;
    }

    /**
     * Set combinacion
     *
     * @param string $combinacion
     *
     * @return Partida
     */
    public function setCombinacion($combinacion)
    {
        $this->combinacion = $combinacion;

        return $this;
    }

    /**
     * Get combinacion
     *
     * @return string
     */
    public function getCombinacion()
    {
        return $this->combinacion;
    }

    /**
     * Set estado
     *
     * @param integer $estado
     *
     * @return Partida
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return int
     */
    public function getEstado()
    {
        return $this->estado;
    }
}

