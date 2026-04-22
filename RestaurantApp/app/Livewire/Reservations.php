<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\FloorPlanElement;
use App\Models\Reservation;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Poll;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Reservations')]
#[Poll('5s')]
class Reservations extends Component
{
    public string $selectedDate = '';

    public string $search = '';

    public string $statusFilter = 'all';

    /** Snapshot of the selected reservation for the detail sheet (frozen while open). */
    public ?array $selectedReservation = null;

    /** Fields for the create form. */
    public string $createGuestName = '';

    public string $createPhone = '';

    public string $createEmail = '';

    public int $createPartySize = 2;

    public string $createDatetime = '';

    public string $createRoomNumber = '';

    public string $createNotes = '';

    public ?int $createFloorPlanElementId = null;

    /** Fields for the edit form. */
    public string $editGuestName = '';

    public string $editPhone = '';

    public string $editEmail = '';

    public int $editPartySize = 2;

    public string $editDatetime = '';

    public string $editRoomNumber = '';

    public string $editNotes = '';

    public string $editStatus = 'scheduled';

    public ?int $editReservationId = null;

    public function mount(): void
    {
        $this->selectedDate = Carbon::today()->toDateString();
        $this->createDatetime = now()->addHour()->format('Y-m-d\TH:i');
    }

    // ─── Computed ──────────────────────────────────────────────────────

    /**
     * All reservations for the selected date, ordered by time.
     *
     * @return Collection<int, Reservation>
     */
    #[Computed]
    public function reservations(): Collection
    {
        return Reservation::whereDate('reservation_datetime', $this->selectedDate)
            ->when($this->search !== '', fn ($q) => $q->where('guest_name', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('reservation_datetime')
            ->get();
    }

    /**
     * Reservations grouped into 30-minute time slots.
     *
     * @return SupportCollection<string, Collection<int, Reservation>>
     */
    #[Computed]
    public function groupedReservations(): SupportCollection
    {
        return $this->reservations->groupBy(
            fn (Reservation $r) => $r->reservation_datetime->copy()->floorMinutes(30)->format('H:i')
        );
    }

    /**
     * Status counts for the filter pills (full day, ignores search).
     *
     * @return array<string, int>
     */
    #[Computed]
    public function statusCounts(): array
    {
        $rows = Reservation::whereDate('reservation_datetime', $this->selectedDate)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return array_merge(['all' => array_sum($rows)], $rows);
    }

    /**
     * Total reservation count for the selected date (all sessions).
     */
    #[Computed]
    public function totalCount(): int
    {
        return Reservation::whereDate('reservation_datetime', $this->selectedDate)->count();
    }

    /**
     * Available tables for the create form, fetched reactively.
     *
     * @return SupportCollection<int, FloorPlanElement>
     */
    #[Computed]
    public function availableTables(): SupportCollection
    {
        if ($this->createDatetime === '' || $this->createPartySize < 1) {
            return collect();
        }

        return app(ReservationService::class)->getAvailableTablesAt(
            Carbon::parse($this->createDatetime),
            $this->createPartySize
        );
    }

    // ─── Date ──────────────────────────────────────────────────────────

    public function setDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->search = '';
        $this->statusFilter = 'all';
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    // ─── Detail Sheet ──────────────────────────────────────────────────

    public function selectReservation(int $id): void
    {
        $reservation = Reservation::find($id);
        if (! $reservation) {
            return;
        }

        $this->selectedReservation = [
            'id' => $reservation->id,
            'guest_name' => $reservation->guest_name,
            'phone' => $reservation->phone,
            'email' => $reservation->email,
            'party_size' => $reservation->party_size,
            'table_number' => $reservation->table_number,
            'room_number' => $reservation->room_number,
            'status' => $reservation->status,
            'internal_notes' => $reservation->internal_notes,
            'reservation_datetime' => $reservation->reservation_datetime->toIso8601String(),
            'created_at' => $reservation->created_at?->toIso8601String(),
        ];

        $this->dispatch('open-sheet', name: 'detail-reservation');
    }

    // ─── Status Transitions ────────────────────────────────────────────

    /**
     * Advance the reservation to the next status in the main flow:
     * scheduled → arrived → departed
     */
    public function advanceStatus(int $id): void
    {
        $reservation = Reservation::findOrFail($id);

        $next = match ($reservation->status) {
            'scheduled' => 'arrived',
            'arrived' => 'departed',
            default => null,
        };

        if ($next === null) {
            return;
        }

        $service = app(ReservationService::class);

        if ($next === 'arrived') {
            $service->seatReservation($reservation);
        } elseif ($next === 'departed') {
            $service->completeReservation($reservation);
        }

        $this->dispatch('toast', message: "{$reservation->guest_name} marked as ".ucfirst($next), type: 'success');
    }

    /**
     * Set a specific status (for destructive / off-flow statuses).
     */
    public function setStatus(int $id, string $status): void
    {
        $allowed = ['late', 'cancelled', 'no_show', 'optional'];
        if (! in_array($status, $allowed)) {
            return;
        }

        if ($status === 'cancelled') {
            $this->authorize('Cancel Reservation');
        }

        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => $status]);

        $label = ucfirst(str_replace('_', ' ', $status));
        $this->dispatch('toast', message: "{$reservation->guest_name} marked as {$label}", type: 'success');
    }

