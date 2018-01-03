<?php


namespace Blogger\BlogBundle\Controller;

use Blogger\BlogBundle\Entity\Enq;
use Blogger\BlogBundle\Entity\Blog;
use Blogger\BlogBundle\Form\EnqType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Blogger\BlogBundle\Repository\BlogRepository;


class PageController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()
            ->getEntityManager();

        $blogs = $em->getRepository('BlogBundle:Blog')
            ->getLatestBlogs();

        return $this->render('BlogBundle:Page:index.html.twig', array(
            'blogs' => $blogs
        ));
    }

    public function aboutAction()
    {
        return $this->render('BlogBundle:Page:about.html.twig');
    }

    public function contactAction(Request $request)
    {
        // 1) build the form
        $enq = new Enq();
        $form = $this->createForm(EnqType::class, $enq);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = \Swift_Message::newInstance()
                ->setSubject('Contact enquiry from symblog')
                ->setFrom('ibnimedahmed@gmail.com')
                ->setTo($this->container->getParameter('blogger_blog.emails.contact_email'))
                ->setBody($this->renderView('BlogBundle:Page:contactEmail.txt.twig', array('enq' => $enq)));
            $this->get('mailer')->send($message);

            $this->addFlash('blogger-notice', 'Your contact enquiry was successfully sent. Thank you!');

            return $this->redirect($this->generateUrl('BloggerBlogBundle_contact'));
        }

        return $this->render('BlogBundle:Page:contact.html.twig', array(
            'form' => $form->createView()
        ));

    }

    public function sidebarAction()
    {
        $em = $this->getDoctrine()
            ->getEntityManager();

        $tags = $em->getRepository('BlogBundle:Blog')
            ->getTags();

        $tagWeights = $em->getRepository('BlogBundle:Blog')
            ->getTagWeights($tags);

        $commentLimit   = $this->container
            ->getParameter('blogger_blog.comments.latest_comment_limit');
        $latestComments = $em->getRepository('BlogBundle:Comment')
            ->getLatestComments($commentLimit);

        return $this->render('BlogBundle:Page:sidebar.html.twig', array(
            'latestComments'    => $latestComments,
            'tags'              => $tagWeights
        ));
    }
}