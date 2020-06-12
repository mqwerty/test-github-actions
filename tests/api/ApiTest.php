<?php

/** @noinspection PhpIllegalPsrClassPathInspection PhpUnhandledExceptionInspection */

use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ApiTest extends Unit
{
    protected ApiTester $tester;

    public function testSet(): void
    {
        $this->tester->sendGet('/');
        $this->tester->seeResponseCodeIs(HttpCode::OK);
        $this->tester->seeResponseIsJson();
    }
}