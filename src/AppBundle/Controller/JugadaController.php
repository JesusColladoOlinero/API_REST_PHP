<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Jugada;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class JugadaController extends Controller
{
    /**
     * Obtiene los datos de una partida y genera las jugadas de la misma
     *
     * @Route("/jugadas/{id}", name="jugadas")
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function getJugadasAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $enjuego = true;
        $errorForm = "";

        // Numero de jugadas máximas para completar el juego
        $numJugadasMax = 15;

        // Obtenemos los datos de la partida
        $partida = $em->getRepository('AppBundle\Entity\Partida')
            ->getDatosPartida($id);

        $resultado = $this->GetEstadoPartida($partida);

        // Obtenemos las jugadas de la partida
        $jugadas = $em->getRepository('AppBundle\Entity\Jugada')
            ->getJugadas($id);

        // Comprobamos si la partida ha terminado para no permitir realizar más jugadas
        if (count($jugadas) >= $numJugadasMax || $resultado !== "enjuego"){
            $enjuego = false;
        }

        // Montamos el formulario para guardar las jugadas
        $jugada = new Jugada();

        $form = $this->CrearFormJugada($jugada);

        $form->handleRequest($request);

        if ($this->ColorRepetido($form)){
            $errorForm = "No se puede repetir el color al realizar la jugada";
        }
        // Guardamos la jugada si el formulario de respuesta es válido
        elseif ($form->isSubmitted() && $form->isValid())
        {
            $jugada = $form->getData();

            $idPartida = $partida['id'];

            // marcamos la partida como en juego
            $jugada->setIdPartida($idPartida);

            $rdoJugada = $this->GetResultado($partida['combinacion'], $jugada->getColor1(),
                $jugada->getColor2(), $jugada->getColor3(), $jugada->getColor4(), $jugada->getColor5(),
                $jugada->getColor6());

            // generamos la combinación secreta
            $jugada->setResultado($rdoJugada);

            $dt = date_create(date('Y-m-d H:i:s'));
            // Obtenemos la fecha de acción
            $jugada->setFechaAccion($dt);

            // Guardamos la entidad partida
            $em = $this->getDoctrine()->getManager();
            $em->persist($jugada);
            $em->flush();

            //Si el resultado obtenido es de éxito, actualizo el estado de la partida a victoria.
            if ($rdoJugada == "Negro, Negro, Negro, Negro, Negro, Negro"){
                $this->UpdatePartida($idPartida, 2);
            }

            //Si no tengo más intentos actualizo el estado de la partida a derrota.
            if (count($jugadas) + 1 >= $numJugadasMax){
                $this->UpdatePartida($idPartida, 3);
            }

            // redirigimos al listado de partidas
            return $this->redirectToRoute('jugadas', array('id' => $idPartida));
        }

        return $this->render('partidas/jugadas.html.twig', [
            'jugadas' => $jugadas,
            'partida' => $partida,
            'resultado' => $resultado,
            'enjuego' => $enjuego,
            'errorForm' => $errorForm,
            'form' => $form->createView(),
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
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
    private function ColorRepetido($form)
    {
        if ($form->isSubmitted() && $form->isValid()){
            $jugada = $form->getData();

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
        else{
            return false;
        }
    }

    /**
     * Crea el formulario para realizar la jugadas
     *
     * @param $jugada
     * @return \Symfony\Component\Form\FormInterface
     */
    private function CrearFormJugada($jugada)
    {
        array("rojo", "naranja", "amarillo", "verde", "azul", "lila", "rosa", "gris", "negro", "blanco");
        return $this->createFormBuilder($jugada)
            ->add
            (
                'color1',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices'  =>
                        [
                            'Rojo' => 'rojo',
                            'Azul' => 'azul',
                            'Amarillo' => 'amarillo',
                            'Verde' => 'verde',
                            'Negro' => 'negro',
                            'Blanco' => 'blanco',
                            'Naranja' => 'naranja',
                            'Lila' => 'lila',
                            'Gris' => 'gris',
                            'Rosa' => 'rosa',
                        ]
                ]
            )
            ->add
            (
                'color2',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices'  =>
                        [
                            'Rojo' => 'rojo',
                            'Azul' => 'azul',
                            'Amarillo' => 'amarillo',
                            'Verde' => 'verde',
                            'Negro' => 'negro',
                            'Blanco' => 'blanco',
                            'Naranja' => 'naranja',
                            'Lila' => 'lila',
                            'Gris' => 'gris',
                            'Rosa' => 'rosa',
                        ]
                ]
            )
            ->add
            (
                'color3',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices'  =>
                        [
                            'Rojo' => 'rojo',
                            'Azul' => 'azul',
                            'Amarillo' => 'amarillo',
                            'Verde' => 'verde',
                            'Negro' => 'negro',
                            'Blanco' => 'blanco',
                            'Naranja' => 'naranja',
                            'Lila' => 'lila',
                            'Gris' => 'gris',
                            'Rosa' => 'rosa',
                        ]
                ]
            )
            ->add
            (
                'color4',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices'  =>
                        [
                            'Rojo' => 'rojo',
                            'Azul' => 'azul',
                            'Amarillo' => 'amarillo',
                            'Verde' => 'verde',
                            'Negro' => 'negro',
                            'Blanco' => 'blanco',
                            'Naranja' => 'naranja',
                            'Lila' => 'lila',
                            'Gris' => 'gris',
                            'Rosa' => 'rosa',
                        ]
                ]
            )
            ->add
            (
                'color5',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices'  =>
                        [
                            'Rojo' => 'rojo',
                            'Azul' => 'azul',
                            'Amarillo' => 'amarillo',
                            'Verde' => 'verde',
                            'Negro' => 'negro',
                            'Blanco' => 'blanco',
                            'Naranja' => 'naranja',
                            'Lila' => 'lila',
                            'Gris' => 'gris',
                            'Rosa' => 'rosa',
                        ]
                ]
            )
            ->add
            (
                'color6',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices'  =>
                        [
                            'Rojo' => 'rojo',
                            'Azul' => 'azul',
                            'Amarillo' => 'amarillo',
                            'Verde' => 'verde',
                            'Negro' => 'negro',
                            'Blanco' => 'blanco',
                            'Naranja' => 'naranja',
                            'Lila' => 'lila',
                            'Gris' => 'gris',
                            'Rosa' => 'rosa',
                        ]
                ]
            )
            ->add('save', SubmitType::class, [ 'label' => 'Hacer Jugada!!!' ])
            ->getForm();
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