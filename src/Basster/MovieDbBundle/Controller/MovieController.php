<?php

namespace Basster\MovieDbBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Basster\MovieDbBundle\Entity\Movie;
use Basster\MovieDbBundle\Form\MovieType;
use Basster\TmdbBundle\Entity\TMDb;

/**
 * Movie controller.
 *
 * @Route("/")
 */
class MovieController extends Controller {

    private $tmdbKey = "60fefc9681ecc32ef7ebc8a431c247da";
    private $tmdb = null;

    /**
     * Lists all Movie entities.
     *
     * @Route("/", name="movie", defaults={"capital" = null, "search" = null })
     * @Route("/list/{capital}", name="movie_list_alphabetical", defaults={"capital" = null, "query" = null })
     * @Route("/search/{query}", name="movie_list_search", defaults={"capital" = null, "query" = "movie" })
     * @Template()
     */
    public function indexAction($capital = null, $query = null) {
        $em = $this->getDoctrine()->getEntityManager();

        $searchForm = $this->createFormBuilder()
                ->add('query', 'search')
                ->getForm();
        
        $dql = "SELECT m, sl
                FROM BassterMovieDbBundle:Movie m
                JOIN m.storageLocation sl";

        if ($query == 'movie') {
            $q = $this->getRequest()->get($searchForm->getName());
            $searchForm->setData($q);
            
            $dql .= " WHERE m.title LIKE '%" . $q['query'] . "%' ";            
        } 
        else {

            if (!is_null($capital)) {
                if ($capital == '0-9') {
                    $dql .= " WHERE SUBSTRING(m.title, 1, 1) BETWEEN '0' AND '9'"; //IN ('0','1','2','3','4','5','6','7','8','9')";
                } else {
                    $dql .= " WHERE m.title LIKE '" . $capital . "%' ";
                }
            }
            
        }
        
        $dql .= " ORDER BY m.title ASC";

        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $this->get('request')->query->get('page', 1)/* page number */, 10/* limit per page */
        );

        return array(
            'pagination' => $pagination,
            'alpha' => $this->getNavAlphabet(),
            'totalCount' => $em->getRepository('BassterMovieDbBundle:Movie')->countAll(),
            'searchForm' => $searchForm->createView()
        );
    }

    /**
     * Ermittelt die Menge aller Filme nach Anfangsbuchstaben für den Listenfilter
     * 
     * @return type array
     */
    private function getNavAlphabet() {
        $conn = $this->get('database_connection');

        $dql2 = "SELECT LEFT(title, 1) as c, COUNT(*) as cnt 
                FROM movie
                GROUP BY LEFT(title, 1)
                ORDER BY 1
                ";

        $alpha = $conn->fetchAll($dql2);

        $alphabet = array();
        $alphabet['0-9'] = 0;
        for ($i = 65; $i <= 90; $i++) {
            $alphabet[chr($i)] = 0;
        }

        foreach ($alpha as $a) {

            $key = $a['c'];
            $value = $a['cnt'];

            if (is_numeric($key)) {
                $alphabet['0-9'] += $value;
            } else {
                $alphabet[$key] = $value;
            }
        }

        return $alphabet;
    }

    /**
     * Finds and displays a Movie entity.
     *
     * @Route("/{slug}/show", name="movie_show")
     * @Template()
     */
    public function showAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = new Movie();

        $entity = $em->getRepository('BassterMovieDbBundle:Movie')->findOneBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Movie entity.');
        }

        $deleteForm = $this->createDeleteForm($slug);

        $movieDbEntry = json_decode($this->getTmdb()->getMovie($entity->getMovieDbLink()));

        return array(
            'entity' => $entity,
            'dbEntry' => $movieDbEntry[0],
            'delete_form' => $deleteForm->createView(),);
    }

    /**
     * Displays a form to create a new Movie entity.
     *
     * @Route("/new", name="movie_new")
     * @Template()
     */
    public function newAction() {
        $entity = new Movie();
        $form = $this->createForm(new MovieType(), $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Creates a new Movie entity.
     *
     * @Route("/create", name="movie_create")
     * @Method("post")
     * @Template("BassterMovieDbBundle:Movie:new.html.twig")
     */
    public function createAction() {
        $entity = new Movie();
        $request = $this->getRequest();
        $form = $this->createForm(new MovieType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('movie_show', array('slug' => $entity->getSlug())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }

    /**
     * Displays a form to edit an existing Movie entity.
     *
     * @Route("/{slug}/edit", name="movie_edit")
     * @Template()
     */
    public function editAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BassterMovieDbBundle:Movie')->findOneBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Movie entity.');
        }

        $editForm = $this->createForm(new MovieType(), $entity);
        $deleteForm = $this->createDeleteForm($slug);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Movie entity.
     *
     * @Route("/{slug}/update", name="movie_update")
     * @Method("post")
     * @Template("BassterMovieDbBundle:Movie:edit.html.twig")
     */
    public function updateAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BassterMovieDbBundle:Movie')->findOneBySlug($slug);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Movie entity.');
        }

        $editForm = $this->createForm(new MovieType(), $entity);
        $deleteForm = $this->createDeleteForm($slug);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('movie_edit', array('slug' => $slug)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Movie entity.
     *
     * @Route("/{slug}/delete", name="movie_delete")
     * @Method("post")
     */
    public function deleteAction($slug) {
        $form = $this->createDeleteForm($slug);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('BassterMovieDbBundle:Movie')->findOneBySlug($slug);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Movie entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('movie'));
    }

    private function createDeleteForm($slug) {
        return $this->createFormBuilder(array('slug' => $slug))
                        ->add('slug', 'hidden')
                        ->getForm()
        ;
    }

    /**
     *
     * @return TMDb\Api\TMDb 
     */
    private function getTmdb() {
        if (is_null($this->tmdb)) {
            $this->tmdb = new TMDb($this->tmdbKey);
            $this->tmdb->setLang('de-DE');
        }

        return $this->tmdb;
    }

    public function searchTmdbAction($title, $autocomplete = false) {

        $tmdb = $this->getTmdb();

        //$result = $tmdb->searchMovie($title);
        $result = $tmdb->browseMovies('title', 'asc', array(
            'query' => $title
                ));
        
        if ($autocomplete == true && $result != '["Nothing found."]') {
            
            $movies = array();
            $results = json_decode($result);
            
            foreach ($results as $res) {
                $movie = array();
                $movie['key'] = $res->id;
                $movie['value'] = $res->name;
                $movies[] = $movie;
            }
            
            $result = $movies;
        }

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    
    public function searchMovieNameAction($part) {
        
        $em = $this->getDoctrine()->getEntityManager();
        $movies = $em->getRepository('BassterMovieDbBundle:Movie')->findByNamePart($part);
        
        $response = new Response(json_encode($movies));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
