<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LostFoundWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_login_and_logout(): void
    {
        $this->post('/register', [
            'name' => 'Campus Student',
            'email' => 'student@example.com',
            'phone' => '012345678',
            'student_id' => '20260001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();

        $this->post('/login', [
            'email' => 'student@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_report_owner_can_edit_but_other_user_cannot(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $item = Item::create([
            'user_id' => $owner->id,
            'title' => 'Original Item',
            'status' => 'lost',
            'category' => 'other',
            'reported_at' => now(),
            'location' => 'Library',
            'contact_info' => 'owner',
        ]);

        $this->actingAs($other)->get(route('report.edit', $item))->assertForbidden();

        $this->actingAs($owner)->put(route('report.update', $item), [
            'title' => 'Updated Item',
            'status' => 'lost',
            'category' => 'other',
            'created_at' => now()->format('Y-m-d\TH:i'),
            'location' => 'Building A',
            'contact_info' => 'owner',
        ])->assertRedirect(route('board.index'));

        $this->assertDatabaseHas('items', ['id' => $item->id, 'title' => 'Updated Item']);
    }

    public function test_uploaded_image_is_optimized_to_webp(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/report', [
            'title' => 'Photo Item',
            'status' => 'lost',
            'category' => 'other',
            'created_at' => now()->format('Y-m-d\TH:i'),
            'location' => 'Library',
            'contact_info' => '012345678',
            'image' => UploadedFile::fake()->image('large.jpg', 2400, 1800),
        ])->assertRedirect(route('board.index'));

        $item = Item::firstOrFail();
        $this->assertStringEndsWith('.webp', $item->image_path);
        Storage::disk('public')->assertExists($item->image_path);

        [$width, $height] = getimagesize(Storage::disk('public')->path($item->image_path));
        $this->assertLessThanOrEqual(1600, max($width, $height));
    }

    public function test_public_pages_load(): void
    {
        $this->get('/')->assertOk();
        $this->get('/board')->assertOk();
        $this->get('/report')->assertRedirect(route('login'));
        $this->get('/claims')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_report_item_submission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/report', [
            'title' => 'Test Keys',
            'status' => 'found',
            'category' => 'key',
            'created_at' => now()->format('Y-m-d\TH:i'),
            'location' => 'Cafeteria',
            'contact_info' => '012999888',
            'description' => 'Feature test',
            'verification_question' => 'What keychain is attached?',
            'verification_answer' => 'Blue tag',
        ])->assertRedirect(route('board.index'));

        $this->assertDatabaseHas('items', ['title' => 'Test Keys', 'category' => 'key', 'user_id' => $user->id]);
    }

    public function test_api_item_create_and_list(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/items', [
            'title' => 'API Water Bottle',
            'status' => 'found',
            'category' => 'bottle_umbrella',
            'created_at' => now()->format('Y-m-d\TH:i'),
            'location' => 'Cafeteria',
            'contact_info' => 'telegram @student',
            'description' => 'Metal bottle with RUPP sticker',
            'verification_question' => 'What sticker is on it?',
            'verification_answer' => 'RUPP',
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'API Water Bottle')
            ->assertJsonPath('data.category', 'bottle_umbrella');

        $this->getJson('/api/items?status=all&sort=desc')
            ->assertOk()
            ->assertJsonFragment(['title' => 'API Water Bottle']);
    }

    public function test_board_category_filter(): void
    {
        Item::create([
            'title' => 'Lost Laptop',
            'status' => 'lost',
            'category' => 'electronic',
            'reported_at' => now(),
            'location' => 'Lab',
            'contact_info' => 'test',
            'description' => 'MacBook',
        ]);

        Item::create([
            'title' => 'Lost Keys',
            'status' => 'lost',
            'category' => 'key',
            'reported_at' => now(),
            'location' => 'Hall',
            'contact_info' => 'test',
            'description' => 'car keys',
        ]);

        $this->get('/board?category=electronic')
            ->assertOk()
            ->assertSee('Lost Laptop')
            ->assertDontSee('Lost Keys');
    }

    public function test_admin_dashboard_and_delete(): void
    {
        $item = Item::create([
            'title' => 'Delete Me',
            'status' => 'lost',
            'category' => 'other',
            'reported_at' => now(),
            'location' => 'Lab',
            'contact_info' => 'test',
            'description' => 'to delete',
        ]);

        $this->get('/admin')->assertRedirect(route('admin.login'));

        $this->post('/admin/login', ['password' => 'RUPPSTAFF'])
            ->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')->assertRedirect(route('admin.dashboard'));

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Management Dashboard')
            ->assertSee('Delete Me')
            ->assertSee('Claims');

        $this->get('/admin/dashboard?section=claims')
            ->assertOk()
            ->assertSee('No claims yet');

        $this->delete(route('admin.items.destroy', $item->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('items', ['id' => $item->id]);

        $this->post('/admin/logout')
            ->assertRedirect(route('home'));

        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
    }

    public function test_claim_on_found_item(): void
    {
        $owner = User::factory()->create();
        $claimant = User::factory()->create();
        $item = Item::create([
            'user_id' => $owner->id,
            'title' => 'Found Wallet',
            'status' => 'found',
            'category' => 'other',
            'reported_at' => now(),
            'location' => 'Cafeteria',
            'contact_info' => '012111222',
            'description' => 'Brown leather',
            'verification_question' => 'What is inside the wallet?',
        ]);

        $this->actingAs($claimant);
        $this->post('/claims', [
            'item_id' => $item->id,
            'claimant_name' => 'Davit',
            'contact_info' => '099888777',
            'verification_answer' => 'Student card',
        ])->assertRedirect();

        $this->assertDatabaseHas('item_claims', [
            'item_id' => $item->id,
            'type' => 'claim',
            'contact_info' => '099888777',
            'message' => null,
            'status' => 'pending',
        ]);

        $this->get('/claims?type=claim')
            ->assertOk()
            ->assertSee('Claim')
            ->assertSee('Found Wallet')
            ->assertSee('View Details')
            ->assertSee('Brown leather');

        $this->actingAs($owner);
        $claim = ItemClaim::firstOrFail();
        $this->patch(route('claims.review', $claim), ['status' => 'approved'])->assertRedirect();

        $this->get('/board')
            ->assertOk()
            ->assertSee('Recently Claimed');

        $this->get('/')
            ->assertOk()
            ->assertSee('Recently Claimed')
            ->assertSee('Found Wallet');
    }

    public function test_api_claim_create_list_show_update_and_delete(): void
    {
        $owner = User::factory()->create();
        $claimant = User::factory()->create();
        $item = Item::create([
            'user_id' => $owner->id,
            'title' => 'Found Calculator',
            'status' => 'found',
            'category' => 'electronic',
            'reported_at' => now(),
            'location' => 'Library',
            'contact_info' => '012111222',
            'description' => 'Casio scientific calculator',
            'verification_question' => 'What initials are written on it?',
        ]);

        Sanctum::actingAs($claimant);
        $this->postJson('/api/claims', [
            'item_id' => $item->id,
            'claimant_name' => 'Sophea',
            'contact_info' => '099123456',
            'message' => 'I can identify the sticker on the back.',
            'verification_answer' => 'SK',
        ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'claim')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.claimant_name', 'Sophea')
            ->assertJsonPath('data.item.title', 'Found Calculator');

        $claim = ItemClaim::firstOrFail();

        $this->getJson('/api/claims?type=claim&status=pending')
            ->assertOk()
            ->assertJsonFragment(['claimant_name' => 'Sophea']);

        $this->getJson("/api/claims/{$claim->id}")
            ->assertOk()
            ->assertJsonPath('data.id', (string) $claim->id)
            ->assertJsonPath('data.item.title', 'Found Calculator');

        Sanctum::actingAs($owner);

        $this->patchJson("/api/claims/{$claim->id}/status", [
            'status' => 'approved',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('item_claims', [
            'id' => $claim->id,
            'status' => 'approved',
        ]);

        $this->deleteJson("/api/claims/{$claim->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Claim deleted successfully');

        $this->assertDatabaseMissing('item_claims', ['id' => $claim->id]);
    }

    public function test_found_report_on_lost_item(): void
    {
        $owner = User::factory()->create();
        $finder = User::factory()->create();
        $item = Item::create([
            'user_id' => $owner->id,
            'title' => 'Lost Phone',
            'status' => 'lost',
            'category' => 'electronic',
            'reported_at' => now(),
            'location' => 'Library',
            'contact_info' => '012333444',
            'description' => 'iPhone 13',
        ]);

        $this->actingAs($finder);
        $this->post('/claims', [
            'item_id' => $item->id,
            'contact_info' => '011555666',
            'message' => 'Found at the front desk.',
        ])->assertRedirect();

        $this->assertDatabaseHas('item_claims', [
            'item_id' => $item->id,
            'type' => 'found',
            'status' => 'pending',
        ]);

        $this->get('/claims?type=return')
            ->assertOk()
            ->assertSee('Found Report')
            ->assertSee('Lost Phone')
            ->assertSee('View Details')
            ->assertSee('iPhone 13');

        $this->actingAs($owner);
        $claim = ItemClaim::firstOrFail();
        $this->patch(route('claims.review', $claim), ['status' => 'approved']);

        $this->get('/board')
            ->assertOk()
            ->assertSee('Recently Claimed');

        $this->get('/')
            ->assertOk()
            ->assertSee('Recently Claimed')
            ->assertSee('Lost Phone');
    }

    public function test_admin_can_delete_claim_from_claims_page(): void
    {
        $user = User::factory()->create();
        $item = Item::create([
            'user_id' => $user->id,
            'title' => 'Found Bag',
            'status' => 'found',
            'category' => 'other',
            'reported_at' => now(),
            'location' => 'Gate',
            'contact_info' => '012000111',
            'description' => 'test',
        ]);

        $this->actingAs($user);
        $this->post('/claims', [
            'item_id' => $item->id,
            'contact_info' => '099111222',
            'message' => 'This is mine',
        ]);

        $claimId = \App\Models\ItemClaim::first()->id;

        $this->post('/admin/login', ['password' => 'RUPPSTAFF']);

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Found Bag');

        $this->get('/admin/dashboard?section=claims&claim_status=claim')
            ->assertOk()
            ->assertSee('Found Bag')
            ->assertSee('View');

        $this->from('/claims')
            ->delete(route('admin.claims.destroy', $claimId))
            ->assertRedirect('/claims')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('item_claims', ['id' => $claimId]);
    }

    public function test_admin_can_review_a_pending_claim(): void
    {
        $owner = User::factory()->create();
        $claimant = User::factory()->create();
        $item = Item::create([
            'user_id' => $owner->id,
            'title' => 'Found Wallet',
            'status' => 'found',
            'category' => 'wallet',
            'reported_at' => now(),
            'location' => 'Library',
            'contact_info' => 'owner-contact',
        ]);
        $claim = ItemClaim::create([
            'item_id' => $item->id,
            'user_id' => $claimant->id,
            'type' => 'claim',
            'status' => 'pending',
            'claimant_name' => 'Student',
            'contact_info' => 'claimant-contact',
            'verification_answer' => 'Blue zipper',
        ]);

        $this->withSession(['is_admin' => true])
            ->patch("/admin/claims/{$claim->id}/review", ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Claim approved.');

        $this->assertDatabaseHas('item_claims', [
            'id' => $claim->id,
            'status' => 'approved',
        ]);
    }
}
