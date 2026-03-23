---
description: Completed Order management table for restaurant managers
---

# Completed Order Management Table Specification

## Overview
A Livewire data table component for managers to view, filter, search, and export completed restaurant orders. Located on the statistics page.

## Data Model
- **Status Definition:** Orders with `status = 'completed'`
- **Default Sort:** Completion date descending (newest first)

## Columns
| Column | Data | Formatting |
|--------|------|------------|
| Order ID | `order_id` | Plain text |
| Location | `location.name` | Plain text |
| Waiter | `waiter.name` | Plain text |
| Items | Count of items | "{count} items" |
| Total | `total_amount` | Currency (€X.XX) |
| Closed | `completed_at` | Relative date ("2 min ago", "1 hour ago") |

## Row Color Coding
- **Green:** High-value orders (>€100)
- **Red:** Refunded orders (`is_refunded = true`)
- **Yellow:** Orders completed >30 minutes ago (stale)
- **Default:** No special styling

## Search
- **Scope:** Order ID, Customer name, Waiter name, Location name
- **Type:** Debounced text search (300ms delay)
- **Placeholder:** "Search by order, customer, waiter, or location..."

## Filters

### Date Range
- Presets: Today, Yesterday, This Week, This Month, Last Month, Custom Range
- Default: Today

### Additional Filters
- Payment Method (dropdown)
- Location (multi-select)
- Waiter (multi-select)
- Order Type: Dine-in, Takeaway, Delivery

## Pagination
- Options: 10, 25, 50 rows per page
- Default: 25
- Clickable toggle buttons (10 | 25 | 50)

## Row Actions (Per Order)
- **View Receipt:** Opens modal with full order details
- **Print:** Direct print receipt

## Bulk Actions
- **Batch Print:** Print receipts for all selected orders
- **Export CSV:** Download selected orders as CSV

## Receipt/Invoice Modal Content
- Order header: ID, completion date, location, waiter, customer name
- Itemized list: Item name, quantity, unit price, line total
- Summary: Subtotal, tax, total
- Payment method
- Footer: Restaurant thank you message

## Row Selection
- Checkbox per row
- "Select All" for current page
- Show selected count when >0 selected

## Access Control
- **Role:** Managers only
- **View Only:** No edit/delete capabilities

## UI Requirements
- Sticky header
- Loading skeleton state on data fetch
- Empty state: "No completed orders found for the selected criteria"
- Selected rows: Visual highlight
- Responsive: Horizontal scroll on mobile

## Technical Notes
- Livewire 4 component with reactive filters
- No real-time updates (manual refresh or filter change triggers reload)
- CSV export uses streaming for large datasets
- Print uses browser print API with receipt-optimized CSS