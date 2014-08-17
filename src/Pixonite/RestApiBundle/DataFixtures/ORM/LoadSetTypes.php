<?php
namespace Pixonite\RestApiBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pixonite\RestApiBundle\Entity\SetType;

class LoadSetTypes implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        $setType1 = new SetType();
        $setType1->description = "audiophile setup";
        $em->persist($setType1);

        $setType2 = new SetType();
        $setType2->description = "male fashion outfit";
        $em->persist($setType2);

        $setType3 = new SetType();
        $setType3->description = "yarn knitting pattern";
        $em->persist($setType3);

        $em->flush();
    }
}
