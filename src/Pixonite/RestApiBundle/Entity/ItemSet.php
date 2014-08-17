<?php

namespace Pixonite\RestApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ItemSets
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ItemSet
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
     * @var integer
     *
     * @ORM\Column(name="setTypeId", type="integer")
     */
    public $setTypeId;

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
     * @ORM\Column(name="name", type="string", length=255)
     */
    public $name;

    /**
     * @var array
     *
     * @ORM\Column(name="itemIds", type="array")
     */
    public $itemIds;

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
        if (isset($_POST['itemIds'])) {
            $ids = json_decode($_POST['itemIds']);
            if (count($ids) <= 0)
                return false;
        }
        foreach ($_POST as $key => $value) {
            if ($key != "url" && $key != "itemIds")
                return false;
        }
        return true;
    }
}
