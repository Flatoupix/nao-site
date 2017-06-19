<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Observation;
use AppBundle\Form\OservationType;
use AppBundle\Form\ProfilType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;
use UserBundle\UserBundle;


class BackController extends Controller
{
    /**
     * @Route("/dashboard/profil", name="dashboard_profil")
     * @Method({"GET","POST"})
     */
    public function profilAction(Request $request)
    {

        $user = $this->getUser();

        // Faire le formulaire et l'envoyer à la vue pour l'affichage !

        $form = $this->get('form.factory')->create(ProfilType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /*// Si l'utilisateur a demandé à être naturaliste on modifie la propriété demandeNaturaliste en DateTime
            if ($request->getContent('demandeNaturaliste') === '1')
            {
                $user->setDemandeNaturaliste(new \DateTime('now'));
            } else {
                $user->setDemandeNaturaliste(null);
            }*/

            // On enregistre en bdd
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('notice', 'Votre profil a bien été modifié !');

            return $this->redirectToRoute('dashboard_profil');
        }


        return $this->render('back/profilDashboard.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/dashboard/observations{page}", defaults={"page" = "1" } ,requirements={"id" = "\d+"}, name="dashboard_observations")
     * @Method({"GET","POST"})
     */
    public function observationsAction($page, Request $request)
    {
        $nbObservationsParPage = $this->container->getParameter('front_nb_observations_par_page');
        
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Observation');

        $filtre = $request->query->get('filtre');
        $ordreDeTri = $request->query->get('ordreDeTri');

        $observations = $repository->findAllPagineEtTrie($page, $nbObservationsParPage, $this->getUser(), $filtre, $ordreDeTri);
        
        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($observations) / $nbObservationsParPage),
            'nomRoute' => 'dashboard_observations',
            'paramsRoute' => array()
        );

        return $this->render('back/observationsDashboard.html.twig', array(
            'observations' => $observations,
            'pagination' => $pagination
        ));
    }

