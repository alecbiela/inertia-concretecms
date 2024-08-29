<?php

namespace Inertia\Tests\Testing;

use Inertia\Inertia;
use Inertia\Middleware;
use Inertia\Support\Header;
use Inertia\Tests\TestCase;
use PHPUnit\Framework\AssertionFailedError;
use Concrete\Core\Http\Request;

class AssertableInertiaTest extends TestCase
{
    /** @test */
    public function the_view_is_served_by_inertia(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('foo');
        });
        
        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->assertInertia($response);
    }

    /** @test */
    public function the_view_is_not_served_by_inertia(): void
    {
        // No test endpoint - we're just hitting the site's home page
        $response = $this->processMockRequest();

        $this->assertTrue($response->isOk());

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Not a valid Inertia response.');

        $this->assertInertia($response);
    }

    /** @test */
    public function the_component_matches(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('foo');
        });
        
        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->assertInertia($response, function ($inertia) {
            $inertia->component('foo');
        });
    }

    /** @test */
    public function the_component_does_not_match(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('foo');
        });

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia page component.');

        $this->assertInertia($response, function ($inertia) {
            $inertia->component('bar');
        });
    }

    /** @test */
    public function the_component_exists_on_the_filesystem(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('Stubs/ExamplePage');
        });

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->config->set('inertia.testing.ensure_pages_exist', true);

        $this->assertInertia($response, function ($inertia) {
            $inertia->component('Stubs/ExamplePage');
        });
    }

    /** @test */
    public function the_component_does_not_exist_on_the_filesystem(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('foo');
        });

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->config->set('inertia.testing.ensure_pages_exist', true);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [foo] does not exist.');

        $this->assertInertia($response, function ($inertia) {
            $inertia->component('foo');
        });
    }

    /** @test */
    public function it_can_force_enable_the_component_file_existence(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('foo');
        });

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->config->set('inertia.testing.ensure_pages_exist', false);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [foo] does not exist.');

        $this->assertInertia($response, function ($inertia) {
            $inertia->component('foo', true);
        });
    }

    /** @test */
    public function it_can_force_disable_the_component_file_existence_check(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('foo');
        });

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->config->set('inertia.testing.ensure_pages_exist', true);

        $this->assertInertia($response, function ($inertia) {
            $inertia->component('foo', false);
        });
    }

    /** @test */
    public function the_component_does_not_exist_on_the_filesystem_when_it_does_not_exist_relative_to_any_of_the_given_paths(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('fixtures/ExamplePage');
        });

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->config->set('inertia.testing.ensure_pages_exist', true);
        $this->config->set('inertia.testing.page_paths', [realpath(__DIR__)]);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [fixtures/ExamplePage] does not exist.');

        $this->assertInertia($response, function ($inertia) {
            $inertia->component('fixtures/ExamplePage');
        });
    }

    /** @test */
    public function the_component_does_not_exist_on_the_filesystem_when_it_does_not_have_one_of_the_configured_extensions(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('Stubs/ExamplePage');
        });

        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->config->set('inertia.testing.ensure_pages_exist', true);
        $this->config->set('inertia.testing.page_extensions', ['bin', 'exe', 'svg']);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Inertia page component file [Stubs/ExamplePage] does not exist.');

        $this->assertInertia($response, function ($inertia) {
            $inertia->component('Stubs/ExamplePage');
        });
    }

    /** @test */
    public function the_page_url_matches(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('foo');
        });
        
        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->assertInertia($response, function ($inertia) {
            $inertia->url('/');
        });
    }

    /** @test */
    public function the_page_url_does_not_match(): void
    {
        $this->prepareMockEndpoint(null, [], null, function(){
            return Inertia::render('foo');
        });
        
        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true
        ]);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia page url.');

        $this->assertInertia($response, function ($inertia) {
            $inertia->url('/invalid-page');
        });
    }

    /** @test */
    public function the_asset_version_matches(): void
    {

        $this->prepareMockEndpoint('example-version', [], null, function(){
            return Inertia::render('foo');
        });
        
        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true,
            Header::VERSION => 'example-version'
        ]);

        $this->assertInertia($response, function ($inertia) {
            $inertia->version('example-version');
        });
    }

    /** @test */
    public function the_asset_version_does_not_match(): void
    {

        $this->prepareMockEndpoint('example-version', [], null, function(){
            return Inertia::render('foo');
        });
        
        $response = $this->processMockRequest('/', 'GET', [
            Header::INERTIA => true,
            Header::VERSION => 'example-version'
        ]);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected Inertia asset version.');

        $this->assertInertia($response, function ($inertia) {
            $inertia->version('different-version');
        });
    }
}
