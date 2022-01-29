<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="post")
     */
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $posts = $paginator->paginate(
            $postRepository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('post/index.html.twig', [
           'posts' => $posts
        ]);
    }

    /**
     * @Route("/post/new", name="post_new")
     */
    public function create(Request $request){
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $post->setCreatedAt(new \DateTime());
            $post->setAuthor($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your post was added'
            );
            return $this->redirectToRoute('post');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/{id}", name="post_show")
     */
    public function show(Request $request, PostRepository $postRepository)
    {
        $postId = $request->attributes->get('id');
        $post = $postRepository->find($postId);
        $comment = new Comment();

        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        $this->addComment($commentForm, $comment, $post);
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView()
        ]);
    }

    /**
     * @Route("/post/{id}/edit", name="post_edit")
     */
    public function edit(Post $post, Request $request)
    {
        if ($this->getUser() !== $post->getAuthor()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your post was edited'
            );
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('/post/edit.html.twig', [
            'post' => $post,
            'editForm' => $form->createView()
        ]);
    }

    // Add comment
    private function addComment($commentForm, $comment, $post)
    {
        if($commentForm->isSubmitted() && $commentForm->isValid()){
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setPost($post);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Your comment was added'
            );
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }
    }
}
