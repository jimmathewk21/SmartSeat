<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\BookingTestCase;

class FrontendContractTest extends BookingTestCase
{
    private function checkPage(TestResponse $response): void
    {
        $response->assertOk();
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($document);
        $this->assertSame(1, $xpath->query('//main[@id="main"]')->length);
        $this->assertSame(1, $xpath->query('//meta[@name="viewport"]')->length);
        $ids = [];
        foreach ($xpath->query('//*[@id]') as $node) {
            $id = $node->getAttribute('id');
            $this->assertArrayNotHasKey($id, $ids, 'Duplicate page ID: '.$id);
            $ids[$id] = true;
        }
        foreach ($xpath->query('//input[not(@type="hidden") and not(@type="submit")] | //select | //textarea') as $control) {
            $labelled = $control->hasAttribute('aria-label') || $control->hasAttribute('aria-labelledby');
            foreach ($xpath->query('//label') as $label) {
                $labelled = $labelled || ($control->hasAttribute('id') && $label->getAttribute('for') === $control->getAttribute('id'));
            }
            for ($parent = $control->parentNode; $parent; $parent = $parent->parentNode) {
                $labelled = $labelled || $parent->nodeName === 'label';
            }
            $this->assertTrue($labelled, 'Unlabelled control: '.$control->getAttribute('name'));
        }
        foreach ($xpath->query('//form') as $form) {
            $method = strtoupper($form->getAttribute('method') ?: 'GET');
            if ($method !== 'GET') {
                $this->assertSame(1, $xpath->query('.//input[@name="_token"]', $form)->length, 'Missing CSRF field');
            }
            foreach ($xpath->query('.//input[@name="_method"]', $form) as $override) {
                $method = strtoupper($override->getAttribute('value'));
            }
            $this->assertNotNull(Route::getRoutes()->match(Request::create($form->getAttribute('action'), $method)));
        }
        foreach ($xpath->query('//a[@href]') as $link) {
            $url = $link->getAttribute('href');
            if (str_starts_with($url, '#')) {
                $this->assertArrayHasKey(substr($url, 1), $ids);
            } elseif (parse_url($url, PHP_URL_HOST) === parse_url(config('app.url'), PHP_URL_HOST)) {
                $this->assertNotNull(Route::getRoutes()->match(Request::create($url, 'GET')));
            }
        }
        foreach ($xpath->query('//link[@rel="stylesheet"]') as $style) {
            $this->assertFileExists(public_path(ltrim(parse_url($style->getAttribute('href'), PHP_URL_PATH), '/')));
        }
    }

    public function test_views_compile_and_all_application_routes_keep_their_security_middleware(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (str_ends_with($file->getFilename(), '.blade.php')) {
                app('blade.compiler')->compile($file->getPathname());
                $this->addToAssertionCount(1);
            }
        }
        $names = [];
        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if ($name === null) {
                continue;
            }
            $this->assertArrayNotHasKey($name, $names, 'Duplicate route name');
            $names[$name] = true;
            if (str_starts_with($name, 'administrator.')) {
                $this->assertContains('auth:web', $route->gatherMiddleware());
                $this->assertContains('can:access-administrator', $route->gatherMiddleware());
            }
            if (str_starts_with($name, 'bookings.') || str_starts_with($name, 'booking-drafts.')) {
                $this->assertContains('auth:web', $route->gatherMiddleware());
            }
            if (array_diff($route->methods(), ['GET', 'HEAD', 'OPTIONS']) && $name !== 'storage.local.upload') {
                $this->assertContains('web', $route->gatherMiddleware());
            }
        }
        foreach (File::allFiles(resource_path('views')) as $file) {
            preg_match_all('/route\(\s*[\'"]([a-zA-Z0-9_.-]+)[\'"]/', $file->getContents(), $matches);
            foreach ($matches[1] as $name) {
                $this->assertArrayHasKey($name, $names, 'Unknown Blade route: '.$name);
            }
        }
    }

    public function test_rendered_pages_have_labels_tokens_valid_routes_and_empty_states(): void
    {
        foreach (['home', 'login', 'register', 'trains.search'] as $name) {
            $this->checkPage($this->get(route($name)));
        }
        $journey = $this->journey();
        $this->actingAs(User::factory()->create(), 'web');
        foreach (['dashboard', 'password.confirm', 'bookings.index'] as $name) {
            $this->checkPage($this->get(route($name)));
        }
        $this->checkPage($this->get(route('bookings.create', $journey['trip'])));
        $key = array_key_last(session('booking_drafts'));
        $this->post(route('booking-drafts.details', $journey['trip']), $this->input() + ['draft_key' => $key])->assertRedirect();
        $this->checkPage($this->get(route('booking-drafts.seats', $key)));
        $this->post(route('booking-drafts.review.store', $key), ['mode' => 'reserve', 'seats' => [$journey['trip']->inventory()->value('id')]])->assertRedirect();
        $this->checkPage($this->get(route('booking-drafts.review', $key)));
        $booking = $this->reserve($journey, auth('web')->user());
        $this->checkPage($this->get(route('bookings.show', $booking)));
        $this->checkPage($this->get(route('bookings.cancellation', $booking)));
        $this->actingAs(User::factory()->administrator()->create(), 'web');
        foreach (['administrator.dashboard', 'administrator.stations.index', 'administrator.trains.index', 'administrator.trips.index', 'administrator.intelligence.index'] as $name) {
            $this->checkPage($this->get(route($name)));
        }
        foreach ([['administrator.trains.stops.index', [$journey['train']]], ['administrator.trains.coaches.index', [$journey['train']]],
            ['administrator.trains.coaches.seats.index', [$journey['train'], $journey['coach']]], ['administrator.trains.trips.index', [$journey['train']]],
            ['administrator.inventory.index', [$journey['trip']]], ['administrator.trains.edit', [$journey['train']]]] as [$name, $parameters]) {
            $this->checkPage($this->get(route($name, $parameters)));
        }
    }
}
