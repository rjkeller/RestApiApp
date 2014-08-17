<?php

namespace Pixonite\RestApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Item
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Item
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    public $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="authorUserId", type="integer")
     */
    public $authorUserId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    public $creationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text")
     */
    public $url;

    public function __construct()
    {
        $this->creationDate = new \DateTime();
    }

    /**
     * Tell the 'modify' function to only permit the URL to be modified.
     */
    public function validate()
    {
        foreach ($_POST as $key => $value) {
            if ($key != "url")
                return false;
        }
        return true;
    }
}
