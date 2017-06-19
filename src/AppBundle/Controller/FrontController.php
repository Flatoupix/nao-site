<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Observation;
use AppBundle\Form\ContactType;
use AppBundle\Form\Model\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Method({"GET","POST"})
     */
    public function indexAction(Request $request)
    {
            $page = $this->getDoctrine()->getRepository('AppBundle:Page')->findOneBy(
            	array('nameIdentifier' => 'accueil')          	
            	);

            return $this->render('front/index.html.twig', array(
                'page' => $page,
            ));
    }

    /**
     * @Route("/contact", name="contact")
     * @Method({"GET","POST"})
     */
    public function ContactAction(Request $request)
    {

        $contact = new Contact();

        $form = $this->get('form.factory')->create(ContactType::class, $contact);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $message = \Swift_Message::newInstance()
                    ->setContentType('text/html')//Message en HTML
                    ->setSubject("NAO - Contact de :" . $contact->getNom() ." ".$contact->getPrenom())//Email devient le sujet de mon objet contact
                    ->setFrom($contact->getEmail())// Email de l'expéditeur est le destinataire du mail
                    ->setTo($this->getParameter('mailer_user'))// destinataire du mail
                    ->setBody("Message : " .$contact->getContenu()); // contenu du mail

                $this->get('mailer')->send($message);//Envoi mail

                $this->addFlash('notice', 'Votre message a bien été envoyé !');

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('front/contact.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/mentions", name="mentions")
     */
    public function mentionsAction()
    {

        $page = $this->getDoctrine()->getRepository('AppBundle:Page')->findOneBy(
        	array('nameIdentifier' => 'mentions légales')
        	);

        return $this->render('front/mentions.html.twig', array(
            'page' => $page,
        ));
    }

    /**
     * @Route("/actualites/{page}", requirements={"page" = "\d+"}, defaults={"page" = 1}, name="actualites")
    */
    public function newsAction($page)
    {
        $nbNewsParPage = $this->container->getParameter('front_nb_news_par_page');

    	// On cherche les 4 premiers articles pour les afficher
    	$em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('AppBundle:Article');

        $listArticles = $repository->findAllPagineEtTrie($page, $nbNewsParPage);

        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($listArticles) / $nbNewsParPage),
            'nomRoute' => 'actualites',
            'paramsRoute' => array()
        );


    	return $this->render('front/news.html.twig', array(
    		'listArticles' => $listArticles,
            'pagination' => $pagination
    		));
    }

    /**
     * @Route("/article/{id}", requirements={"id" = "\d+"}, name="article")
     * @param Article $article
     */
    public function voirNewsAction(Article $article)
    {
        // On cherche les 3 premiers articles pour les afficher
        $em = $this->getDoctrine()->getManager();
        $listArticles = $em->getRepository('AppBundle:Article')
            ->findBy(
                array('deleted' => null),
                array('date' => 'desc'),
                3
            );

        return $this->render('front/voirNews.html.twig', array(
            'listArticles' => $listArticles,
            'article'=>$article,
        ));
    }

    /**
     * @Route("/observation/{id}", requirements={"id" = "\d+"}, name="observation")
     * @param Observation $observation
     */
    public function voirObservationAction(Observation $observation)
    {
        // On cherche les 3 premiers observations pour les afficher
        $em = $this->getDoctrine()->getManager();
        $listObservations = $em->getRepository('AppBundle:Observation')
            ->findBy(
                array(),
                array('date' => 'desc'),
                3
            );

        return $this->render('front/voirObservation.html.twig', array(
            'listObservations' => $listObservations,
            'observation'=>$observation,
        ));
    }

    /**
     * @Route("/qui sommes nous", name="about")
     */
    public function aboutAction()
    {

        $page = $this->getDoctrine()->getRepository('AppBundle:Page')->findOneBy(
            array('nameIdentifier' => 'about')
        );

        return $this->render('front/about.html.twig', array(
            'page' => $page,
        ));
    }

    /**
    * @Route("/rechercher", name="map")
    */
    public function mapAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$listObservations = array();

    	// On requête les observations en ajax
    	if ($request->isXmlHttpRequest() ) 
    	{
    		$oiseauName = $request->request->get('oiseauName');
    		$oiseau = $em->getRepository('AppBundle:OiseauTaxref')->findOneBy(array(
    			'nomVern' => $oiseauName
    			));
    		// Problème ici, les 2 fonctions ci-dessous retourne toujours un array vide
    		$listObservations = $em->getRepository('AppBundle:Observation')->findBy(array(
    			'oiseau' => $oiseau
    			));
    		// $listObservations = $em->getRepository('AppBundle:Observation')->findObsvervationForOiseau($oiseau);

    		// $count = count($listObservations);
    		return new JsonResponse($listObservations);
    		// return new JsonResponse($listObservations);
    	}

    	// Liste les noms des oiseaux pour l'autocomplete
    	$listOiseaux = $em->getRepository('AppBundle:OiseauTaxref')->findAll();
    	$listOiseauNames = [];

    	foreach ($listOiseaux as $oiseau) {
    			if($oiseau->getNomVern() ) 
    					$listOiseauNames[] = $oiseau->getNomVern();
    			else 
    					$listOiseauNames[] = $oiseau->getNomValide();
    	}

    	return $this->render('front/map.html.twig', [
    		'observations' => $listObservations,
    		'listOiseauNames' => $listOiseauNames
    		]);
    }

    /**
    * @Route("/observer", name="observer")
    */
    public function observerAction(Request $request)
    {
    	return $this->render('front/observer.html.twig');
    }
}
