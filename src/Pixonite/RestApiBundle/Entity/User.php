<?php

namespace Pixonite\RestApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    public $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    public $password;

}
