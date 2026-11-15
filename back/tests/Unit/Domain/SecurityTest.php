<?php

use App\Domain\Exceptions\BusinessRuleException;
use App\Domain\People\Person;
use App\Domain\People\Reader;
use App\Domain\People\StaffMember;
use App\Domain\Security\Permission;
use App\Domain\Security\Role;
use App\Domain\Security\User;

it('reader_and_staff_member_extend_person', function () {
    expect(new Reader('1', 'Ana'))->toBeInstanceOf(Person::class)
        ->and(new StaffMember('2', 'Luis', 'Librarian'))->toBeInstanceOf(Person::class);
});

it('user_has_role_permissions_only_when_active', function () {
    $u = new User(1, 'director', 'hash', true, new StaffMember('2', 'Luis', 'Director'), [
        new Role('Director', [Permission::CAPACITY_VIEW, Permission::LOAN_VIEW]),
    ]);

    expect($u->hasPermission(Permission::CAPACITY_VIEW))->toBeTrue()
        ->and($u->hasPermission(Permission::USER_MANAGE))->toBeFalse();

    $u->deactivate();
    expect($u->hasPermission(Permission::CAPACITY_VIEW))->toBeFalse();
});

it('settings_require_positive_values', function () {
    settings(clearanceCm: 0);
})->throws(BusinessRuleException::class);
