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
class SampleDataControllerTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        //-- create element test
        $fields = array(
            'data' => "This is a test!",
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/SampleData.json');
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        $this->assertEquals("Success!", $result->response);


        //-- retrieve element test
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/SampleData.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $isHit = false;
        foreach ($allEntries->entities as $obj) {
            if ($obj == $result->entity)
                $isHit = true;
        }
        $this->assertTrue($isHit);

        //-- delete element test
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/SampleData/' . $result->entity->id .'.json');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        $delResponse = json_decode($data);
        curl_close($ch);

        $this->assertEquals("Success!", $delResponse->response);



        //-- retrieve elements and make sure new one is deleted
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://restapi.gdev/api/v1/SampleData.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $allEntries = json_decode(curl_exec($ch));
        curl_close($ch);

        $isHit = false;
        foreach ($allEntries->entities as $obj) {
            if ($obj == $result->entity)
                $isHit = true;
        }
        $this->assertFalse($isHit);
    }
}
