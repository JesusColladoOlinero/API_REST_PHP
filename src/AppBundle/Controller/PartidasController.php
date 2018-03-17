<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Partida;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
/**use Symfony\Component\Routing\Annotation\Route; */
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PartidasController extends FOSRestController
{
    /**
     * Obtiene todas la partidas
     *
     * @Rest\Get("/partida")
     */
    public function getPartidasAction()
    {
        $em = $this->getDoctrine()->getManager();

        $partidas = $em->getRepository('AppBundle\Entity\Partida')
            ->getAllPartidas();

        if ($partidas === null) {
            return new View("Actualmente no existen partidas.", Response::HTTP_NOT_FOUND);
        }
        return $partidas;
    }

    /**
     * Obtiene los datos de una partida
     *
     * @Rest\Get("/partida/{id}")
     */
    public function getPartida($id)
    {
        $em = $this->getDoctrine()->getManager();

        // Obtenemos los datos de la partida
        $partida = $em->getRepository('AppBundle\Entity\Partida')
            ->getDatosPartidaREST($id);

        if ($partida === null) {
            return new View("Partida no encontrada.", Response::HTTP_NOT_FOUND);
        }
        return $partida;
    }

    /**
     * Crea una nueva partida a partir de un formulario
     *
     * @Rest\Post("/partida")
     */
    public function nuevaPartidaAction(Request $request)
    {
        if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
        else {
            return new View("Header Content-Type must be application/json. Operation not allowed",
                Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $partida = new Partida();

        $nombre = $request->get('nombre');

        if(empty($nombre))
        {
            return new View("El nombre de la partida es obligatorio.", Response::HTTP_NOT_ACCEPTABLE);
        }

        // añadimos el nombre a la partida
        $partida->setNombre($nombre);

        // marcamos la partida como en juego
        $partida->setEstado(1);

        // generamos la combinación secreta
        $partida->setCombinacion($this->GetCombinacion());

        $dt = date_create(date('Y-m-d H:i:s'));
        // Obtenemos la fecha de acción
        $partida->setFechaAccion($dt);

        // Guardamos la entidad partida
        $em = $this->getDoctrine()->getManager();
        $em->persist($partida);
        $em->flush();

        return new View("Se ha añadido la partida correctamente.", Response::HTTP_OK);
    }

    /**
     * Crea una nueva partida a partir de un formulario
     *
     * @Rest\Put("/partida/{id}")
     */
    public function UpdatePartida($id, Request $request)
    {
        if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
        else {
            return new View("Header Content-Type must be application/json. Operation not allowed",
                Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $nombre = $request->get('nombre');
        $estado = $request->get('estado');

        $em = $this->getDoctrine()->getManager();

        // Obtenemos los datos de la partida
        $partida = $em->getRepository('AppBundle\Entity\Partida')
            ->find($id);

        $dt = date_create(date('Y-m-d H:i:s'));
        // Obtenemos la fecha de acción
        $partida->setFechaAccion($dt);

        if (!empty($estado) && !is_numeric($estado)){
            return new View("Estado debe ser un valor numérico.",
                Response::HTTP_NOT_ACCEPTABLE);
        }

        if (empty($partida)) {
            return new View("Partida no encontrada.", Response::HTTP_NOT_FOUND);
        }
        elseif(!empty($nombre) && !empty($estado)){
            $partida->setNombre($nombre);
            $partida->setEstado($estado);
            // Actualizamos la entidad partida
            $em->merge($partida);
            $em->flush();
            return new View("Entidad Partida actualizada correctamente", Response::HTTP_OK);
        }
        elseif(empty($nombre) && !empty($estado)){
            $partida->setEstado($estado);
            // Actualizamos la entidad partida
            $em->merge($partida);
            $em->flush();
            return new View("Estado de partida actualizado correctamente", Response::HTTP_OK);
        }
        elseif(!empty($nombre) && empty($estado)){
            $partida->setNombre($nombre);
            // Actualizamos la entidad partida
            $em->merge($partida);
            $em->flush();
            return new View("Nombre de partida actualizado correctamente", Response::HTTP_OK);
        }
        else return new View("Debe indicar nombre o estado para realizar la actualización",
            Response::HTTP_NOT_ACCEPTABLE);
    }

    // Genera la combinación secreta de 6 colores a partir de 10 disponibles y sin repetirse.
    private function GetCombinacion() {
        $array = array("rojo", "naranja", "amarillo", "verde", "azul", "lila", "rosa", "gris", "negro", "blanco");
        $keys = array_keys($array);
        $new = "";

        shuffle($keys);

        $i = 0;

        foreach($keys as $key) {
            if ($i == 6)
                break;

            if ($new == ""){
                $new = $array[$key];
            }
            else{
                $new = $new .",". $array[$key];
            }

            $i = $i + 1;
        }

        return $new;
    }

}