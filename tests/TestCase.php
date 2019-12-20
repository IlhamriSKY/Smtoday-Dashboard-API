<?php

namespace Tests;

use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Assert that session has provided error.
     * @param $message
     */
    protected function assertSessionHasError($message)
    {
        $errors = session()->get('errors')->getBag('default');

        $this->assertEquals($message, $errors->first());
    }

    /**
     * Assert that session has provided error.
     * @param $message
     */
    protected function assertSessionHasSuccess($message)
    {
        $this->assertEquals($message, session()->get('success'));
    }

    /**
     * Create and log in an admin user.
     * @return TestCase
     */
    protected function beAdmin()
    {
        return $this->actingAsAdmin();
    }

    /**
     * Create and log in an admin user.
     * @return TestCase
     */
    protected function actingAsAdmin()
    {
        $this->be(UserFactory::admin()->create());

        return $this;
    }
}
