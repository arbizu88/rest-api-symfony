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

/**
 * Event controller.
 *
 * @Route("/api/person")
 */
class PersonController extends Controller
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
     * @ParamConverter("post", class="AppBundle:Person")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createPerson(Person $request)
    {

        $session = $request->getSession();
        echo $session;
/*        $entity = new Person();
        $form = $this->createForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //return $this->redirect($this->generateUrl('event_show', array('id' => $entity->getId())));

            return new JsonResponse($entity);
        }*/

        return new JsonResponse($request);
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



