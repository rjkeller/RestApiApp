<?php
namespace Pixonite\RestApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pixonite\RestApiBundle\Entity\User;

class LoadUserData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        $user1 = new User();
        $user1->email = "a@aa.com";
        $user1->password = "a1!";
	$em->persist($user1);

	$user2 = new User();
	$user2->email = "b@bb.com";
	$user2->password = "b2@";
	$em->persist($user2);

	$user3 = new User();
	$user3->email = "c@cc.com";
	$user3->password = "c3#";
	$em->persist($user3);

	$em->flush();
    }
}