    // ─── Create ────────────────────────────────────────────────────────

    public function openCreateSheet(): void
    {
        $this->createGuestName = '';
        $this->createPhone = '';
        $this->createEmail = '';
        $this->createPartySize = 2;
        $this->createDatetime = Carbon::parse($this->selectedDate)->setHour(19)->setMinute(0)->format('Y-m-d\TH:i');
        $this->createRoomNumber = '';
        $this->createNotes = '';
        $this->createFloorPlanElementId = null;
        $this->resetValidation();
        $this->dispatch('open-sheet', name: 'create-reservation');
    }

    public function createReservation(): void
    {
        $this->validate([
            'createGuestName' => ['required', 'string', 'max:255'],
            'createPhone' => ['nullable', 'string', 'max:50'],
            'createEmail' => ['nullable', 'email', 'max:255'],
            'createPartySize' => ['required', 'integer', 'min:1', 'max:20'],
            'createDatetime' => ['required', 'date', 'after:now'],
            'createRoomNumber' => ['nullable', 'string', 'max:50'],
            'createNotes' => ['nullable', 'string', 'max:1000'],
            'createFloorPlanElementId' => ['nullable', 'integer', 'exists:floor_plan_elements,id'],
        ], [
            'createDatetime.after' => 'Reservation time cannot be in the past.',
        ]);

        $service = app(ReservationService::class);
        $data = [
            'guest_name' => $this->createGuestName,
            'phone' => $this->createPhone ?: null,
            'email' => $this->createEmail ?: null,
            'party_size' => $this->createPartySize,
            'reservation_datetime' => $this->createDatetime,
            'room_number' => $this->createRoomNumber ?: null,
            'internal_notes' => $this->createNotes ?: null,
            'status' => 'scheduled',
        ];

        if ($this->createFloorPlanElementId) {
            $service->createForTable($this->createFloorPlanElementId, $data);
        } else {
            $bestTable = $service->findBestAvailableTableAt(
                Carbon::parse($this->createDatetime),
                $this->createPartySize
            );

            if ($bestTable !== null) {
                $service->createForTable($bestTable->id, $data);
            } else {
                Reservation::create($data);
                $this->dispatch('toast', message: 'No suitable table available. Reservation created without a table.', type: 'warning');
            }
        }

        $this->dispatch('close-sheet', name: 'create-reservation');
        $this->dispatch('toast', message: "Reservation created for {$this->createGuestName}", type: 'success');
    }

    // ─── Edit ──────────────────────────────────────────────────────────

    public function openEditSheet(int $id): void
    {
        $reservation = Reservation::findOrFail($id);

        $this->editReservationId = $reservation->id;
        $this->editGuestName = $reservation->guest_name;
        $this->editPhone = $reservation->phone ?? '';
        $this->editEmail = $reservation->email ?? '';
        $this->editPartySize = $reservation->party_size;
        $this->editDatetime = $reservation->reservation_datetime->format('Y-m-d\TH:i');
        $this->editRoomNumber = $reservation->room_number ?? '';
        $this->editNotes = $reservation->internal_notes ?? '';
        $this->editStatus = $reservation->status;
        $this->resetValidation();

        $this->dispatch('close-sheet', name: 'detail-reservation');
        $this->dispatch('open-sheet', name: 'edit-reservation');
    }

    public function updateReservation(): void
    {
        $this->validate([
            'editGuestName' => ['required', 'string', 'max:255'],
            'editPhone' => ['nullable', 'string', 'max:50'],
            'editEmail' => ['nullable', 'email', 'max:255'],
            'editPartySize' => ['required', 'integer', 'min:1', 'max:20'],
            'editDatetime' => ['required', 'date'],
            'editRoomNumber' => ['nullable', 'string', 'max:50'],
            'editNotes' => ['nullable', 'string', 'max:1000'],
            'editStatus' => ['required', 'string', 'in:scheduled,arrived,departed,cancelled,late,optional,no_show'],
        ]);

        $reservation = Reservation::findOrFail($this->editReservationId);
        $reservation->update([
            'guest_name' => $this->editGuestName,
            'phone' => $this->editPhone ?: null,
            'email' => $this->editEmail ?: null,
            'party_size' => $this->editPartySize,
            'reservation_datetime' => $this->editDatetime,
            'room_number' => $this->editRoomNumber ?: null,
            'internal_notes' => $this->editNotes ?: null,
            'status' => $this->editStatus,
        ]);

        $this->dispatch('close-sheet', name: 'edit-reservation');
        $this->dispatch('toast', message: "Reservation for {$this->editGuestName} updated", type: 'success');
    }

    // ─── Render ────────────────────────────────────────────────────────

    public function render(): View
    {
        $this->throttledAutoMarkLate();

        return view('livewire.reservations')
            ->layout('layouts.molveno');
    }

    /**
     * Run autoMarkLateReservations at most once per minute to avoid
     * hammering the DB on every 5-second poll render.
     */
    private function throttledAutoMarkLate(): void
    {
        $key = 'auto_mark_late_last_run';

        if (cache()->has($key)) {
            return;
        }

        app(ReservationService::class)->autoMarkLateReservations();
        cache()->put($key, true, now()->addMinute());
    }

    // ─── Helpers ───────────────────────────────────────────────────────

}
