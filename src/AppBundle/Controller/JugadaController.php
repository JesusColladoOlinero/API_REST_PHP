<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Jugada;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class JugadaController extends FOSRestController
{
    /**
     * Obtiene los datos de una partida y las jugadas de la misma
     *
     * @Rest\Get("/partida/{id}/jugada")
     *
     * @param $id
     * @return View
     */
    public function getJugadasPartidaAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // Obtenemos las jugadas de la partida
        $jugadas = $em->getRepository('AppBundle\Entity\Jugada')
            ->getJugadasPartida($id);

        if ($jugadas === null) {
            return new View("Actualmente no existen jugadas para la partida indicada.", Response::HTTP_NOT_FOUND);
        }

        return $jugadas;
    }

    /**
     * Obtiene todas las jugadas
     *
     * @Rest\Get("/jugada")
     *
     * @return View
     */
    public function getJugadas()
    {
        $em = $this->getDoctrine()->getManager();

        // Obtenemos los datos de la partida
        $jugadas = $em->getRepository('AppBundle\Entity\Jugada')
            ->getAllJugadas();

        if ($jugadas === null) {
            return new View("Actualmente no existen jugadas en el sistema.", Response::HTTP_NOT_FOUND);
        }
        return $jugadas;
    }

    /**
     * Obtiene los datos de la jugada
     *
     * @Rest\Get("/jugada/{id}")
     *
     * @param $id
     * @return View
     */
    public function getJugada($id)
    {
        $em = $this->getDoctrine()->getManager();

        // Obtenemos los datos de la partida
        $jugada = $em->getRepository('AppBundle\Entity\Jugada')
            ->getJugada($id);

        if ($jugada === null) {
            return new View("Jugada no encontrada.", Response::HTTP_NOT_FOUND);
        }
        return $jugada;
    }


    /**
     * Crea una nueva jugada a partir de una partida y una apuesta
     *
     * @Rest\Post("/partida/{id}/jugada")
     * @param Request $request
     * @return View
     */
    public function setJugadaAction($id, Request $request)
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

        $em = $this->getDoctrine()->getManager();

        // Numero de jugadas máximas para completar el juego
        $numJugadasMax = 6;

        // Obtenemos los datos de la partida
        $partida = $em->getRepository('AppBundle\Entity\Partida')
            ->getDatosPartida($id);

        if (empty($partida)) {
            return new View("Partida no encontrada.", Response::HTTP_NOT_FOUND);
        }

        $resultado = $this->GetEstadoPartida($partida);

        // Obtenemos las jugadas de la partida
        $jugadas = $em->getRepository('AppBundle\Entity\Jugada')
            ->getJugadasPartida($id);

        // Comprobamos si la partida ha terminado para no permitir realizar más jugadas
        if (!empty($jugada)) {
            if (count($jugadas) >= $numJugadasMax || $resultado !== "enjuego") {
                return new View(
                    "La partida está finalizada. No se permite realizar más jugadas.",
                    Response::HTTP_NOT_ACCEPTABLE
                );
            }
        }

        // Obtenemos los colores del body de la petición
        $color1 = $request->get('color1');
        $color2 = $request->get('color2');
        $color3 = $request->get('color3');
        $color4 = $request->get('color4');
        $color5 = $request->get('color5');
        $color6 = $request->get('color6');

        if(empty($color1) || empty($color2) || empty($color3) || empty($color4) || empty($color5) || empty($color6))
        {
            return new View("Para añadir una jugada debe indicar 6 colores. (color1,color2,color3,color4,color5,color6)", Response::HTTP_NOT_ACCEPTABLE);
        }

        if (!$this->CheckNombreColor($color1) || !$this->CheckNombreColor($color2) || !$this->CheckNombreColor($color3) || !$this->CheckNombreColor($color4) || !$this->CheckNombreColor($color5) || !$this->CheckNombreColor($color6)){
            return new View("Algún color de la jugada tiene un valor erróneo. Valores correctos (rojo, naranja, verde, azul, lila, rosa, gris, negro, blanco, amarillo)", Response::HTTP_NOT_ACCEPTABLE);
        }

        // Montamos el formulario para guardar las jugadas
        $jugada = new Jugada();

        // Establecemos los colores en la jugada
        $jugada->setColor1($color1);
        $jugada->setColor2($color2);
        $jugada->setColor3($color3);
        $jugada->setColor4($color4);
        $jugada->setColor5($color5);
        $jugada->setColor6($color6);

        // Chequeamos que no haya colores repetidos en la jugada
        if ($this->ColorRepetido($jugada)){
            return new View("No se puede repetir el color al realizar la jugada", Response::HTTP_NOT_ACCEPTABLE);
        }

        $idPartida = $partida['id'];

        // añadimos el id de la partida a la jugada
        $jugada->setIdPartida($idPartida);

        // Obtenemos el resultado de la jugada
        $rdoJugada = $this->GetResultado($partida['combinacion'], $jugada->getColor1(),
            $jugada->getColor2(), $jugada->getColor3(), $jugada->getColor4(), $jugada->getColor5(),
            $jugada->getColor6());

        // generamos la combinación secreta
        $jugada->setResultado($rdoJugada);

        $dt = date_create(date('Y-m-d H:i:s'));
        // Obtenemos la fecha de acción
        $jugada->setFechaAccion($dt);

         // Guardamos la entidad partida
        $em->persist($jugada);
        $em->flush();

        //Si el resultado obtenido es de éxito, actualizo el estado de la partida a victoria.
        if ($rdoJugada == "Negro, Negro, Negro, Negro, Negro, Negro"){
            $this->UpdatePartida($idPartida, 2);

            return new View("Se ha añadido la jugada correctamente. Resultado Jugada: Victoria!!!",
                Response::HTTP_OK);
        }
        elseif (!empty($jugadas)){
            //Si no tengo más intentos actualizo el estado de la partida a derrota.
            if (count($jugadas) + 1 >= $numJugadasMax){
                $this->UpdatePartida($idPartida, 3);

                return new View("Se ha añadido la jugada correctamente. Resultado Jugada: Derrota!!!",
                    Response::HTTP_OK);
            }
        }

        return new View("Se ha añadido la jugada correctamente. Resultado Jugada: " . $rdoJugada,
             Response::HTTP_OK);

