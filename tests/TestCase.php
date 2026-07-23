<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // ponytail: declared so Pest's beforeEach()-assigned $this->service (varies per test file) resolves for IDE static analysis
    public mixed $service = null;
}
