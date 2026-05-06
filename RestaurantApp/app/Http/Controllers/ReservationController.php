<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());

        // Auto-mark late reservations
        app(ReservationService::class)->autoMarkLateReservations();

        $dinnerReservations = Reservation::whereDate('reservation_datetime', $selectedDate)
            ->orderBy('reservation_datetime')
            ->get()
            ->groupBy(fn (Reservation $r) => $r->reservation_datetime->format('Y-m-d H:i'));

        return view('reservations', compact('dinnerReservations', 'selectedDate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('Create Reservations');

        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1|max:20',
            'reservation_datetime' => 'required|date',
            'table_number' => 'nullable|string|max:50',
            'room_number' => 'nullable|string|max:50',
            'internal_notes' => 'nullable|string|max:1000',
        ]);

        $validated['status'] = 'scheduled';

        Reservation::create($validated);

        return redirect()->route('reservations.index', ['date' => Carbon::parse($validated['reservation_datetime'])->toDateString()])
            ->with('success', 'Reservation created successfully.');
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('Update Reservations');

        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'party_size' => 'required|integer|min:1|max:20',
            'reservation_datetime' => 'required|date',
            'table_number' => 'nullable|string|max:50',
            'room_number' => 'nullable|string|max:50',
            'status' => 'required|string|in:scheduled,arrived,departed,cancelled,late,optional,no_show',
            'internal_notes' => 'nullable|string|max:1000',
        ]);

        $reservation->update($validated);

        return redirect()->route('reservations.index', ['date' => Carbon::parse($validated['reservation_datetime'])->toDateString()])
            ->with('success', 'Reservation updated successfully.');
    }

    public function updateStatus(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('Update Reservations');

        $validated = $request->validate([
            'status' => 'required|string|in:scheduled,arrived,departed,cancelled,late,optional,no_show',
        ]);

        $reservation->update($validated);

        return redirect()->route('reservations.index', ['date' => $reservation->reservation_datetime->toDateString()])
            ->with('success', 'Reservation status updated.');
    }
}