//        // Guardamos la jugada si el formulario de respuesta es válido
//        elseif ($form->isSubmitted() && $form->isValid())
//        {
//            $jugada = $form->getData();
//
//            $idPartida = $partida['id'];
//
//            // marcamos la partida como en juego
//            $jugada->setIdPartida($idPartida);
//
//            $rdoJugada = $this->GetResultado($partida['combinacion'], $jugada->getColor1(),
//                $jugada->getColor2(), $jugada->getColor3(), $jugada->getColor4(), $jugada->getColor5(),
//                $jugada->getColor6());
//
//            // generamos la combinación secreta
//            $jugada->setResultado($rdoJugada);
//
//            $dt = date_create(date('Y-m-d H:i:s'));
//            // Obtenemos la fecha de acción
//            $jugada->setFechaAccion($dt);
//
//            // Guardamos la entidad partida
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($jugada);
//            $em->flush();
//
//            //Si el resultado obtenido es de éxito, actualizo el estado de la partida a victoria.
//            if ($rdoJugada == "Negro, Negro, Negro, Negro, Negro, Negro"){
//                $this->UpdatePartida($idPartida, 2);
//            }
//
//            //Si no tengo más intentos actualizo el estado de la partida a derrota.
//            if (count($jugadas) + 1 >= $numJugadasMax){
//                $this->UpdatePartida($idPartida, 3);
//            }
//
//            // redirigimos al listado de partidas
//            return $this->redirectToRoute('jugadas', array('id' => $idPartida));
//        }
//
//        return $this->render('partidas/jugadas.html.twig', [
//            'jugadas' => $jugadas,
//            'partida' => $partida,
//            'resultado' => $resultado,
//            'enjuego' => $enjuego,
//            'errorForm' => $errorForm,
//            'form' => $form->createView(),
//            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
//        ]);
    }



    private function UpdatePartida($idpartida, $estado)
    {
        $em = $this->getDoctrine()->getManager();

        // Obtenemos los datos de la partida
        $partida = $em->getRepository('AppBundle\Entity\Partida')
            ->find($idpartida);

        $partida->setEstado($estado);

        $dt = date_create(date('Y-m-d H:i:s'));
        // Obtenemos la fecha de acción
        $partida->setFechaAccion($dt);

        // Actualizamos la entidad partida
        $em->merge($partida);
        $em->flush();
    }

    /**
     * Obtiene la clase para el estado de la partida.
     *
     * @param $partida
     * @return string
     */
    private function GetEstadoPartida($partida)
    {
        if(empty($partida)){
            return "enjuego";
        }

        switch ($partida['idestado']){
            case "1":
                return "enjuego";
            case "2":
                return "victoria";
            case "3":
                return "derrota";
            default:
                return "enjuego";
        }
    }

    /**
     * Comprueba si existe un color repetido en la jugada
     *
     * @param $form
     * @return bool
     */
    private function ColorRepetido($jugada)
    {
        $color1 = $jugada->getColor1();
        $color2 = $jugada->getColor2();
        $color3 = $jugada->getColor3();
        $color4 = $jugada->getColor4();
        $color5 = $jugada->getColor5();
        $color6 = $jugada->getColor6();

        // Comprobamos para cada color si está repetido en el resto de la jugada
        if ($this->ContainsWord($color2 . "|" . $color3 . "|" . $color4 . "|" . $color5 . "|" . $color6, $color1)) { return true; }
        if ($this->ContainsWord($color1 . "|" . $color3 . "|" . $color4 . "|" . $color5 . "|" . $color6, $color2)) { return true; }
        if ($this->ContainsWord($color1 . "|" . $color2 . "|" . $color4 . "|" . $color5 . "|" . $color6, $color3)) { return true; }
        if ($this->ContainsWord($color1 . "|" . $color2 . "|" . $color3 . "|" . $color5 . "|" . $color6, $color4)) { return true; }
        if ($this->ContainsWord($color1 . "|" . $color2 . "|" . $color3 . "|" . $color4 . "|" . $color6, $color5)) { return true; }
        if ($this->ContainsWord($color1 . "|" . $color2 . "|" . $color3 . "|" . $color4 . "|" . $color5, $color6)) { return true; }
    }

    /**
     * Crea el formulario para realizar la jugadas
     *
     * @param $jugada
     * @return \Symfony\Component\Form\FormInterface
     */
    private function CheckNombreColor($color)
    {
        if ($color === "rojo" || $color === "naranja" || $color === "verde" || $color === "azul" || $color === "lila" || $color === "rosa" || $color === "gris" || $color === "negro" || $color === "blanco" || $color === "amarillo"){
            return true;
        }

        return false;
    }

    /**
     * Obtiene el resultado de la jugada realizada para la combinación secreta de la partida
     *
     * @param $combinacion
     * @param $color1
     * @param $color2
     * @param $color3
     * @param $color4
     * @param $color5
     * @param $color6
     * @return string
     */
    private function GetResultado($combinacion, $color1, $color2, $color3, $color4, $color5, $color6)
    {
        if ($combinacion == null){
            return "NULL, NULL, NULL, NULL, NULL, NULL";
        }

        $rdo = "";

        // obtenemos los colores de la combinación ganadora
        list($combColor1, $combColor2, $combColor3, $combColor4, $combColor5, $combColor6) = explode(',',
            $combinacion);

        // Chequeamos cada color seleccionado
        $rdo = $this->CheckColor($combColor1, $color1, $combinacion);
        $rdo = $rdo. ", ". $this->CheckColor($combColor2, $color2, $combinacion);
        $rdo = $rdo. ", ". $this->CheckColor($combColor3, $color3, $combinacion);
        $rdo = $rdo. ", ". $this->CheckColor($combColor4, $color4, $combinacion);
        $rdo = $rdo. ", ". $this->CheckColor($combColor5, $color5, $combinacion);
        $rdo = $rdo. ", ". $this->CheckColor($combColor6, $color6, $combinacion);

        return $rdo;
    }

    /**
     * Chequea si el color seleccionado es correcto o no y devuelve el resultado de la comprobación.
     * NULL --> Cuando no existe el color
     * Blanco --> Cuando existe pero no es su posicion correcta
     * Negro --> Cuando existe y es su posición correcta
     *
     * @param $combColor
     * @param $color
     * @param $combinacion
     * @return string
     */
    private function CheckColor($combColor, $color, $combinacion)
    {
        $rdo = "NULL";

        if ($combColor == $color){
            $rdo = "Negro";
        }
        elseif ($this->ContainsWord($combinacion, $color)){
            $rdo = "Blanco";
        }

        return $rdo;
    }

    /**
     * Comprueba si una cadena contiene los caracteres indicados en el parámetro $word
     *
     * @param $str
     * @param $word
     * @return bool
     */
    private function ContainsWord($str, $word)
    {
        return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#i', $str);
    }
}