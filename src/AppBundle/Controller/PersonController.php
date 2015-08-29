<?php
/**
 * Created by PhpStorm.
 * User: arbizu
 * Date: 8/23/2015
 * Time: 10:57 PM
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\Person;
use AppBundle\Form\PersonType;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * Event controller.
 *
 * @Route("/api/person")
 */
class PersonController extends FOSRestController
{

    /**
     * @Route("/", name="get_person_list")
     * @Method("GET")
     */
    public function getPersons()
    {

        $persons = $this->getDoctrine()->getRepository('AppBundle:Person')
            ->findAll();

        $serializer = $this->get('jms_serializer');

        /*Se puede utilizar serialize / normalize / denormalize
        * dependiendo de lo que se necesite realizar
        */
        $json = $serializer->serialize(
            $persons,
            'json'
        );

        return new JsonResponse($json);
    }


    /**
     * @Route("/{id}", name="get_person")
     * @Method("GET")
     */
    public function getPerson($id)
    {
        $persons = $this->getDoctrine()->getRepository('AppBundle:Person')
            ->find($id);
        $serializer = $this->get('jms_serializer');

        $json = $serializer->serialize(
            $persons,
            'json'
        );

        return new JsonResponse($json);
    }

    /**
     * @Route("/", name="create_person")
     * @Method("POST")
     * @ParamConverter("person", converter="fos_rest.request_body")
     */
    public function createPerson(Person $person)
    {
        $errors = $this->getErrors($person);

        if(isset($errors)) {
            return $this->view($errors, 400);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($person);
        $em->flush();

        return $this->view($person, 200);
    }

    private function getErrors ($entity){
        $validator = $this->get('validator');
        $errors = $validator->validate($entity);
        
        if (count($errors) > 0) {
            return $errors;
        }

        return null;
    }

    /**
     * @Route("/{id}", name="update_person")
     * @Method("PUT")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updatePerson(Request $request, Person $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(new PersonType(), $entity, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();

                return $entity;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
    * @Route("/{id}", name="delete_person")
     * @Method("DELETE")
     */
    public function deletePerson(Person $entity){

        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}



