<?php

namespace PTS\UserRegistrationBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PTS\UserRegistrationBundle\Util;

class UtilTest extends WebTestCase
{
    const PATTERN = '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i';

    /**
     * @test
     */
    public function uuidv4()
    {
        for($i = 0; $i < 10; $i++) {
            $uuidv4 = Util::uuidv4();

            $this->assertEquals(1, preg_match(self::PATTERN, $uuidv4));
        }
    }


}
