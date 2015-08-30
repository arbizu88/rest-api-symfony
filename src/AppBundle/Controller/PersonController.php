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

        return $persons;
    }


    /**
     * @Route("/{id}", name="get_person")
     * @Method("GET")
     */
    public function getPerson($id)
    {
        $person = $this->getDoctrine()->getRepository('AppBundle:Person')
            ->find($id);

        return $person;
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
     * @ParamConverter("person", converter="fos_rest.request_body")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updatePerson($id, Person $person)
    {

        $em = $this->getDoctrine()->getManager();
        $dbPerson = new Person();
        $dbPerson = $this->getDoctrine()->getRepository('AppBundle:Person')->find($id);

        $dbPerson->setName($person->getName());
        $dbPerson->setAge($person->getAge());
        $dbPerson->setEmail($person->getEmail());
        $dbPerson->setLastName($person->getLastname());
        $dbPerson->setTelephone($person->getTelephone());

        $errors = $this->getErrors($person);

        if (isset($errors)) {
            return $this->view($errors, 400);
        }

        $em->persist($dbPerson);
        $em->flush();

        return $dbPerson;
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



