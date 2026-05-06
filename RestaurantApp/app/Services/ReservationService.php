<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TableStatus;
use App\Models\FloorPlanElement;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Service responsible for reservation operations.
 * Single Responsibility: Handles reservation lifecycle and table availability.
 */
final readonly class ReservationService
{
    /** Default stay window for a seated guest, in hours. */
    public const DEFAULT_STAY_HOURS = 2;

    /**
     * Compute when the currently seated guest at a table must vacate:
     * seating start + 2h, capped by the next active reservation's start time.
     * Stay starts when the guest was actually seated (seated_at), falling back
     * to reservation_datetime for legacy rows without a recorded seat time.
     * Returns null if no guest is currently seated at this table.
     */
    public function getStayEndForElement(int $elementId): ?Carbon
    {
        $arrived = Reservation::where('floor_plan_element_id', $elementId)
            ->where('status', 'arrived')
            ->orderByDesc('reservation_datetime')
            ->first();

        if (! $arrived) {
            return null;
        }

        $stayStart = $arrived->seated_at ?? $arrived->reservation_datetime;
        $defaultEnd = $stayStart->copy()->addHours(self::DEFAULT_STAY_HOURS);

        $nextReservation = Reservation::where('floor_plan_element_id', $elementId)
            ->where('id', '!=', $arrived->id)
            ->where('status', 'scheduled')
            ->where('reservation_datetime', '>', $arrived->reservation_datetime)
            ->orderBy('reservation_datetime')
            ->first();

        if ($nextReservation && $nextReservation->reservation_datetime->lt($defaultEnd)) {
            return $nextReservation->reservation_datetime;
        }

        return $defaultEnd;
    }

    /**
     * Get active reservation for a table element (scheduled or arrived today).
     */
    public function getActiveReservationForElement(int $elementId): ?Reservation
    {
        return Reservation::where('floor_plan_element_id', $elementId)
            ->whereIn('status', ['scheduled', 'arrived'])
            ->whereDate('reservation_datetime', today())
            ->orderBy('reservation_datetime')
            ->first();
    }

    /**
     * Get today's reservations for a table element.
     *
     * @return Collection<int, Reservation>
     */
    public function getTodayReservationsForElement(int $elementId): Collection
    {
        return Reservation::where('floor_plan_element_id', $elementId)
            ->whereDate('reservation_datetime', today())
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('reservation_datetime')
            ->get();
    }

    /**
     * Get upcoming reservations for a table element.
     *
     * @return Collection<int, Reservation>
     */
    public function getUpcomingReservationsForElement(int $elementId): Collection
    {
        return Reservation::where('floor_plan_element_id', $elementId)
            ->where('reservation_datetime', '>=', now())
            ->whereNotIn('status', ['cancelled', 'no_show', 'departed'])
            ->orderBy('reservation_datetime')
            ->get();
    }

    /**
     * Check if a table is available at a given time (2-hour window).
     */
    public function isTableAvailableAt(int $elementId, Carbon $dateTime): bool
    {
        $windowStart = $dateTime->copy()->subHours(2);
        $windowEnd = $dateTime->copy()->addHours(2);

        return ! Reservation::where('floor_plan_element_id', $elementId)
            ->whereBetween('reservation_datetime', [$windowStart, $windowEnd])
            ->whereNotIn('status', ['cancelled', 'no_show', 'departed'])
            ->exists();
    }

    /**
     * Create a reservation linked to a table element.
     */
    public function createForTable(int $elementId, array $data): Reservation
    {
        $element = FloorPlanElement::findOrFail($elementId);

        $reservation = Reservation::create([
            'guest_name' => $data['guest_name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'party_size' => $data['party_size'],
            'reservation_datetime' => $data['reservation_datetime'],
            'floor_plan_element_id' => $elementId,
            'table_number' => $element->table_name,
            'internal_notes' => $data['internal_notes'] ?? null,
            'status' => 'scheduled',
        ]);

        // Mark table as Reserved only when the reservation is imminent (≤ 30 min away)
        // AND the table is not already occupied
        $reservationTime = Carbon::parse($data['reservation_datetime']);
        if ($reservationTime->isFuture()
            && $reservationTime->diffInMinutes(now()) <= 30
            && $element->status !== TableStatus::Occupied) {
            $element->update(['status' => TableStatus::Reserved]);
        }

        return $reservation;
    }

    /**
     * Seat a reservation (mark as arrived, set table to Occupied).
     *
     * @throws \RuntimeException If table is already occupied by another guest.
     */
    public function seatReservation(Reservation $reservation): Reservation
    {
        // Prevent seating if another guest is already at this table
        // Check fresh database state to avoid stale relationship issues
        if ($reservation->floor_plan_element_id) {
            $existingArrived = Reservation::where('floor_plan_element_id', $reservation->floor_plan_element_id)
                ->where('status', 'arrived')
                ->where('id', '!=', $reservation->id)
                ->exists();

            if ($existingArrived) {
                throw new \RuntimeException('This table is already occupied by another guest.');
            }
        }

        $reservation->update([
            'status' => 'arrived',
            'seated_at' => now(),
        ]);

        if ($reservation->floorPlanElement) {
            $reservation->floorPlanElement->update(['status' => TableStatus::Occupied]);
        }

        return $reservation->fresh();
    }

    /**
     * Complete a reservation (mark as departed). Table status is recomputed from
     * remaining today-reservations: Occupied > Reserved > Available.
     * Order payment is handled separately via the Order Info flow.
     */
    public function completeReservation(Reservation $reservation): Reservation
    {
        $reservation->update(['status' => 'departed']);

        $this->syncTableStatusFromReservations($reservation);

        return $reservation->fresh();
    }

    /**
     * Recompute and persist the table status for a reservation's floor plan element
     * based on today's remaining reservations.
     */
    public function syncTableStatusFromReservations(Reservation $reservation): void
    {
        $element = $reservation->floorPlanElement;
        if (! $element) {
            return;
        }

        $remaining = Reservation::where('floor_plan_element_id', $element->id)
            ->where('id', '!=', $reservation->id)
            ->whereIn('status', ['scheduled', 'arrived'])
            ->whereDate('reservation_datetime', today())
            ->get();

        $status = match (true) {
            $remaining->contains(fn (Reservation $r): bool => $r->status === 'arrived') => TableStatus::Occupied,
            $remaining->contains(fn (Reservation $r): bool => $r->status === 'scheduled') => TableStatus::Reserved,
            default => TableStatus::Available,
        };

        $element->update(['status' => $status]);
    }

    /**
     * Auto-mark scheduled reservations as late if their time has passed.
     */
    public function autoMarkLateReservations(): int
    {
        $count = Reservation::where('status', 'scheduled')
            ->where('reservation_datetime', '<', now())
            ->update(['status' => 'late']);

        $this->resetStaleTableStatuses();

        return $count;
    }

    /**
     * Reset floor plan element status to Available for any table whose
     * persisted status is Reserved but has no active (scheduled/arrived)
     * reservation for today.
     */
    private function resetStaleTableStatuses(): void
    {
        $staleElementIds = FloorPlanElement::where('status', TableStatus::Reserved)
            ->whereDoesntHave('reservations', function ($query) {
                $query->whereIn('status', ['scheduled', 'arrived'])
                    ->whereDate('reservation_datetime', today());
            })
            ->pluck('id');

        if ($staleElementIds->isNotEmpty()) {
            FloorPlanElement::whereIn('id', $staleElementIds)
                ->update(['status' => TableStatus::Available]);
        }
    }

    /**
     * Get all FloorPlanElements that are available at a given datetime for a given party size.
     *
     * @return SupportCollection<int, FloorPlanElement>
     */
    public function getAvailableTablesAt(Carbon $dateTime, int $partySize): SupportCollection
    {
        return FloorPlanElement::whereNotNull('table_name')
            ->where('seat_count', '>=', $partySize)
            ->get()
            ->filter(fn (FloorPlanElement $element) => $this->isTableAvailableAt($element->id, $dateTime))
            ->values();
    }

    /**
     * Find the smallest available table that fits the party size at the given datetime.
     * Ties broken by id for deterministic selection.
     */
    public function findBestAvailableTableAt(Carbon $dateTime, int $partySize): ?FloorPlanElement
    {
        return $this->getAvailableTablesAt($dateTime, $partySize)
            ->sortBy([['seat_count', 'asc'], ['id', 'asc']])
            ->first();
    }

    /**
     * Get reservation-to-element mapping for a floor plan (today's active reservations).
     *
     * @return array<int, array{reservation_id: int, guest_name: string, party_size: int, time: string, status: string}>
     */
    public function getReservationMapForFloorPlan(int $floorPlanId): array
    {
        return $this->getReservationMapForFloorPlanAt($floorPlanId, now());
    }

    /**
     * Reservation map for a floor plan at a specific datetime (±2-hour window).
     *
     * @return array<int, array{reservation_id: int, guest_name: string, party_size: int, time: string, status: string}>
     */
    public function getReservationMapForFloorPlanAt(int $floorPlanId, Carbon $datetime): array
    {
        $windowStart = $datetime->copy()->subHours(2);
        $windowEnd = $datetime->copy()->addHours(2);

        $reservations = Reservation::whereHas('floorPlanElement', function ($query) use ($floorPlanId) {
            $query->where('floor_plan_id', $floorPlanId);
        })
            ->whereBetween('reservation_datetime', [$windowStart, $windowEnd])
            ->whereNotIn('status', ['cancelled', 'no_show', 'departed'])
            ->get();

        $map = [];
        foreach ($reservations as $reservation) {
            $map[$reservation->floor_plan_element_id] = [
                'reservation_id' => $reservation->id,
                'guest_name' => $reservation->guest_name,
                'party_size' => $reservation->party_size,
                'time' => $reservation->reservation_datetime->format('H:i'),
                'status' => $reservation->status,
            ];
        }

        return $map;
    }
}
