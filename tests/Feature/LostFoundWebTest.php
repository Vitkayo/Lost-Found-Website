<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LostFoundWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load(): void
    {
        $this->get('/')->assertOk();
        $this->get('/board')->assertOk();
        $this->get('/report')->assertOk();
        $this->get('/claims')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_report_item_submission(): void
    {
        $this->post('/report', [
            'title' => 'Test Keys',
            'status' => 'found',
            'category' => 'key',
            'created_at' => now()->format('Y-m-d\TH:i'),
            'location' => 'Cafeteria',
            'contact_info' => '012999888',
            'description' => 'Feature test',
        ])->assertRedirect(route('board.index'));

        $this->assertDatabaseHas('items', ['title' => 'Test Keys', 'category' => 'key']);
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
        $item = Item::create([
            'title' => 'Found Wallet',
            'status' => 'found',
            'category' => 'other',
            'reported_at' => now(),
            'location' => 'Cafeteria',
            'contact_info' => '012111222',
            'description' => 'Brown leather',
        ]);

        $this->post('/claims', [
            'item_id' => $item->id,
            'claimant_name' => 'Davit',
            'contact_info' => '099888777',
            'message' => 'It is mine, has my ID inside.',
        ])->assertRedirect(route('claims.index', ['type' => 'claim']));

        $this->assertDatabaseHas('item_claims', [
            'item_id' => $item->id,
            'type' => 'claim',
            'contact_info' => '099888777',
        ]);

        $this->get('/claims?type=claim')
            ->assertOk()
            ->assertSee('Claim')
            ->assertSee('Found Wallet')
            ->assertSee('View Details')
            ->assertSee('Brown leather');

        $this->get('/board')
            ->assertOk()
            ->assertDontSee('Found Wallet');
    }

    public function test_found_report_on_lost_item(): void
    {
        $item = Item::create([
            'title' => 'Lost Phone',
            'status' => 'lost',
            'category' => 'electronic',
            'reported_at' => now(),
            'location' => 'Library',
            'contact_info' => '012333444',
            'description' => 'iPhone 13',
        ]);

        $this->post('/claims', [
            'item_id' => $item->id,
            'contact_info' => '011555666',
            'message' => 'Found at the front desk.',
        ])->assertRedirect(route('claims.index', ['type' => 'return']));

        $this->assertDatabaseHas('item_claims', [
            'item_id' => $item->id,
            'type' => 'found',
        ]);

        $this->get('/claims?type=return')
            ->assertOk()
            ->assertSee('Return')
            ->assertSee('Lost Phone')
            ->assertSee('View Details')
            ->assertSee('iPhone 13');

        $this->get('/board')
            ->assertOk()
            ->assertDontSee('Lost Phone');
    }

    public function test_admin_can_delete_claim_from_claims_page(): void
    {
        $item = Item::create([
            'title' => 'Found Bag',
            'status' => 'found',
            'category' => 'other',
            'reported_at' => now(),
            'location' => 'Gate',
            'contact_info' => '012000111',
            'description' => 'test',
        ]);

        $this->post('/claims', [
            'item_id' => $item->id,
            'contact_info' => '099111222',
            'message' => 'This is mine',
        ]);

        $claimId = \App\Models\ItemClaim::first()->id;

        $this->post('/admin/login', ['password' => 'RUPPSTAFF']);

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertDontSee('Found Bag');

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
}
