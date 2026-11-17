<?php

use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\CcpaAuditLog\Facades\CcpaAuditLog;
use JeffersonGoncalves\CcpaAuditLog\Models\AuditLog;
use JeffersonGoncalves\Filament\CcpaAuditLog\CcpaAuditLogPlugin;
use JeffersonGoncalves\Filament\CcpaAuditLog\Resources\AuditLogs\AuditLogResource;
use JeffersonGoncalves\Filament\CcpaAuditLog\Resources\AuditLogs\Pages\ListAuditLogs;
use JeffersonGoncalves\Filament\CcpaAuditLog\Resources\AuditLogs\Pages\ViewAuditLog;
use JeffersonGoncalves\Filament\CcpaAuditLog\Tests\Fixtures\TestUser;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = TestUser::create(['name' => 'Admin', 'email' => 'admin@example.com']);
    $this->subject = TestUser::create(['name' => 'Maria', 'email' => 'maria@example.com']);
    $this->actingAs($this->admin);
});

it('lists who did what to whose data', function () {
    $viewed = CcpaAuditLog::record('viewed', $this->subject, ['email'], 'Support ticket');
    $exported = CcpaAuditLog::record('exported', $this->subject, ['email']);

    Livewire::test(ListAuditLogs::class)
        ->assertCanSeeTableRecords([$viewed, $exported])
        ->assertSee('TestUser #'.$this->admin->getKey())
        ->assertSee('TestUser #'.$this->subject->getKey())
        ->assertSee('Viewed')
        ->assertSee('Exported')
        ->filterTable('action', 'exported')
        ->assertCanSeeTableRecords([$exported])
        ->assertCanNotSeeTableRecords([$viewed]);
});

it('shows an entry with its hash', function () {
    $log = CcpaAuditLog::record('viewed', $this->subject, ['email'], 'Support ticket', ['ticket' => 42]);

    Livewire::test(ViewAuditLog::class, ['record' => $log->getKey()])
        ->assertSuccessful()
        ->assertSee('Support ticket')
        ->assertSee($log->hash);
});

it('logs who opens an entry, unless disabled', function () {
    $log = CcpaAuditLog::record('exported', $this->subject);

    Livewire::test(ViewAuditLog::class, ['record' => $log->getKey()]);

    $view = AuditLog::query()->latest('id')->first();
    expect($view)
        ->action->toBe('viewed')
        ->purpose->toBe('Audit log review')
        ->subject->is($log)->toBeTrue()
        ->actor->is($this->admin)->toBeTrue()
        ->and(CcpaAuditLog::verify())->toBeNull();

    CcpaAuditLogPlugin::get()->logViews(false);
    Livewire::test(ViewAuditLog::class, ['record' => $log->getKey()]);

    expect(AuditLog::count())->toBe(2);
});

it('logs who opens the list and who verifies the chain', function () {
    Livewire::test(ListAuditLogs::class)->callAction('verify');

    expect(AuditLog::query()->orderBy('id')->get()->map(fn (AuditLog $log): array => [$log->action, $log->purpose, $log->metadata])->all())
        ->toBe([
            ['viewed', 'Audit log review', null],
            ['verified', 'Audit log review', ['intact' => true, 'broken_at' => null]],
        ])
        ->and(AuditLog::query()->latest('id')->first()->actor->is($this->admin))->toBeTrue();

    CcpaAuditLogPlugin::get()->logViews(false);
    Livewire::test(ListAuditLogs::class)->callAction('verify');

    expect(AuditLog::count())->toBe(2);
});

it('verifies the chain from the panel', function () {
    foreach (range(1, 3) as $i) {
        CcpaAuditLog::record('viewed', $this->subject);
    }

    Livewire::test(ListAuditLogs::class)
        ->callAction('verify')
        ->assertNotified('The audit log is intact.');

    $second = AuditLog::query()->orderBy('id')->skip(1)->first();
    DB::table('ccpa_audit_logs')->where('id', $second->id)->update(['action' => 'exported']);

    Livewire::test(ListAuditLogs::class)
        ->callAction('verify')
        ->assertNotified("The chain breaks at entry #{$second->id}: it or the entry before it was altered, removed or inserted.");
});

it('shows custom actions as they are', function () {
    expect(AuditLogResource::action('printed'))->toBe('printed')
        ->and(AuditLogResource::action('anonymized'))->toBe('Anonymized');
});

it('is read-only and applies the fluent plugin options', function () {
    CcpaAuditLogPlugin::get()->navigationGroup('CCPA')->slug('audit');

    expect(AuditLogResource::canCreate())->toBeFalse()
        ->and(AuditLogResource::getPages())->toHaveKeys(['index', 'view'])->not->toHaveKey('edit')
        ->and(AuditLogResource::getNavigationGroup())->toBe('CCPA')
        ->and(AuditLogResource::getSlug())->toBe('audit');
});
