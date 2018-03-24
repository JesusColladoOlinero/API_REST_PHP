<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Partida;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class PartidasController extends FOSRestController
{
    /**
     * Gets a games
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets a games",
     *   output={
     *       "class"="AppBundle\Entity\Partida",
     *       "groups"={"list"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the page is not found"
     *   }
     * )
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
     * Gets a Game for a given id
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets a Game for a given id",
     *   output={
     *       "class"="AppBundle\Entity\Partida",
     *       "groups"={"list"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the page is not found"
     *   }
     * )
     *
     * @Rest\Get("/partida/{id}")
     *
     * @param $id
     * @return View
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
     * Creates a new game from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new game from the submitted data.",
     *   input={
     *       "class"="AppBundle\Entity\Partida",
     *       "groups"={"create"}
     *   },
     *   statusCodes = {
     *     201 = "Returned when successful",
     *     405 = "Returned when header type not allow",
     *     406 = "Returned when parameter not acceptable"
     *   }
     * )
     *
     * @Rest\Post("/partida")
     * @param Request $request
     * @return View
     */
    public function nuevaPartidaAction(Request $request)
    {
        // Comprobamos el content-type
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

        return new View("Se ha añadido la partida correctamente.", Response::HTTP_CREATED);
    }

    /**
     * Modify the Game from the submitted data.
     *
     * @Rest\Put("/partida/{id}")
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modify the Game from the submitted data.",
     *   input={
     *       "class"="AppBundle\Entity\Partida",
     *       "groups"={"modify"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when not found the game",
     *     405 = "Returned when header type not allow",
     *     406 = "Returned when parameter not acceptable"
     *   }
     * )
     *
     * @param $id
     * @param Request $request
     * @return View
     */
    public function UpdatePartida($id, Request $request)
    {
        // Comprobamos el content-type
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