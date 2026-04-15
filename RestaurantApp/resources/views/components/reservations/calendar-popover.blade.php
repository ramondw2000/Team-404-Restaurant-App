@props(['selectedDate'])

@php
    $selected = \Carbon\Carbon::parse($selectedDate);
    $viewMonth = $selected->copy()->startOfMonth();
    $daysInMonth = $viewMonth->daysInMonth;
    $startDow = ($viewMonth->dayOfWeek + 6) % 7; // Monday-first
    $days = ['Mo','Tu','We','Th','Fr','Sa','Su'];
    $today = \Carbon\Carbon::today()->toDateString();
@endphp

<div
    x-data="{
        viewYear: {{ $viewMonth->year }},
        viewMonth: {{ $viewMonth->month }},
        selected: '{{ $selected->toDateString() }}',
        today: '{{ $today }}',

        daysInMonth() {
            return new Date(this.viewYear, this.viewMonth, 0).getDate();
        },
        startDow() {
            const d = new Date(this.viewYear, this.viewMonth - 1, 1).getDay();
            return (d + 6) % 7;
        },
        isoDate(day) {
            const m = String(this.viewMonth).padStart(2, '0');
            const d = String(day).padStart(2, '0');
            return `${this.viewYear}-${m}-${d}`;
        },
        prevMonth() {
            if (this.viewMonth === 1) { this.viewMonth = 12; this.viewYear--; } else { this.viewMonth--; }
        },
        nextMonth() {
            if (this.viewMonth === 12) { this.viewMonth = 1; this.viewYear++; } else { this.viewMonth++; }
        },
        monthName() {
            return new Date(this.viewYear, this.viewMonth - 1, 1).toLocaleString('en-GB', { month: 'long' });
        },
        pick(day) {
            this.selected = this.isoDate(day);
            $wire.setDate(this.selected);
            open = false;
        },
    }"
>
    {{-- Month nav --}}
    <div class="flex items-center justify-between mb-3">
        <button type="button" @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <span class="text-sm font-semibold text-gray-900" x-text="monthName() + ' ' + viewYear"></span>
        <button type="button" @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </div>

    {{-- Day headers --}}
    <div class="grid grid-cols-7 mb-1">
        @foreach($days as $d)
            <div class="text-center text-[0.65rem] font-bold text-gray-400 uppercase py-1">{{ $d }}</div>
        @endforeach
    </div>

    {{-- Day cells --}}
    <div class="grid grid-cols-7 gap-y-1">
        <template x-for="offset in startDow()" :key="'blank-' + offset">
            <div></div>
        </template>
        <template x-for="day in daysInMonth()" :key="day">
            <button
                type="button"
                @click="pick(day)"
                :class="{
                    'bg-molveno-blue-500 text-white font-bold': isoDate(day) === selected,
                    'bg-molveno-blue-50 text-molveno-blue-700 font-semibold ring-1 ring-molveno-blue-300': isoDate(day) === today && isoDate(day) !== selected,
                    'text-gray-700 hover:bg-gray-100': isoDate(day) !== selected && isoDate(day) !== today,
                }"
                class="w-full aspect-square flex items-center justify-center text-xs rounded-lg transition-colors duration-100"
                x-text="day"
            ></button>
        </template>
    </div>

    {{-- Today shortcut --}}
    <div class="mt-3 pt-3 border-t border-gray-100">
        <button
            type="button"
            @click="pick(new Date().getDate()); viewYear = new Date().getFullYear(); viewMonth = new Date().getMonth() + 1;"
            class="w-full text-center text-xs font-semibold text-molveno-blue-600 hover:text-molveno-blue-800 transition-colors"
        >
            Jump to today
        </button>
    </div>
</div>
