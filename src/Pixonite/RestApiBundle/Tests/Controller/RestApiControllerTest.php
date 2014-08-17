<?php

namespace Pixonite\RestApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * UNIT TESTS FOR PIXONITE REST API
 * 
 * Basically do some basic Curl calls as a sanity check against the API.
 * 
 * @author R.J. Keller <rjkeller-fun@pixonite.com>
 */
class RestApiControllerTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        //-- test authentication system
        $badAuthHeader = ['Authorization: Basic '. base64_encode("bad:bad")];
        $goodAuthHeader = ['Authorization: Basic '. base64_encode("a@aa.com:a1!")];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $badAuthHeader);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertEquals("Access Denied", $result->exception_message);

        //-- create element test
        $fields = array(
            'name' => "This is a test!",
            'url' => 'http://yay',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);

        $result = json_decode(curl_exec($ch));
        curl_close($ch);
        $this->assertEquals("Success!", $result->status);

        //-- try to delete an entity that doesn't belong to this user.
        // Item #1 in the DB is a dummie item set up by fixtures that isn't deletable by any user.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item/1.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);

        $data = curl_exec($ch);
        $delResponse = json_decode($data);
        curl_close($ch);

        $this->assertNotEquals("Success!", $delResponse->status);


        //-- retrieve element test
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $isHit = false;
        $itemId = null;
        foreach ($allEntries->entities as $obj) {
            if ($obj == $result->entity) {
                $itemId = $result->entity->id;
                $isHit = true;
            }
        }
        $this->assertTrue($isHit);

        //-- retrieve by user ID test
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item.json?authorEmail='. urlencode('a@aa.com'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertTrue(count($allEntries->entities) > 0);

        //-- retrieve last N results
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item.json?authorEmail='. urlencode('a@aa.com') ."&firstN=2");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertTrue(count($allEntries->entities) <= 2);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item.json?authorEmail='. urlencode('a@aa.com') ."&lastN=2");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertTrue(count($allEntries->entities) <= 2);


        //-- update element test
        // 1) Update element
        $fields = array(
            'name' => "This is a test!",
            'url' => 'http://yay2',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item/'. $itemId .'.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);

        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertEquals("Success!", $result->status);

        // 2) Make sure update is applied
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $isHit = false;
        $element = null;
        foreach ($allEntries->entities as $obj) {
            if ($obj == $result->entity) {
                $isHit = true;
                $element = $obj;
            }
        }

        $this->assertTrue($isHit);
        $this->assertTrue($element != null);
        $this->assertEquals("http://yay2", $element->url);

        //-- delete element test
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item/' . $result->entity->id .'.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);

        $data = curl_exec($ch);
        $delResponse = json_decode($data);
        curl_close($ch);

        $this->assertEquals("Success!", $delResponse->status);



        //-- retrieve elements and make sure new one is deleted
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/Item.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $isHit = false;
        foreach ($allEntries->entities as $obj) {
            if ($obj == $result->entity)
                $isHit = true;
        }
        $this->assertFalse($isHit);



        //-- create item set
        $fields = array(
            'setTypeId' => 2,
            'name' => 'woah12',
            'itemIds' => json_encode([1,2]),
            'url' => 'http://yay',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/ItemSet.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);

        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertEquals("Success!", $result->status);

        $fields = array(
            'setTypeId' => 1,
            'name' => 'woah1',
            'itemIds' => json_encode([1]),
            'url' => 'http://yay1',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/ItemSet.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);

        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertEquals("Success!", $result->status);

        $fields = array(
            'setTypeId' => 1,
            'name' => 'woah2',
            'itemIds' => json_encode([2]),
            'url' => 'http://yay2',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/ItemSet.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);

        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertEquals("Success!", $result->status);

        $fields = array(
            'setTypeId' => 1,
            'name' => 'woah',
            'itemIds' => json_encode([]),
            'url' => 'http://yay',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/ItemSet.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);

        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertEquals("Success!", $result->status);


        //-- try some queries
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/ItemSet.json?anyItemIds='. urlencode(json_encode([1,2])));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        foreach ($allEntries->entities as $obj) {
            $this->assertTrue(
                in_array(1, $obj->itemIds) ||
                in_array(2, $obj->itemIds)
            );
        }

        //-- try some queries
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/ItemSet.json?setTypeId=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $goodAuthHeader);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertTrue(count($allEntries->entities) > 0);
        foreach ($allEntries->entities as $obj) {
            $this->assertTrue($obj->setTypeId == 1);
        }


    }
}