    /**
     * @Route("/dashboard/all_observations{page}", defaults={"page" = "1" } ,requirements={"id" = "\d+"}, name="dashboard_all_observations")
     * @Method({"GET","POST"})
     */
    public function allObservationsAction($page, Request $request)
    {
        $nbObservationsParPage = $this->container->getParameter('front_nb_observations_par_page');
        
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Observation');

        $filtre = $request->query->get('filtre');
        $ordreDeTri = $request->query->get('ordreDeTri');

        $observations = $repository->findAllPagineEtTrie($page, $nbObservationsParPage, null, $filtre, $ordreDeTri);
        
        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($observations) / $nbObservationsParPage),
            'nomRoute' => 'dashboard_all_observations',
            'paramsRoute' => array()
        );
        
        return $this->render('back/allObservationsDashboard.html.twig', array(
            'observations' => $observations,
            'pagination' => $pagination
        ));
    }

    /**
     * @Route("/dashboard/observations/{id}/valider", requirements={"id" = "\d+"}, name="validerObservation")
     * @Method({"GET","POST"})
     * @param Observation $observation
     * @ParamConverter()
     */
    public function validerObservationAction(Observation $observation, Request $request)
    {
        $redirect = $request->query->get('redirect');

        $em = $this->getDoctrine()->getManager();
        $observation
            ->setStatus(Observation::VALIDEE)
            ->setPublish(new \Datetime())
            ->setValidateur($this->getUser());
        $em->flush();

        $this->addFlash('notice', "L'observation a bien été validée !");

        if ($redirect === 'all_observations') {
            return $this->redirectToRoute('dashboard_all_observations');
        }

        return $this->redirectToRoute('observation', array(
            "id" => $observation->getId()));

    }

    /**
     * @Route("/dashboard/observations/{id}/signaler", requirements={"id" = "\d+"}, name="signalerObservation")
     * @Method({"GET","POST"})
     * @param Observation $observation
     * @ParamConverter()
     */
    public function signalerObservationAction(Observation $observation, Request $request)
    {
        $redirect = $request->query->get('redirect');

        $em = $this->getDoctrine()->getManager();
        $observation
            ->setStatus(Observation::SIGNALEE)
            ->setValidateur($this->getUser());
        $em->flush();

        $this->addFlash('notice', "L'observation a bien été signalée !");

        if ($redirect === 'all_observations') {
            return $this->redirectToRoute('dashboard_all_observations');
        }

        return $this->redirectToRoute('observation', array(
                "id" => $observation->getId())
        );

    }

    /**
     * @Route("/dashboard/observations/{id}/supprimer", requirements={"id" = "\d+"}, name="supprimerObservation")
     * @Method({"GET","POST"})
     * @param Observation $observation
     * @ParamConverter()
     */
    public function supprimerObservationAction(Observation $observation, Request $request)
    {
        $redirect = $request->query->get('redirect');

        $em = $this->getDoctrine()->getManager();
        $observation
            ->setStatus(Observation::SUPPRIMEE)
            ->setDeleted(new \Datetime())
            ->setValidateur($this->getUser());
        $em->flush();

        $this->addFlash('notice', "L'observation a bien été supprimée !");

        if ($redirect === 'all_observations') {
            return $this->redirectToRoute('dashboard_all_observations');
        }

        if ($redirect === 'observations') {
            return $this->redirectToRoute('dashboard_observations');
        }

        return $this->redirectToRoute('observation', array(
            "id" => $observation->getId()));

    }

    /**
     * @Route("/dashboard/observations/{id}/demandeDeModification", requirements={"id" = "\d+"}, name="demandeDeModificationObservation")
     * @Method({"GET","POST"})
     * @param Observation $observation
     * @ParamConverter()
     */
    public function demandeDeModificationObservationAction(Observation $observation, Request $request)
    {
        $redirect = $request->query->get('redirect');

        $em = $this->getDoctrine()->getManager();
        $observation
            ->setStatus(Observation::A_MODIFIER)
            ->setValidateur($this->getUser());
        $em->flush();


        /*Swift_Message::newInstance*/


        $this->addFlash('notice', "La demande de modification de l'observation a bien été envoyée !");

        if ($redirect === 'all_observations') {
            return $this->redirectToRoute('dashboard_all_observations');
        }

        if ($redirect === 'observations') {
            return $this->redirectToRoute('dashboard_observations');
        }

        return $this->redirectToRoute('observation', array(
            "id" => $observation->getId()));

    }


    /**
     * @Route("/dashboard/all_articles{page}", defaults={"page" = "1" } ,requirements={"id" = "\d+"}, name="dashboard_all_articles")
     * @Method({"GET","POST"})
     */
    public function allArticlesAction(Request $request, $page)
    {
        $nbArticlesParPage = $this->container->getParameter('front_nb_articles_par_page');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Article');

        $filtre = $request->query->get('filtre');
        $ordreDeTri = $request->query->get('ordreDeTri');
        
        $isSeulementMoi = $request->query->has('only-me');

        if ($isSeulementMoi) {
            $articles = $repository->findAllPagineEtTrie($page, $nbArticlesParPage, $this->getUser(),$filtre, $ordreDeTri);
        } else {
            $articles = $repository->findAllPagineEtTrie($page, $nbArticlesParPage, null, $filtre, $ordreDeTri);
        }

        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($articles) / $nbArticlesParPage),
            'nomRoute' => 'dashboard_all_articles',
            'paramsRoute' => array()
        );

        return $this->render('back/allArticlesDashboard.html.twig', array(
            'articles' => $articles,
            'pagination' => $pagination
        ));
    }

    /**
     * @Route("/dashboard/article/{id}/supprimer", requirements={"id" = "\d+"}, name="supprimerArticle")
     * @Method({"GET","POST"})
     * @param Article $article
     * @ParamConverter()
     */
    public function supprimerArticleAction(Article $article, Request $request)
    {
        $redirect = $request->query->get('redirect');

        $em = $this->getDoctrine()->getManager();
        $article
            ->setDeleted(new \Datetime());
        $em->flush();

        $this->addFlash('notice', "L'article a bien été supprimé !");

        if ($redirect === 'all_articles') {
            return $this->redirectToRoute('dashboard_all_articles');
        }

        return $this->redirectToRoute('article', array(
            "id" => $observation->getId()));

    }


    /**
     * @Route("/dashboard/utilisateurs{page}", defaults={"page" = "1" } ,requirements={"id" = "\d+"}, name="dashboard_utilisateurs")
     * @Method({"GET","POST"})
     */
    public function UtilisateursAction($page, Request $request)
    {
        $nbUtilisateursParPage = $this->container->getParameter('front_nb_utilisateurs_par_page');

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('UserBundle:User');

        $filtre = $request->query->get('filtre');
        $ordreDeTri = $request->query->get('ordreDeTri');
        
        $utilisateurs = $repository->findAllPagineEtTrie($page, $nbUtilisateursParPage, $filtre, $ordreDeTri);

        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($utilisateurs) / $nbUtilisateursParPage),
            'nomRoute' => 'dashboard_utilisateurs',
            'paramsRoute' => array()
        );
        
        // var_dump($utilisateurs); die;
        return $this->render('back/utilisateursDashboard.html.twig', array(
            'utilisateurs' => $utilisateurs,
            'pagination' => $pagination
        ));
    }

    /**
     * @Route("/dashboard/detailUtilisateur/{id}", requirements={"id" = "\d+"}, name="detailUtilisateur")
     * @param User $user
     */
    public function voirUtilisateurAction(User $user, Request $request)
    {
        $form = $this->get('form.factory')->create(ProfilType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On enregistre en bdd
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->render('back/detailUtilisateur.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/dashboard/utilisateurs/{id}/supprimer", name="supprimerUtilisateurs")
     * @Method({"GET","POST"})
     * @param User $user
     * @ParamConverter()
     */
    public function SupprimerUtilisateursAction(User $user, Request $request)
    {
        $redirect = $request->query->get('redirect');

        $em = $this->getDoctrine()->getManager();
        $user->setDeleted(new \Datetime());
        $em->flush();

        $this->addFlash('notice', "L'utilisateur a bien été supprimé !");

        if ($redirect === 'utilisateurs') {
            return $this->redirectToRoute('dashboard_utilisateurs');
        }


        return $this->redirectToRoute('', array(
            "id" => $user->getId()));

    }

    /**
     * @Route("/dashboard/utilisateurs/{id}/promouvoir", name="promouvoirUtilisateurs")
     * @Method({"GET","POST"})
     * @param User $user
     * @ParamConverter()
     */
    public function PromouvoirUtilisateursAction(User $user, Request $request)
    {
        $redirect = $request->query->get('redirect');

        $em = $this->getDoctrine()->getManager();

        if (in_array('ROLE_PARTICULIER', $user->getRoles())) {
            $user->setRoles(['ROLE_NATURALISTE']);
        } elseif (in_array('ROLE_NATURALISTE', $user->getRoles())) {
            $user->setRoles(['ROLE_CONTRIBUTEUR']);
        }

        $em->flush();

        $this->addFlash('notice', "L'utilisateur a bien été promu !");

        if ($redirect === 'utilisateurs') {
            return $this->redirectToRoute('dashboard_utilisateurs');
        }

        return $this->redirectToRoute('detailUtilisateur', array(
            "id" => $user->getId(),
        ));

    }

    /**
     * @Route("/dashboard/utilisateurs/{id}/destituer", name="destituerUtilisateurs")
     * @Method({"GET","POST"})
     * @param User $user
     * @ParamConverter()
     */
    public function DestituerUtilisateursAction(User $user, Request $request)
    {
        $redirect = $request->query->get('redirect');

        $em = $this->getDoctrine()->getManager();

        if (in_array('ROLE_NATURALISTE', $user->getRoles())) {
            $user
                ->setDemandeNaturaliste(NULL)
                ->setRoles(['ROLE_PARTICULIER']);
        } elseif (in_array('ROLE_CONTRIBUTEUR', $user->getRoles())) {
            $user
                ->setDemandeContributeur(NULL)
                ->setRoles(['ROLE_NATURALISTE']);
        }

        $em->flush();

        $this->addFlash('notice', "L'utilisateur a bien été rétrogradé !");

        if ($redirect === 'utilisateurs') {
            return $this->redirectToRoute('dashboard_utilisateurs');
        }

        return $this->redirectToRoute('detailUtilisateur', array(
            "id" => $user->getId()));

    }


}
