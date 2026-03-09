<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl brand-pill">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m6 6V7" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 3v18h16" />
                </svg>
            </div>
            <div>
                <p class="text-sm concept-label">Concept</p>
                <h2 class="text-2xl font-semibold header-title">Statistics preview</h2>
            </div>
        </div>
    </x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500&display=swap');

        :root {
            --primary-blue: #005693;
            --neutral-white: #ffffff;
            --blue-100: #66b6dc;
            --blue-300: #309bcf;
            --blue-500: #0084c4;
            --blue-700: #006ead;
        }

        .brand-pill {
            background: var(--blue-100);
            color: var(--primary-blue);
        }

        .concept-label {
            color: var(--blue-500);
        }

        .header-title {
            color: var(--primary-blue);
        }

        .stats-shell {
            font-family: 'Space Grotesk', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding-bottom: 4rem;
            background-color: var(--neutral-white);
        }

        .stats-panel {
            background: linear-gradient(145deg, var(--neutral-white) 0%, rgba(102, 182, 220, 0.25) 55%, rgba(0, 86, 147, 0.12) 100%);
            border-radius: 32px;
            padding: 2.5rem;
            box-shadow: 0 45px 65px -40px rgba(0, 86, 147, 0.45);
            position: relative;
            overflow: hidden;
        }

        .stats-panel::after {
            content: "";
            position: absolute;
            inset: 20% -5% auto auto;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(0, 132, 196, 0.35) 0%, rgba(255, 255, 255, 0) 70%);
            transform: translate(30%, -40%);
            pointer-events: none;
        }

        .stats-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .stats-header h1 {
            font-weight: 600;
            font-size: clamp(2rem, 2.6vw, 2.75rem);
            color: var(--primary-blue);
            margin: 0;
        }

        .stats-description {
            max-width: 36ch;
            color: rgba(0, 86, 147, 0.75);
            line-height: 1.6;
            font-size: 1.05rem;
        }

        .timeframe-toggle {
            display: inline-flex;
            padding: 0.08rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: inset 0 1px 4px rgba(0, 86, 147, 0.08);
            gap: 0.1rem;
            backdrop-filter: blur(5px);
            height: 34px;
            align-items: center;
        }

        .timeframe-button {
            border: none;
            border-radius: 999px;
            padding: 0.32rem 0.85rem;
            font-size: 0.85rem;
            line-height: 1.1;
            font-weight: 600;
            letter-spacing: 0.01em;
            color: var(--blue-500);
            background: transparent;
            cursor: pointer;
            transition: all 0.25s ease;
            height: 100%;
            display: inline-flex;
            align-items: center;
        }

        .timeframe-button.is-active {
            background: var(--primary-blue);
            color: var(--neutral-white);
            box-shadow: 0 8px 20px rgba(0, 86, 147, 0.35);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.25rem;
            margin-top: 2.5rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            padding: 1.8rem;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(0, 86, 147, 0.12);
            box-shadow: 0 25px 40px -35px rgba(0, 86, 147, 0.35);
        }

        .stat-card--focus {
            grid-column: span 2;
            min-height: 220px;
        }

        .stat-card h3 {
            margin: 0;
            font-size: 1.05rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--blue-500);
        }

        .stat-value {
            font-size: clamp(2.5rem, 3.2vw, 3.2rem);
            font-weight: 600;
            color: var(--primary-blue);
            margin: 0.75rem 0 0.2rem;
        }

        .stat-label {
            font-size: 1.1rem;
            color: rgba(0, 86, 147, 0.75);
        }

        .insight-text {
            margin-top: 1.2rem;
            font-size: 1.1rem;
            color: var(--blue-700);
            line-height: 1.5;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1.25rem;
        }

        .mini-card {
            border: 1px solid rgba(17, 24, 39, 0.08);
            border-radius: 18px;
            padding: 1.2rem;
            background: #fff;
        }

        .mini-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.08em;
        }

        .mini-value {
            font-size: 1.45rem;
            font-weight: 600;
            color: #111827;
            margin: 0.35rem 0;
        }

        .mini-subtext {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .lists-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-top: 2rem;
        }

        .dish-list {
            list-style: none;
            padding: 0;
            margin: 1.2rem 0 0;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .dish-item {
            background: rgba(0, 132, 196, 0.08);
            border-radius: 16px;
            padding: 1rem 1.2rem;
        }

        .dish-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
        }

        .dish-name {
            font-weight: 600;
            color: var(--primary-blue);
            font-size: 1.1rem;
            margin: 0;
        }

        .dish-meta {
            font-size: 1rem;
            color: rgba(0, 86, 147, 0.7);
            margin: 0.15rem 0 0;
        }

        .dish-bar {
            margin-top: 0.75rem;
            height: 8px;
            border-radius: 999px;
            background: rgba(0, 132, 196, 0.2);
            overflow: hidden;
        }

        .dish-bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--blue-300), var(--blue-500));
        }

        .dish-bar span.is-soft {
            background: linear-gradient(90deg, var(--blue-100), var(--blue-300));
        }

        .stats-shell .text-slate-500,
        .stats-shell .text-slate-400 {
            color: rgba(0, 86, 147, 0.7) !important;
        }

        .stats-shell .text-slate-900 {
            color: var(--primary-blue) !important;
        }

        .stats-shell .text-slate-600 {
            color: rgba(0, 86, 147, 0.8) !important;
        }

        @media (max-width: 1024px) {
            .payment-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .stat-card--focus {
                grid-column: span 1;
            }
        }

        @media (max-width: 640px) {
            .stats-panel {
                padding: 1.8rem;
                border-radius: 24px;
            }

            .timeframe-toggle {
                width: 100%;
                flex-wrap: wrap;
                justify-content: center;
            }

            .timeframe-button {
                flex: 1 1 45%;
                text-align: center;
            }
        }
    </style>

    <section class="stats-shell">
        <div class="stats-panel">
            <div class="stats-header">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Overview</p>
                    <h1>Sales performance</h1>
                    <p class="stats-description">Concept preview of the statistics experience. Toggle between timeframes to
                        see profit, demand patterns and menu winners.</p>
                </div>
                <div class="timeframe-toggle">
                    <button type="button" class="timeframe-button is-active" data-range="daily">Daily</button>
                    <button type="button" class="timeframe-button" data-range="weekly">Weekly</button>
                    <button type="button" class="timeframe-button" data-range="monthly">Monthly</button>
                    <button type="button" class="timeframe-button" data-range="yearly">Yearly</button>
                </div>
            </div>

            <div class="stat-grid">
                <article class="stat-card stat-card--focus">
                    <h3>Total profit</h3>
                    <p class="stat-value" data-profit>—</p>
                    <p class="stat-label" data-label></p>
                    <p class="insight-text" data-highlight></p>
                </article>

                <article class="stat-card">
                    <h3>Orders</h3>
                    <p class="stat-value" data-orders>—</p>
                    <p class="stat-label" data-orders-detail></p>
                </article>
            </div>

            <div class="lists-grid">
                <article class="stat-card">
                    <h3>Best-selling dishes</h3>
                    <ul class="dish-list" data-top-list></ul>
                </article>

                <article class="stat-card">
                    <h3>Lowest-selling dishes</h3>
                    <ul class="dish-list" data-low-list></ul>
                </article>
            </div>

        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statisticsData = {
                daily: {
                    label: 'Today • 6 March 2026',
                    profit: '€ 2,340',
                    highlight: '',
                    orders: '184',
                    ordersDetail: 'Average 23 per hour',
                    topDishes: [
                        { name: 'Tartufo Ravioli', sold: 58, share: 92 },
                        { name: 'Tagliata di Manzo', sold: 41, share: 74 },
                        { name: 'Torta al Limone', sold: 33, share: 66 }
                    ],
                    lowDishes: [
                        { name: 'Panino Caprese', sold: 12, share: 34 },
                        { name: 'Minestrone Verde', sold: 9, share: 28 },
                        { name: 'Crostata Senza Glutine', sold: 6, share: 22 }
                    ]
                },
                weekly: {
                    label: 'Week 10 • 3 – 9 March',
                    profit: '€ 14,870',
                    highlight: '',
                    orders: '1,182',
                    ordersDetail: '1,182 covers served',
                    topDishes: [
                        { name: 'Degustazione dello Chef', sold: 176, share: 94 },
                        { name: 'Burger di Chianina', sold: 142, share: 78 },
                        { name: 'Insalata di Burrata', sold: 131, share: 71 }
                    ],
                    lowDishes: [
                        { name: 'Zuppa di Stagione', sold: 44, share: 38 },
                        { name: 'Gnocchi al Pesto', sold: 32, share: 30 },
                        { name: 'Torta al Cioccolato Vegana', sold: 27, share: 28 }
                    ]
                },
                monthly: {
                    label: 'March 2026',
                    profit: '€ 62,480',
                    highlight: '',
                    orders: '4,812',
                    ordersDetail: 'Average 155 per day',
                    topDishes: [
                        { name: 'Bistecca alla Fiorentina', sold: 522, share: 96 },
                        { name: 'Branzino al Limone', sold: 414, share: 82 },
                        { name: 'Crostata al Caramello Salato', sold: 398, share: 76 }
                    ],
                    lowDishes: [
                        { name: 'Risotto alla Zucca', sold: 133, share: 36 },
                        { name: 'Cavolfiore Piccante', sold: 118, share: 32 },
                        { name: 'Panna Cotta al Pistacchio', sold: 101, share: 30 }
                    ]
                },
                yearly: {
                    label: '2025 (Jan – Dec)',
                    profit: '€ 742,300',
                    highlight: '',
                    orders: '55,418',
                    ordersDetail: '55,418 covers served',
                    topDishes: [
                        { name: 'Percorso Degustazione (7 portate)', sold: 6_830, share: 98 },
                        { name: 'Tagliolini all’Astice', sold: 5_944, share: 85 },
                        { name: 'Tortino Morbido al Pistacchio', sold: 5_311, share: 79 }
                    ],
                    lowDishes: [
                        { name: 'Panino Club Classico', sold: 1_904, share: 44 },
                        { name: 'Gazpacho Mediterraneo', sold: 1_502, share: 34 },
                        { name: 'Antipasto Vegano all’Italiana', sold: 1_211, share: 29 }
                    ]
                }
            };

            const profitEl = document.querySelector('[data-profit]');
            const labelEl = document.querySelector('[data-label]');
            const highlightEl = document.querySelector('[data-highlight]');
            const ordersEl = document.querySelector('[data-orders]');
            const ordersDetailEl = document.querySelector('[data-orders-detail]');
            const topListEl = document.querySelector('[data-top-list]');
            const lowListEl = document.querySelector('[data-low-list]');
            const buttons = document.querySelectorAll('.timeframe-button');

            const renderList = (element, list, soft = false) => {
                element.innerHTML = list.map(item => `
                    <li class="dish-item">
                        <div class="dish-row">
                            <div>
                                <p class="dish-name">${item.name}</p>
                                <p class="dish-meta">${item.share}% of sales</p>
                            </div>
                            <span class="font-semibold text-slate-600">${item.sold.toLocaleString('en-US')}</span>
                        </div>
                        <div class="dish-bar"><span class="${soft ? 'is-soft' : ''}" style="width:${item.share}%"></span></div>
                    </li>
                `).join('');
            };

            const renderRange = (range) => {
                const data = statisticsData[range];
                if (!data) return;

                profitEl.textContent = data.profit;
                labelEl.textContent = data.label;
                highlightEl.textContent = data.highlight;
                ordersEl.textContent = data.orders;
                ordersDetailEl.textContent = data.ordersDetail;

                renderList(topListEl, data.topDishes);
                renderList(lowListEl, data.lowDishes, true);
            };

            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    const range = button.dataset.range;
                    buttons.forEach(btn => btn.classList.toggle('is-active', btn === button));
                    renderRange(range);
                });
            });

            renderRange('daily');
        });
    </script>
</x-app-layout>
