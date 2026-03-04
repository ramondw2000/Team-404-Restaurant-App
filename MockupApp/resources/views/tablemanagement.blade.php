<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Table Management - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            /* Desktop (≥1280px): original fixed 7-column layout */
            .table-grid {
                grid-template-columns: repeat(7, 200px);
            }
            .table-card {
                width: 200px;
                height: 200px;
                box-sizing: border-box;
            }
            /* Large tablet / small desktop (768px–1279px): 4 fluid columns */
            @media (max-width: 1279px) {
                .table-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
                .table-card {
                    width: 100%;
                    height: auto;
                    aspect-ratio: 1;
                    box-sizing: border-box;
                }
            }
            /* Small tablet (640px–767px): 3 fluid columns */
            @media (max-width: 767px) {
                .table-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }
            /* Mobile (<640px): 2 fluid columns, smaller icon + tighter card gap */
            @media (max-width: 639px) {
                .table-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                .table-card {
                    gap: 0.4rem !important;
                }
                .table-card .flex-1 svg {
                    width: 44px;
                    height: 44px;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.guest-navigation')

        @php
        $tables = [
            ['id' => 'A1',  'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A2',  'seats' => 6, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A3',  'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A4',  'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A5',  'seats' => 6, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A6',  'seats' => 4, 'status' => 'free',     'waiter' => true ],
            ['id' => 'A7',  'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A8',  'seats' => 4, 'status' => 'free',     'waiter' => false],
            ['id' => 'A9',  'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A10', 'seats' => 4, 'status' => 'free',     'waiter' => true ],
            ['id' => 'A11', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A12', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A13', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A14', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A15', 'seats' => 6, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A16', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A17', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A18', 'seats' => 6, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A19', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A20', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A21', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A22', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A23', 'seats' => 4, 'status' => 'occupied', 'waiter' => true ],
            ['id' => 'A24', 'seats' => 6, 'status' => 'free',     'waiter' => false],
            ['id' => 'A25', 'seats' => 4, 'status' => 'reserved', 'waiter' => false],
            ['id' => 'A26', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A27', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
            ['id' => 'A28', 'seats' => 4, 'status' => 'occupied', 'waiter' => false],
        ];
        @endphp

        <div class="max-w-screen-xl mx-auto px-6 py-8 flex flex-col gap-8">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="m-0 text-lg font-bold text-primary">Floor Plan</h2>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 sm:gap-5">
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-sm" style="background-color: #2e7d5e;"></div>
                        <span class="text-[13px] font-medium text-gray-700">Available</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-sm" style="background-color: #7a4030;"></div>
                        <span class="text-[13px] font-medium text-gray-700">Occupied</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-sm" style="background-color: #006ead;"></div>
                        <span class="text-[13px] font-medium text-gray-700">Reserved</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-3.5 h-3.5 rounded-full" style="background-color: #b07a20;"></div>
                        <span class="text-[13px] font-medium text-gray-700">Waiter needed</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 justify-center table-grid">
                @foreach($tables as $table)
                    @php
                        $cardBg = match($table['status']) {
                            'free'     => '#C1E1C1',
                            'occupied' => '#AEC6CF',
                            default    => '#FF6961',
                        };
                        $waiterBg = $table['waiter'] ? 'gold' : 'white';
                    @endphp
                    <div class="flex flex-col gap-5 p-4 rounded-2xl cursor-pointer transition-all duration-150 shadow-md hover:shadow-xl hover:-translate-y-1 select-none table-card"
                         style="background-color: {{ $cardBg }};">

                        <div class="flex justify-between items-start shrink-0">
                            <span style="text-shadow: 1px 1px #000000;" class="text-white font-bold text-lg leading-none">
                                {{ $table['seats'] }} persons
                            </span>
                            <div class="w-[26px] h-[26px] rounded-full flex items-center justify-center shrink-0 shadow-sm p-2 border border-black/80 shadow-black"
                                 style="background-color: {{$waiterBg}}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="black">
                                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 flex items-center justify-center">
                            <svg width="64" height="64" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="22" y="2" width="16" height="10" rx="3" fill="white" fill-opacity="0.9"/>
                                <rect x="22" y="48" width="16" height="10" rx="3" fill="white" fill-opacity="0.9"/>
                                <rect x="2" y="22" width="10" height="16" rx="3" fill="white" fill-opacity="0.9"/>
                                <rect x="48" y="22" width="10" height="16" rx="3" fill="white" fill-opacity="0.9"/>
                                <circle cx="30" cy="30" r="14" fill="white" fill-opacity="0.8"/>
                            </svg>
                        </div>

                        <div class="shrink-0">
                            <span style="text-shadow: 1px 1px #000000;" class="text-white font-black text-[15px] leading-none">{{ $table['id'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </body>
</html>
