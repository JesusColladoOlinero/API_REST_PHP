<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Jugada
 *
 * @ORM\Table(name="jugada")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\JugadaRepository")
 */
class Jugada
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\ManyToOne(targetEntity="Partida", inversedBy="idPartida")
     *
     * @JMS\SerializedName("id")
     * @JMS\Type("integer")
     * @JMS\Groups({ "detail" })
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="IdPartida", type="integer")
     *
     * @JMS\SerializedName("idPartida")
     * @JMS\Type("integer")
     * @JMS\Groups({ "detail" })
     */
    private $idPartida;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="FechaAccion", type="datetime")
     *
     * @JMS\SerializedName("fechaAccion")
     * @JMS\Type("datetime")
     * @JMS\Groups({ "detail" })
     */
    private $fechaAccion;

    /**
     *
     * @JMS\SerializedName("apuesta")
     * @JMS\Type("string")
     * @JMS\Groups({ "detail" })
     */
    private $apuesta;

    /**
     * @var string
     *
     * @ORM\Column(name="Color1", type="string", length=50)
     *
     * @JMS\SerializedName("color1")
     * @JMS\Type("string")
     * @JMS\Groups({ "create" })
     */
    private $color1;

    /**
     * @var string
     *
     * @ORM\Column(name="Color2", type="string", length=50)
     *
     * @JMS\SerializedName("color2")
     * @JMS\Type("string")
     * @JMS\Groups({ "create" })
     */
    private $color2;

    /**
     * @var string
     *
     * @ORM\Column(name="Color3", type="string", length=50)
     *
     * @JMS\SerializedName("color3")
     * @JMS\Type("string")
     * @JMS\Groups({ "create" })
     */
    private $color3;

    /**
     * @var string
     *
     * @ORM\Column(name="Color4", type="string", length=50)
     *
     * @JMS\SerializedName("color4")
     * @JMS\Type("string")
     * @JMS\Groups({ "create" })
     */
    private $color4;

    /**
     * @var string
     *
     * @ORM\Column(name="Color5", type="string", length=50)
     *
     * @JMS\SerializedName("color5")
     * @JMS\Type("string")
     * @JMS\Groups({ "create" })
     */
    private $color5;

    /**
     * @var string
     *
     * @ORM\Column(name="Color6", type="string", length=50)
     *
     * @JMS\SerializedName("color6")
     * @JMS\Type("string")
     * @JMS\Groups({ "create" })
     */
    private $color6;

    /**
     * @var string
     *
     * @ORM\Column(name="Resultado", type="string", length=255)
     *
     * @JMS\SerializedName("resultado")
     * @JMS\Type("string")
     * @JMS\Groups({ "detail" })
     */
    private $resultado;


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
     * Set idPartida
     *
     * @param integer $idPartida
     *
     * @return Jugada
     */
    public function setIdPartida($idPartida)
    {
        $this->idPartida = $idPartida;

        return $this;
    }

    /**
     * Get idPartida
     *
     * @return int
     */
    public function getIdPartida()
    {
        return $this->idPartida;
    }

    /**
     * Set fechaAccion
     *
     * @param \DateTime $fechaAccion
     *
     * @return Jugada
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
     * Set color1
     *
     * @param string $color1
     *
     * @return Jugada
     */
    public function setColor1($color1)
    {
        $this->color1 = $color1;

        return $this;
    }

    /**
     * Get color1
     *
     * @return string
     */
    public function getColor1()
    {
        return $this->color1;
    }

    /**
     * Set color2
     *
     * @param string $color2
     *
     * @return Jugada
     */
    public function setColor2($color2)
    {
        $this->color2 = $color2;

        return $this;
    }

    /**
     * Get color2
     *
     * @return string
     */
    public function getColor2()
    {
        return $this->color2;
    }

    /**
     * Set color3
     *
     * @param string $color3
     *
     * @return Jugada
     */
    public function setColor3($color3)
    {
        $this->color3 = $color3;

        return $this;
    }

    /**
     * Get color3
     *
     * @return string
     */
    public function getColor3()
    {
        return $this->color3;
    }

    /**
     * Set color4
     *
     * @param string $color4
     *
     * @return Jugada
     */
    public function setColor4($color4)
    {
        $this->color4 = $color4;

        return $this;
    }

    /**
     * Get color4
     *
     * @return string
     */
    public function getColor4()
    {
        return $this->color4;
    }

    /**
     * Set color5
     *
     * @param string $color5
     *
     * @return Jugada
     */
    public function setColor5($color5)
    {
        $this->color5 = $color5;

        return $this;
    }

    /**
     * Get color5
     *
     * @return string
     */
    public function getColor5()
    {
        return $this->color5;
    }

    /**
     * Set color6
     *
     * @param string $color6
     *
     * @return Jugada
     */
    public function setColor6($color6)
    {
        $this->color6 = $color6;

        return $this;
    }

    /**
     * Get color6
     *
     * @return string
     */
    public function getColor6()
    {
        return $this->color6;
    }

    /**
     * Set resultado
     *
     * @param string $resultado
     *
     * @return Jugada
     */
    public function setResultado($resultado)
    {
        $this->resultado = $resultado;

        return $this;
    }

    /**
     * Get resultado
     *
     * @return string
     */
    public function getResultado()
    {
        return $this->resultado;
    }
}

