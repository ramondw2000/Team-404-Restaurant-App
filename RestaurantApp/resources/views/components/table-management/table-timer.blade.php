@props([
    'timerInfo' => null,
])

@if($timerInfo)
    @php
        $arrivedAt24h = $timerInfo['arrived_at_24h'] ?? $timerInfo['arrived_at'] ?? '';
        $occupiedUntil24h = $timerInfo['occupied_until_24h'] ?? $timerInfo['occupied_until'] ?? '';
        $slotStart24h = $timerInfo['slot_start_24h'] ?? '19:00';
        $elapsedMinutes = $timerInfo['elapsed_minutes'] ?? 0;
        $remainingMinutes = $timerInfo['remaining_minutes'] ?? 120;
        $isOvertime = ($timerInfo['is_overtime'] ?? false) ? 'true' : 'false';
        // Determine effective start: if arrived before 7pm, show 7pm; otherwise show actual arrival
        $arrivedHour = (int) substr($arrivedAt24h, 0, 2);
        $arrivedMinute = (int) substr($arrivedAt24h, 3, 2);
        $effectiveStart24h = ($arrivedHour < 19 || ($arrivedHour === 19 && $arrivedMinute === 0)) ? '19:00' : $arrivedAt24h;
        $effectiveStartLabel = ($arrivedHour < 19 || ($arrivedHour === 19 && $arrivedMinute === 0)) ? '7:00 p.m.' : ($timerInfo['arrived_at'] ?? '');
    @endphp
    <div
        class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100"
        x-data="{
            arrivedAt: '{{ $arrivedAt24h }}',
            effectiveStart: '{{ $effectiveStart24h }}',
            occupiedUntil: '{{ $occupiedUntil24h }}',
            elapsedMinutes: {{ $elapsedMinutes }},
            remainingMinutes: {{ $remainingMinutes }},
            isOvertime: {{ $isOvertime }},
            timerInterval: null,
            formatDuration(mins) {
                if (mins < 0) mins = 0;
                const h = Math.floor(mins / 60);
                const m = mins % 60;
                return h + 'h ' + (m < 10 ? '0' : '') + m + 'm';
            },
            formatTime12h(time24h) {
                if (!time24h || !time24h.includes(':')) return '';
                const parts = time24h.split(':');
                const hours = parseInt(parts[0]);
                const minutes = parseInt(parts[1]);
                const period = hours >= 12 ? 'p.m.' : 'a.m.';
                const hours12 = hours % 12 || 12;
                return hours12 + ':' + (minutes < 10 ? '0' : '') + minutes + ' ' + period;
            },
            updateTimer() {
                const now = new Date();
                const slotStart = new Date();
                slotStart.setHours(19, 0, 0); // 7:00 PM
                const occupied = new Date();
                occupied.setHours(21, 0, 0); // 9:00 PM

                if (now < slotStart) {
                    // Before 7pm: no elapsed time, full 2 hours remaining
                    this.elapsedMinutes = 0;
                    this.remainingMinutes = 120; // 2 hours
                    this.isOvertime = false;
                } else if (now >= slotStart && now < occupied) {
                    // Between 7pm and 9pm: normal countdown
                    this.elapsedMinutes = Math.floor((now - slotStart) / 60000);
                    this.remainingMinutes = Math.max(0, Math.floor((occupied - now) / 60000));
                    this.isOvertime = false;
                } else {
                    // After 9pm: overtime
                    this.elapsedMinutes = Math.floor((now - slotStart) / 60000);
                    this.remainingMinutes = 0;
                    this.isOvertime = true;
                }
            }
        }"
        x-init="updateTimer(); timerInterval = setInterval(() => updateTimer(), 10000)"
        x-on:livewire:navigate.window="clearInterval(timerInterval)"
    >
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Table Timer</span>
            </div>
            <span
                x-show="isOvertime"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700"
            >
                Overtime
            </span>
        </div>

        <div class="grid gap-3 text-center" x-bind:class="elapsedMinutes > 0 ? 'grid-cols-3' : 'grid-cols-2'">
            {{-- Elapsed Time (hidden when 0) --}}
            <div class="bg-white/70 rounded-lg p-2" x-show="elapsedMinutes > 0" x-cloak>
                <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">Sitting</p>
                <p
                    class="text-sm font-bold"
                    x-bind:class="elapsedMinutes > 90 ? 'text-amber-600' : 'text-gray-800'"
                    x-text="formatDuration(elapsedMinutes)"
                >{{ floor($elapsedMinutes / 60) }}h {{ str_pad($elapsedMinutes % 60, 2, '0', STR_PAD_LEFT) }}m</p>
                <p class="text-[9px] text-gray-400 mt-0.5">
                    since <span x-text="formatTime12h(effectiveStart)">{{ $effectiveStartLabel }}</span>
                </p>
            </div>

            {{-- Remaining Time --}}
            <div class="bg-white/70 rounded-lg p-2">
                <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">Remaining</p>
                <p
                    class="text-sm font-bold"
                    x-bind:class="remainingMinutes < 30 ? 'text-red-600' : 'text-green-600'"
                    x-text="formatDuration(remainingMinutes)"
                >{{ floor($remainingMinutes / 60) }}h {{ str_pad($remainingMinutes % 60, 2, '0', STR_PAD_LEFT) }}m</p>
                <p class="text-[9px] text-gray-400 mt-0.5">until next guest</p>
            </div>

            {{-- Occupied Until --}}
            <div class="bg-white/70 rounded-lg p-2">
                <p class="text-[10px] text-gray-500 uppercase tracking-wide mb-1">Free at</p>
                <p class="text-sm font-bold text-gray-800" x-text="formatTime12h(occupiedUntil)">{{ $timerInfo['occupied_until'] ?? '' }}</p>
                <p class="text-[9px] text-gray-400 mt-0.5">table available</p>
            </div>
        </div>

        {{-- Guest Info --}}
        <div class="mt-3 pt-3 border-t border-blue-200/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                    {{ substr($timerInfo['guest_name'] ?? '', 0, 1) }}
                </span>
                <span class="text-sm font-medium text-gray-700">{{ $timerInfo['guest_name'] ?? '' }}</span>
            </div>
            <span class="text-xs text-gray-500">{{ $timerInfo['party_size'] ?? 0 }} guests</span>
        </div>
    </div>
@endif
