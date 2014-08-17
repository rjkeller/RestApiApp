<?php

namespace Pixonite\RestApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Some sample data to store in a MySQL database.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SampleData
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
     * @ORM\Column(name="data", type="string", length=255)
     */
    public $data;
}
