<?php
namespace Pixonite\RestApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pixonite\RestApiBundle\Entity\Item;

class LoadDummieItem implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        $item = new Item();
        $item->id = 1; //force the ID to 1 for unit tests
        $item->name = "test item";
        $item->authorUserId = -1;
        $item->url = "http://pixonite.com";

        $em->persist($item);
        $em->flush();
    }
}
