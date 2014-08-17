<?php

namespace Pixonite\RestApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pixonite\RestApiBundle\Entity\SampleData;
use Pixonite\RestApiBundle\Form\SampleDataType;

/**
 * This is the sample controller for a REST api that I quickly cooked up.
 *
 * @author R.J. Keller <rjkeller-fun@pixonite.com>
 * @Route("/api/v1/")
 */
class SampleDataController extends Controller
{

    /**
     * Lists all SampleData entities.
     *
     * @Route("{entityName}.json", name="api_sample-data")
     * @Method("GET")
     */
    public function indexAction($entityName)
    {
        $this->getRequest()->setRequestFormat("json");
        //-- Do some validation first
        $this->checkEntityName($entityName);



        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PixoniteRestApiBundle:'. $entityName)->findAll();

        return $this->getJsonResponse(['entities' => $entities]);
    }

    /**
     * Creates a new SampleData entity.
     *
     * @Route("{entityName}.json", name="api_sample-data_create")
     * @Method("POST")
     */
    public function createAction($entityName)
    {
        //-- Do some validation first
        $this->checkEntityName($entityName);



        $request = $this->getRequest();
        $entityClass = "Pixonite\\RestApiBundle\\Entity\\". $entityName;
        $entityTypeClass = "Pixonite\\RestApiBundle\\Form\\". $entityName . "Type";

        $entity = new $entityClass();
        $form = $this->createCreateForm($entity, new $entityTypeClass());
        $form->submit($_POST, false);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->getJsonResponse([
                "response" => "Success!",
                "entity" => $entity,
            ]);
        }

        return $this->getJsonResponse(['entity' => $entity]);
    }

    /**
     * Finds and displays a SampleData entity.
     *
     * @Route("{entityName}/{id}.json", name="api_sample-data_show")
     * @Method("GET")
     */
    public function showAction($entityName, $id)
    {
        //-- Do some validation first
        $this->checkEntityName($entityName);



        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PixoniteRestApiBundle:'. $entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SampleData entity.');
        }

        return $this->getJsonResponse(['entity' => $entity]);
    }


    /**
     * Deletes a SampleData entity.
     *
     * @Route("{entityName}/{id}.json", name="api_sample-data_delete")
     * @Method("DELETE")
     */
    public function deleteAction($entityName, $id)
    {
        //-- Do some validation first
        $this->checkEntityName($entityName);


        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('PixoniteRestApiBundle:'. $entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SampleData entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->getJsonResponse([
            "response" => "Success!",
            "id" => $id,
        ]);
    }

    //----------- FORMS FOR VARIOUS REST FUNCTIONS -----------//


    /**
     * Creates a form to create a SampleData entity.
     *
     * @param $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm($entity, $type)
    {
        $form = $this->createForm($type,$entity, array('method' => 'POST'));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Checks if the entity name passed in is invalid. If it is invalid, an
     * exception is thrown.
     * 
     * @throws Exception if the entity name passed is invalid.
     * @return true all the time (if an exception is not thrown)
     */
    private function checkEntityName($name)
    {
        $em = $this->getDoctrine()->getManager();
        $allMetadata = $em->getMetadataFactory()->getAllMetadata();
        $allEntities = [];

        foreach ($allMetadata as $entityMetaData) {
            $classNameInfo = explode("\\", $entityMetaData->getName());
            $allEntities[] = array_pop($classNameInfo);
        }

        if (!in_array($name, $allEntities))
            throw new \Exception("Entity not found: ". $name);

        return true;
    }

    /**
     * Converts a JSON array into a properly configured JSON response. Also
     * makes sure it is compliant with the REST api we have defined.
     * 
     * Note that if the 'status' is not defined, we assume that it is
     * successful.
     * 
     * @param $array The data to encode
     * @param \Symfony\Component\HttpFoundation\Response the Symfony Response
     *   object to return.
     */
    private function getJsonResponse(array $array)
    {
        if (!isset($array['status']))
            $array['status'] = "Success!";

        $response = new Response(json_encode($array, JSON_PRETTY_PRINT));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}