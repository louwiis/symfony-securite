<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
// is granted function
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(Request $r, EntityManagerInterface $em): Response
    {
        $un_post = new Post();
        $form = $this->createForm(PostType::class, $un_post);

        $form->handleRequest($r);

        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid() && $this->isGranted('ROLE_ADMIN')) {
            $em->persist($un_post);
            $em->flush();

            return $this->redirectToRoute('app_post');
        }

        $posts = $em->getRepository(Post::class)->findAll();
        $reversedPosts = array_reverse($posts);

        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
            'posts' => $reversedPosts,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/edit/{id}', name: 'app_post_edit')]
    public function edit(Request $r, EntityManagerInterface $em, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($r);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_post');
        }

        $edit_form = $this->createForm(PostType::class, $post);

        return $this->render('post/edit.html.twig', [
            'controller_name' => 'PostController',
            'edit_form' => $edit_form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/post/delete/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $r, EntityManagerInterface $em, Post $post): Response
    {
        if ($this->isCsrfTokenValid('post_delete'.$post->getId(), $r->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('app_post');
    }
}
