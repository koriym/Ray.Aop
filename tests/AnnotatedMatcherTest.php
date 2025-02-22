<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;
use Ray\Aop\Annotation\FakeMarker;

use function serialize;
use function unserialize;

class AnnotatedMatcherTest extends TestCase
{
    /**
     * Tests that AnnotatedMatcher can be serialized and unserialized
     * while maintaining its functionality
     */
    public function testAnnotatedMatcherSerialization(): void
    {
        $matcher = new AnnotatedMatcher('annotatedWith', [FakeMarker::class]);
        $this->assertInstanceOf(AnnotatedMatcher::class, unserialize(serialize($matcher)));
    }
}
