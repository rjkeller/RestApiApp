<?php

namespace Pixonite\RestApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SetType
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SetType
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
     * @ORM\Column(name="description", type="text")
     */
    public $description;

}
