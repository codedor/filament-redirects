<?php

use Codedor\FilamentRedirects\Filament\RedirectResource\Pages\ManageRedirects;
use Codedor\FilamentRedirects\Http\Middleware\Redirects;
use Codedor\FilamentRedirects\Models\Redirect;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->redirects = Redirect::factory()->createMany([
        [
            'from' => 'http://example.com/one',
            'to' => 'http://example.com/two',
        ],
        [
            'from' => 'https://example.com/foo',
            'to' => 'https://example.com/bar',
        ],
    ]);

    $this->actingAs(\Codedor\FilamentRedirects\Tests\Fixtures\Models\User::factory()->create());
});

it('can list redirects', function () {
    livewire(ManageRedirects::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($this->redirects);
});

it('has an edit action', function () {
    livewire(ManageRedirects::class)
        ->assertTableActionExists('edit');
});

it('has a delete action', function () {
    livewire(ManageRedirects::class)
        ->assertTableActionExists('delete')
        ->assertTableBulkActionExists('delete');
});

it('has an import action that can throw an error', function () {
    livewire(ManageRedirects::class)
        ->assertActionExists('import')
        ->callAction('import');

    Notification::assertNotified('Something went wrong during the import');
});

it('has an import action that can truncate the table', function () {
    Storage::disk('local')->put(
        'import_redirects.xlsx',
        file_get_contents(__DIR__ . '/../../../../Fixtures/import_redirects.xlsx', 'import_redirects.xlsx')
    );

    livewire(ManageRedirects::class)
        ->assertActionExists('import')
        ->callAction('import', ['file' => ['file' => 'import_redirects.xlsx'],
        ]);

    Notification::assertNotified(
        Notification::make()
            ->success()
            ->title('Import was successful')
    );

    $this->assertDatabaseCount(Redirect::class, 3);
    $this->assertDatabaseHas(Redirect::class, [
        'from' => 'https://example.com/from',
        'to' => 'https://example.com/to',
        'status' => 301,
    ]);
});

it('can create a redirect with validation errors', function () {
    livewire(ManageRedirects::class)
        ->assertActionExists('create')
        ->callAction('create', [
            'from' => '/from',
        ])
        ->assertHasActionErrors(['to' => 'required']);
});

it('can create a redirect', function () {
    livewire(ManageRedirects::class)
        ->assertActionExists('create')
        ->callAction('create', [
            'from' => 'https://example.com/from',
            'to' => 'https://example.com/to',
            'status' => 410,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseCount(Redirect::class, 3);
    $this->assertDatabaseHas(Redirect::class, [
        'from' => 'https://example.com/from',
        'to' => 'https://example.com/to',
        'status' => 410,
    ]);
});

it('can create a redirect with validation errors for invalid URLs', function () {
    config([
        'filament-redirects.input-validation' => ['url', 'required'],
    ]);

    livewire(ManageRedirects::class)
        ->assertActionExists('create')
        ->callAction('create', [
            'from' => 'invalid-url',
            'to' => 'another-invalid-url',
        ])
        ->assertHasActionErrors(['from' => 'url', 'to' => 'url']);
});

it('can create a redirect with different protocols', function () {
    livewire(ManageRedirects::class)
        ->assertActionExists('create')
        ->callAction('create', [
            'from' => 'http://example.com/old',
            'to' => 'https://example.com/new',
            'status' => 301,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Redirect::class, [
        'from' => 'http://example.com/old',
        'to' => 'https://example.com/new',
        'status' => 301,
    ]);
});

it('can create a redirect with relative url', function () {
    livewire(ManageRedirects::class)
        ->assertActionExists('create')
        ->callAction('create', [
            'from' => '/old',
            'to' => '/new',
            'status' => 301,
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(Redirect::class, [
        'from' => '/old',
        'to' => '/new',
        'status' => 301,
    ]);
});

it('redirects correctly regardless of protocol', function ($fromProtocol, $toProtocol) {
    $redirect = Redirect::create([
        'from' => "{$fromProtocol}://example.com/test",
        'to' => "{$toProtocol}://example.com/result",
        'status' => 301,
        'online' => true,
    ]);

    $request = Request::create($redirect->from);

    $middleware = new Redirects;
    $response = $middleware->handle($request, fn () => response('This is a secret place'));

    expect($response->getStatusCode())
        ->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe($redirect->to);

    $redirect->from = $fromProtocol === 'http'
        ? 'https://example.com/test'
        : 'http://example.com/test';

    $request = Request::create($redirect->from);

    $response = $middleware->handle($request, fn () => response('This is a secret place'));

    expect($response->getStatusCode())
        ->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe($redirect->to);
})
    ->with([
        'http to http' => ['http', 'http'],
        'http to https' => ['http', 'https'],
        'https to http' => ['https', 'http'],
        'https to https' => ['https', 'https'],
    ]);

it('redirects correctly with query parameters', function () {
    $redirect = Redirect::create([
        'from' => 'http://example.com/query',
        'to' => 'https://example.com/result',
        'status' => 301,
        'pass_query_string' => true,
        'online' => true,
    ]);

    $query = '?param=value';
    $request = Request::create("{$redirect->from}{$query}", 'GET');

    $middleware = new Redirects;
    $response = $middleware->handle($request, fn () => response('This is a secret place'));

    expect($response->getStatusCode())
        ->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe("{$redirect->to}{$query}");
});

it('handles wildcard redirects', function () {
    $redirect = Redirect::create([
        'from' => 'http://example.com/wildcard*',
        'to' => 'https://example.com/result',
        'status' => 301,
        'online' => true,
    ]);

    $request = Request::create('http://example.com/wildcard-test', 'GET');

    $middleware = new Redirects;
    $response = $middleware->handle($request, fn () => response('This is a secret place'));

    expect($response->getStatusCode())
        ->toBe(301)
        ->and($response->headers->get('Location'))
        ->toBe($redirect->to);
});
