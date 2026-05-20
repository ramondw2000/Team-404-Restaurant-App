# Sales Statistics

This dashboard summarises completed orders into business-readable numbers. Use it for daily reports, weekly catch-ups, and longer-term trend spotting.

## Period Selector

The **Today / Week / Month / Year** buttons in the top-right scope every number on the page.

| Period | Window |
| --- | --- |
| **Today** | Just today (00:00 → now) |
| **Week** | The last 7 days |
| **Month** | The current calendar month |
| **Year** | The current calendar year |

Switching the period reloads every chart, KPI, and list with the new window. The "Live · …" timestamp in the corner shows when the data was last refreshed.

## KPI Cards

Three top-level numbers, big and bold:

- **Total Sales** — Sum of € from every completed order in the period. *Cancelled and pending orders are excluded.*
- **Average Order Value** — Total sales ÷ order count. Useful for spotting whether revenue is growing because of more orders or bigger orders.
- **Completed Orders** — Raw count of finished orders. Good for staffing decisions.

## Channel Performance

Breaks total sales down by **where** the order came from:

- **Restaurant** — Tables, sit-down service
- **Bar** — Walk-up bar orders
- (Any other channels your installation tracks)

Each card shows the channel's revenue, order count, share % of period revenue, and a progress bar. Look here to answer questions like *"Is the bar still pulling its weight?"*

## Dish Performance

Two halves: **Most Sold** and **Least Sold**.

### Most Sold (green)
Top dishes by units sold. The badge shows total revenue for that dish in the period. A consistent #1 is a good sign your menu has a winner.

### Least Sold (amber)
The slowest movers. Look here to spot:
- Menu items to retire or rework
- Items priced too high
- Items the staff aren't suggesting

A dish at the bottom of *Least Sold* for several periods running is a candidate for the chop.

## Recent Highlights (right column)

A vertical list of the most recent completed orders. Each row shows:
- Order number / table or *walk-up*
- Items count
- Order total
- Time completed

Use this to spot-check the day or share with a manager doing the books.

## Reading the Numbers Honestly

- **Period changes the baseline.** A €500 Today might be normal; €500 Year is alarming. Always check the selected period.
- **Average Order Value cuts both ways.** Climbing AOV means either bigger orders or higher prices — drill into Dish Performance to see which.
- **Channel share is relative.** If Bar share rose from 20% → 30%, that may be Restaurant declining, not Bar booming. Always look at absolute € too.
- **Least Sold needs context.** A new dish added yesterday will be at the bottom of Most Sold for *Today* — give it a Week before judging.

## Tips

- **Snapshot weekly.** Screenshot or copy the KPIs every Sunday for a week-over-week comparison.
- **Cross-check with floor data.** Reservations + table turn rate sit on the Reservations page; combine with sales to compute revenue per table.
- **Flag anomalies.** A sudden drop in Channel A might be a broken POS — investigate before assuming guests changed habits.
