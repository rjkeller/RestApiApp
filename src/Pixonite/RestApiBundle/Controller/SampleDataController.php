<?php

namespace Pixonite\RestApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pixonite\RestApiBundle\Entity\Item;
use Pixonite\RestApiBundle\Entity\ItemSet;
use Pixonite\RestApiBundle\Form\ItemType;
use Pixonite\RestApiBundle\Form\ItemSetType;

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
        $em = $this->getDoctrine()->getManager();

        //-- Do some validation first
        $this->getRequest()->setRequestFormat("json");
        $this->checkEntityName($entityName);
        $this->checkAuthentication();

        $parameters = array();
        $dql = "
            SELECT
                e
            FROM
                PixoniteRestApiBundle:". $entityName ." e
            WHERE
                1 = 1
        ";
        $sortBy = " e.creationDate DESC";
        if (isset($_GET['lastN'])) {
            $sortBy = "e.creationDate ASC";
        }

        $entities = array();

        if (isset($_GET['name'])) {
            $dql .= " AND e.name = :name ";
            $parameters['name'] = $_GET['name'];

        }
        elseif (isset($_GET['setTypeId'])) {
            $dql .= " AND e.setTypeId = :setTypeId ";
            $parameters['setTypeId'] = $_GET['setTypeId'];
        }
        elseif (isset($_GET['authorEmail'])) {
            $user = $em->getRepository("PixoniteRestApiBundle:User")
                ->findOneByEmail($_GET['authorEmail']);

            $dql .= " AND e.authorUserId = :authorUserId ";
            $parameters['authorUserId'] = $user->id;
        }

        $q = $em->createQuery($dql . " ORDER BY ". $sortBy);
        foreach ($parameters as $key => $value)
            $q->setParameter($key, $value);

        if (isset($_GET['lastN']))
            $q->setMaxResults($_GET['lastN']);
        if (isset($_GET['firstN']))
            $q->setMaxResults($_GET['firstN']);

        $entities = $q->getResult();

        if (isset($_GET['anyItemIds'])) {
            $ids = json_decode($_GET['anyItemIds']);
            foreach ($entities as $key => $value) {
                if (count(array_intersect($ids, $value->itemIds)) <= 0) {
                    unset($entities[$key]);
                }
            }
            $entities = array_values($entities);
        }

        if (isset($_GET['allItemIds'])) {
            $ids = json_decode($_GET['allItemIds']);
            foreach ($entities as $key => $value) {
                if ($value->itemIds == null)
                    $value->itemIds = array();
                if (count(array_diff($ids, $value->itemIds)) != 0) {
                    unset($entities[$key]);
                }
            }
            $entities = array_values($entities);
        }

        return $this->getJsonResponse(['entities' => $entities]);
    }

    /**
     * Creates or modifies a new SampleData entity.
     *
     * @Route("Item.json", name="api_sample-data_create", defaults={"id" = null})
     * @Route("Item/{id}.json", name="api_sample-data_update")
     * @Method("POST")
     */
    public function createAction($id = null)
    {
        $em = $this->get('doctrine')->getManager();

        //-- Do some validation first
        $this->getRequest()->setRequestFormat("json");
        $user = $this->checkAuthentication();


        $request = $this->getRequest();

        $entity = new Item();

        //if this is an element update, attempt to load the element
        if ($id != null) {
            $entity = $em->getRepository('PixoniteRestApiBundle:Item')->findOneBy([
                'id' => $id,
                'authorUserId' => $user->id,
                ]);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ID or access is denied.');
            }
        }
        //if we're creating an item, then initialize some defaults
        else {
            $entity->authorUserId = $user->id;
        }

        $ids = null;
        $form = $this->createCreateForm($entity, new ItemType());
        $form->submit($_POST, false);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->getJsonResponse(["entity" => $entity]);
        }

        return $this->getJsonResponse([
            'status' => 'failed',
            'entity' => $entity,
            'errors' => $form->getErrors()
            ]);
    }

    /**
     * Creates or modifies a new SampleData entity.
     *
     * @Route("ItemSet.json", name="api_item-set_create", defaults={"id" = null})
     * @Route("ItemSet/{id}.json", name="api_item-set_update")
     * @Method("POST")
     */
    public function createItemSetAction($id = null)
    {
        $em = $this->get('doctrine')->getManager();

        //-- Do some validation first
        $this->getRequest()->setRequestFormat("json");
        $user = $this->checkAuthentication();



        $request = $this->getRequest();

        $entity = new ItemSet();

        //if this is an element update, attempt to load the element
        if ($id != null) {
            $entity = $em->getRepository('PixoniteRestApiBundle:ItemSet')->findOneBy([
                'id' => $id,
                'authorUserId' => $user->id,
                ]);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ID or access is denied.');
            }
        }
        //if we're creating an item, then initialize some defaults
        else {
            $entity->authorUserId = $user->id;
        }

        //--ICKY HACK APPROACHING--
        //this hacks around the fact that Symfony forms does a crappy job
        //handling arrays. So we basically store the POST data for ItemIDs,
        //and omit it from the form handling system.
        $ids = null;
        $_POST['itemIds'] = array_values(json_decode($_POST['itemIds']));
        $ids = $_POST['itemIds'];
        unset($_POST['itemIds']);

        $form = $this->createCreateForm($entity, new ItemSetType());
        $form->submit($_POST, false);

        if ($form->isValid()) {

            $entity->itemIds = $ids;
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->getJsonResponse(["entity" => $entity]);
        }

        return $this->getJsonResponse([
            'status' => 'failed',
            'entity' => $entity,
            'errors' => (string) $form->getErrors(true, false),
            ]);
    }

    /**
     * Finds and displays an entity
     *
     * @Route("{entityName}/{id}.json", name="api_sample-data_show")
     * @Method("GET")
     */
    public function showAction($entityName, $id)
    {
        //-- Do some validation first
        $this->getRequest()->setRequestFormat("json");
        $this->checkEntityName($entityName);
        $this->checkAuthentication();


        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PixoniteRestApiBundle:'. $entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ID or access is denied.');
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
        $this->getRequest()->setRequestFormat("json");
        $this->checkEntityName($entityName);
        $user = $this->checkAuthentication();


        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('PixoniteRestApiBundle:'. $entityName)->findOneBy([
            'id' => $id,
            'authorUserId' => $user->id,
            ]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SampleData entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->getJsonResponse(["id" => $id]);
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
        /*
         * Commenting out for now the ability to auto detect this list.
        $em = $this->getDoctrine()->getManager();
        $allMetadata = $em->getMetadataFactory()->getAllMetadata();
        $allEntities = [];

        foreach ($allMetadata as $entityMetaData) {
            $classNameInfo = explode("\\", $entityMetaData->getName());
            $allEntities[] = array_pop($classNameInfo);
        }
        */
        $allEntities = ['Item', 'ItemSet'];

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

    /**
     * Checks if the authentication headers corresond to a valid user. If not,
     * an exception is thrown.
     * 
     * @throws \Exception When authentication fails.
     * @return The user that is currently logged in.
     */
    private function checkAuthentication()
    {
        $em = $this->get('doctrine')->getManager();
        $allHeaders = getallheaders();
        foreach ($allHeaders as $name => $value) {
            //if we found the authentication header, 
            if ($name == "Authorization") {
                //decode the authentication information in the format:
                //    Authentication: Basic [Base64Encoded(Username:Password)
                $parsedData = explode(" ", $value);
                $authInfo = explode(":", base64_decode(array_pop($parsedData)));

                //validate the user
                $user = $em->getRepository("PixoniteRestApiBundle:User")
                    ->findOneBy([
                        'email' => $authInfo[0],
                        'password' => $authInfo[1],
                    ]);

                //if a user was found with good credentials, then authentication was successful!
                if ($user != null)
                    return $user;
            }
        }

        //if there is no authentication headers or auth failed.
        throw new \Exception("Access Denied");
    }
}
