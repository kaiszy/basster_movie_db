<?php

namespace Basster\MovieDbBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Basster\MovieDbBundle\Entity\StorageLocation;
use Basster\MovieDbBundle\Form\StorageLocationType;

/**
 * StorageLocation controller.
 *
 * @Route("/storagelocation")
 */
class StorageLocationController extends Controller
{
    /**
     * Lists all StorageLocation entities.
     *
     * @Route("/", name="storagelocation")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('BassterMovieDbBundle:StorageLocation')->findAll();

        return array('entities' => $entities);
    }

    /**
     * Finds and displays a StorageLocation entity.
     *
     * @Route("/{id}/show", name="storagelocation_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BassterMovieDbBundle:StorageLocation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StorageLocation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        );
    }

    /**
     * Displays a form to create a new StorageLocation entity.
     *
     * @Route("/new", name="storagelocation_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new StorageLocation();
        $form   = $this->createForm(new StorageLocationType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Creates a new StorageLocation entity.
     *
     * @Route("/create", name="storagelocation_create")
     * @Method("post")
     * @Template("BassterMovieDbBundle:StorageLocation:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new StorageLocation();
        $request = $this->getRequest();
        $form    = $this->createForm(new StorageLocationType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('storagelocation_show', array('id' => $entity->getId())));
            
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing StorageLocation entity.
     *
     * @Route("/{id}/edit", name="storagelocation_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BassterMovieDbBundle:StorageLocation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StorageLocation entity.');
        }

        $editForm = $this->createForm(new StorageLocationType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing StorageLocation entity.
     *
     * @Route("/{id}/update", name="storagelocation_update")
     * @Method("post")
     * @Template("BassterMovieDbBundle:StorageLocation:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BassterMovieDbBundle:StorageLocation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find StorageLocation entity.');
        }

        $editForm   = $this->createForm(new StorageLocationType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('storagelocation_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a StorageLocation entity.
     *
     * @Route("/{id}/delete", name="storagelocation_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('BassterMovieDbBundle:StorageLocation')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find StorageLocation entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('storagelocation'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
