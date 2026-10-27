<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_employees_api_returns_successful_response(): void
    {
        Employee::create([
            'name' => 'Rahul',
            'email' => 'rahul@example.com',
        ]);

        $response = $this->getJson('/api/employees');

        $response->assertOk();

        $response->assertJsonFragment([
            'name' => 'Rahul',
            'email' => 'rahul@example.com',
        ]);
    }


    public function test_employee_can_be_created_successfully(): void
    {
        $response = $this->postJson('/api/employees', [
            'name' => 'Priya',
            'email' => 'priya@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Employee created successfully')
            ->assertJsonPath('data.name', 'Priya')
            ->assertJsonPath('data.email', 'priya@example.com');

        $this->assertDatabaseHas('employees', [
            'name' => 'Priya',
            'email' => 'priya@example.com',
        ]);
    }

    public function test_employee_creation_requires_name_and_email(): void
    {
        $response = $this->postJson('/api/employees', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_employee_creation_requires_valid_email(): void
    {
        $response = $this->postJson('/api/employees', [
            'name' => 'Priya',
            'email' => 'invalid-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_employee_email_must_be_unique(): void
    {
        \App\Models\Employee::create([
            'name' => 'Rahul',
            'email' => 'rahul@example.com',
        ]);

        $response = $this->postJson('/api/employees', [
            'name' => 'Priya',
            'email' => 'rahul@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
