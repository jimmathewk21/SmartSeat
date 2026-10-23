<?php

namespace Tests\Unit\Support;

use App\Support\FrontendLink;
use PHPUnit\Framework\TestCase;

class FrontendLinkTest extends TestCase
{
    public function test_it_converts_internal_absolute_links_to_relative_paths(): void
    {
        $this->assertSame('/contact-us', FrontendLink::normalize('https://events.innosites.in/contact-us'));
        $this->assertSame('/index.html', FrontendLink::normalize('https://events.innosites.in/index.html'));
        $this->assertSame('/trains?from=Mumbai', FrontendLink::normalize('https://events.innosites.in/trains?from=Mumbai'));
        $this->assertSame('https://events.innosites.in/speakers/2?tab=bio', FrontendLink::normalize('https://events.innosites.in/speakers/2?tab=bio'));
    }

    public function test_it_keeps_external_links_unchanged(): void
    {
        $this->assertSame('https://www.linkedin.com/company/example', FrontendLink::normalize('https://www.linkedin.com/company/example'));
    }

    public function test_it_preserves_hash_links(): void
    {
        $this->assertSame('#section-tickets', FrontendLink::normalize('#section-tickets'));
    }
}
