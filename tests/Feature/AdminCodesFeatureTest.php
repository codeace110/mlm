<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AdminCode;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class AdminCodesFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $distributor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'is_admin' => true,
            'email' => 'admin@test.com'
        ]);

        // Create regular distributor
        $this->distributor = User::factory()->create([
            'is_admin' => false,
            'email' => 'distributor@test.com'
        ]);
    }

    public function test_admin_can_generate_batch_of_codes()
    {
        // Action: admin generates batch of codes
        $response = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 50,
                'batch_name' => 'Test Batch 2024'
            ]);

        // Assertions
        $response->assertRedirect(route('admin.admin_codes.index'));
        $response->assertSessionHas('success');

        // Verify codes were created
        $this->assertDatabaseCount('admin_codes', 50);
        $this->assertDatabaseHas('admin_codes', [
            'batch_name' => 'Test Batch 2024',
            'status' => 'issued'
        ]);

        // Verify all codes are unique
        $codes = AdminCode::where('batch_name', 'Test Batch 2024')->pluck('code');
        $this->assertCount(50, $codes->unique());
    }

    public function test_admin_can_issue_code_to_distributor()
    {
        // Setup: create issued code
        $code = AdminCode::factory()->create([
            'status' => 'issued',
            'distributor_id' => null
        ]);

        // Action: admin issues code to distributor
        $response = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.issue', $code), [
                'distributor_id' => $this->distributor->id
            ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify code was issued
        $code->refresh();
        $this->assertEquals($this->distributor->id, $code->distributor_id);
        $this->assertEquals('unused', $code->status);
    }

    public function test_admin_can_revoke_issued_code()
    {
        // Setup: create unused code assigned to distributor
        $code = AdminCode::factory()->create([
            'status' => 'unused',
            'distributor_id' => $this->distributor->id
        ]);

        // Action: admin revokes code
        $response = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.revoke', $code));

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify code was revoked
        $code->refresh();
        $this->assertNull($code->distributor_id);
        $this->assertEquals('issued', $code->status);
    }

    public function test_admin_cannot_revoke_used_code()
    {
        // Setup: create used code
        $usedByUser = User::factory()->create();
        $code = AdminCode::factory()->create([
            'status' => 'used',
            'distributor_id' => $this->distributor->id,
            'used_by_user_id' => $usedByUser->id,
            'used_at' => now()
        ]);

        // Action: admin tries to revoke used code
        $response = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.revoke', $code));

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot revoke a used code.');

        // Verify code was not changed
        $code->refresh();
        $this->assertEquals('used', $code->status);
        $this->assertEquals($this->distributor->id, $code->distributor_id);
    }

    public function test_admin_can_assign_code_to_distributor()
    {
        // Setup: create issued code
        $code = AdminCode::factory()->create([
            'status' => 'issued',
            'distributor_id' => null
        ]);

        // Action: admin assigns code to distributor
        $response = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.assign', $code), [
                'distributor_id' => $this->distributor->id
            ]);

        // Assertions
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify code was assigned
        $code->refresh();
        $this->assertEquals($this->distributor->id, $code->distributor_id);
        $this->assertEquals('unused', $code->status);
    }

    public function test_admin_can_download_csv_of_all_codes()
    {
        // Setup: create some codes
        $codes = AdminCode::factory()->count(10)->create([
            'status' => 'issued'
        ]);

        // Action: admin downloads CSV
        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.download'));

        // Assertions
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="all_admin_codes_*.csv"');

        // Verify CSV content
        $content = $response->getContent();
        $this->assertStringContains('Code,Status,Batch ID,Batch Name,Distributor,Used By,Created At,Used At', $content);

        foreach ($codes as $code) {
            $this->assertStringContains($code->code, $content);
        }
    }

    public function test_admin_can_download_csv_of_specific_batch()
    {
        // Setup: create codes in specific batch
        $batchId = 'test-batch-' . time();
        $codes = AdminCode::factory()->count(5)->create([
            'batch_id' => $batchId,
            'batch_name' => 'Test Batch',
            'status' => 'issued'
        ]);

        // Action: admin downloads specific batch CSV
        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.download', ['batch_id' => $batchId]));

        // Assertions
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="admin_codes_batch_Test Batch_' . $batchId . '.csv"');

        // Verify CSV content contains only batch codes
        $content = $response->getContent();
        $this->assertStringContains('Test Batch', $content);

        foreach ($codes as $code) {
            $this->assertStringContains($code->code, $content);
        }

        // Verify no other codes are included
        $otherCodes = AdminCode::where('batch_id', '!=', $batchId)->pluck('code');
        foreach ($otherCodes as $otherCode) {
            $this->assertStringNotContains($otherCode, $content);
        }
    }

    public function test_admin_can_view_code_statistics()
    {
        // Setup: create codes with different statuses
        AdminCode::factory()->count(10)->create(['status' => 'issued']);
        AdminCode::factory()->count(5)->create(['status' => 'unused']);
        AdminCode::factory()->count(3)->create(['status' => 'used']);

        // Action: admin views index page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.index'));

        // Assertions
        $response->assertStatus(200);
        $response->assertViewHas('stats');

        // Verify statistics are calculated correctly
        $response->assertSee('Total: 18');
        $response->assertSee('Issued: 10');
        $response->assertSee('Unused: 5');
        $response->assertSee('Used: 3');
    }

    public function test_admin_can_view_batches()
    {
        // Setup: create codes in different batches
        AdminCode::factory()->count(3)->create([
            'batch_id' => 'batch-1',
            'batch_name' => 'First Batch'
        ]);
        AdminCode::factory()->count(2)->create([
            'batch_id' => 'batch-2',
            'batch_name' => 'Second Batch'
        ]);

        // Action: admin views batches page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.batches'));

        // Assertions
        $response->assertStatus(200);
        $response->assertSee('First Batch');
        $response->assertSee('Second Batch');
    }

    public function test_non_admin_cannot_access_admin_code_pages()
    {
        // Setup: create regular user
        $regularUser = User::factory()->create(['is_admin' => false]);

        // Action: regular user tries to access admin pages
        $response1 = $this->actingAs($regularUser)
            ->get(route('admin.admin_codes.index'));
        $response2 = $this->actingAs($regularUser)
            ->post(route('admin.admin_codes.generate'));
        $response3 = $this->actingAs($regularUser)
            ->get(route('admin.admin_codes.download'));

        // Assertions: should get 403 Forbidden
        $response1->assertStatus(403);
        $response2->assertStatus(403);
        $response3->assertStatus(403);
    }

    public function test_admin_code_generation_validation()
    {
        // Action: admin tries to generate codes with invalid data
        $response1 = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 1001, // Too many
                'batch_name' => 'Test'
            ]);

        $response2 = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 10,
                'batch_name' => '' // Empty name
            ]);

        $response3 = $this->actingAs($this->admin)
            ->post(route('admin.admin_codes.generate'), [
                'count' => 5,
                'batch_name' => str_repeat('A', 256) // Too long
            ]);

        // Assertions: validation errors
        $response1->assertSessionHasErrors(['count']);
        $response2->assertSessionHasErrors(['batch_name']);
        $response3->assertSessionHasErrors(['batch_name']);
    }

    public function test_admin_can_view_code_details()
    {
        // Setup: create code with relationships
        $usedByUser = User::factory()->create();
        $code = AdminCode::factory()->create([
            'status' => 'used',
            'distributor_id' => $this->distributor->id,
            'used_by_user_id' => $usedByUser->id,
            'used_at' => now()
        ]);

        // Action: admin views code details
        $response = $this->actingAs($this->admin)
            ->get(route('admin.admin_codes.show', $code));

        // Assertions
        $response->assertStatus(200);
        $response->assertSee($code->code);
        $response->assertSee($this->distributor->name);
        $response->assertSee($usedByUser->name);
    }
}