<?php

namespace Tests\Feature;

use App\Actions\CreateAdministratorAccount;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\DatabaseTestCase;

class AdministratorBootstrapTest extends DatabaseTestCase
{
    private function details(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Administrator Test Fixture',
            'email' => 'administrator@example.test',
            'password' => 'LocalBootstrap!42',
            'password_confirmation' => 'LocalBootstrap!42',
        ], $overrides);
    }

    public function test_insert_only_action_creates_hashed_administrator_in_temporary_sqlite(): void
    {
        $user = (new CreateAdministratorAccount)->create($this->details());
        $this->assertTrue($user->isAdministrator());
        $this->assertTrue(Hash::check('LocalBootstrap!42', $user->password));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'administrator']);
        $this->assertDatabaseMissing('users', ['password' => 'LocalBootstrap!42']);
    }

    public function test_existing_passenger_is_never_promoted_or_overwritten(): void
    {
        $user = User::factory()->create(['email' => 'administrator@example.test']);
        $password = $user->password;
        try {
            (new CreateAdministratorAccount)->create($this->details(['email' => 'ADMINISTRATOR@EXAMPLE.TEST']));
            $this->fail('Duplicate email must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('email', $exception->errors());
        }
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'passenger', 'password' => $password]);
        $this->assertDatabaseMissing('users', ['role' => 'administrator']);
    }

    public function test_existing_administrator_is_never_overwritten(): void
    {
        $user = User::factory()->administrator()->create(['email' => 'administrator@example.test']);
        $name = $user->name;
        try {
            (new CreateAdministratorAccount)->create($this->details());
            $this->fail('Duplicate administrator must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('email', $exception->errors());
        }
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => $name]);
    }

    public function test_weak_bootstrap_password_creates_no_account(): void
    {
        try {
            (new CreateAdministratorAccount)->create($this->details(['password' => 'weak', 'password_confirmation' => 'weak']));
            $this->fail('Weak password must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }
        $this->assertDatabaseCount('users', 0);
    }
}
