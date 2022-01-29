<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column (type="string", length=255)
     */
    private $title;


    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="category")
     */
    private $posts;



    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getPosts()
    {
        return $this->posts;
    }

    public function addPost(Post $post){
        if(!$this->posts->contains($post)){
            $this->posts[] = $post;
            $post->setCategory($this);
        }
        return $this;
    }

    public  function removePost(Post  $post){
        if($this->posts->contains($post)){
            $this->posts->removeElement($post);
            if($post->getCategory() === $this){
                $post->setCategory(null);
            }
        }
        return $this;
    }
}
