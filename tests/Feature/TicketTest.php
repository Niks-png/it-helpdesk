<?php

namespace Tests\Feature;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use App\Filament\Resources\Tickets\Pages\CreateTicket;
use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->employee = User::firstWhere('role', 'employee');
        $this->admin = User::firstWhere('role', 'admin');
    }

    private function ticketFor(User $user, string $title = 'Nav interneta'): Ticket
    {
        return Ticket::create([
            'user_id' => $user->id,
            'category' => TicketCategory::Network,
            'title' => $title,
            'description' => 'Dators nevar pieslēgties tīklam.',
        ]);
    }

    public function test_employee_creates_ticket_with_screenshot(): void
    {
        Storage::fake('local');
        $this->actingAs($this->employee);

        Livewire::test(CreateTicket::class)
            ->fillForm([
                'category' => TicketCategory::Network->value,
                'title' => 'Dators nevar pieslēgties tīklam',
                'description' => 'Kopš rīta nav interneta.',
                'screenshot' => UploadedFile::fake()->image('ekrans.png'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $ticket = Ticket::sole();
        $this->assertSame(sprintf('IT-%d-0001', now()->year), $ticket->number);
        $this->assertTrue($ticket->user->is($this->employee));
        $this->assertSame(TicketStatus::New, $ticket->status);
        $this->assertSame(TicketCategory::Network, $ticket->category);
        Storage::disk('local')->assertExists($ticket->screenshot);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs($this->employee);

        Livewire::test(CreateTicket::class)
            ->fillForm(['category' => null, 'title' => '', 'description' => ''])
            ->call('create')
            ->assertHasFormErrors(['category' => 'required', 'title' => 'required', 'description' => 'required']);

        $this->assertSame(0, Ticket::count());
    }

    public function test_employee_sees_only_own_tickets(): void
    {
        $other = User::create(['name' => 'Cits Darbinieks', 'email' => 'cits@test.local', 'password' => 'x', 'role' => 'employee']);
        $mine = $this->ticketFor($this->employee, 'Mans pieteikums');
        $theirs = $this->ticketFor($other, 'Svešs pieteikums');

        $this->actingAs($this->employee);

        Livewire::test(ListTickets::class)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);

        $this->get("/tickets/{$mine->id}")->assertOk()->assertSee($mine->number);
        $this->get("/tickets/{$theirs->id}")->assertNotFound();
    }

    public function test_admin_sees_all_tickets(): void
    {
        $tickets = [$this->ticketFor($this->employee), $this->ticketFor($this->admin)];

        $this->actingAs($this->admin);

        Livewire::test(ListTickets::class)->assertCanSeeTableRecords($tickets);
    }
}
