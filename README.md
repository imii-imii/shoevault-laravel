
# ShoeVault – Web-Based Operations Management System

**ShoeVault** is a web-based operations management system designed for **Shoe Vault Batangas**, a retail shoe business. The system automates key operations such as sales transactions, inventory management, pickup-only reservations, and provides descriptive and predictive analytics for data-driven decision-making.

---

## Features

* **Point of Sale (POS) Module** – Automates in-store sales, calculates totals, applies discounts, and updates inventory in real time.
* **Reservation System (Pickup Only)** – Allows customers to reserve items online for in-store pickup.
* **Inventory Management** – Provides a centralized view of stock levels with automatic updates.
* **Supplier Management** – Tracks supplier deliveries for timely restocking.
* **Analytics Dashboard** – Offers descriptive and predictive analytics to monitor sales, inventory, and product performance.
* **Automated Reporting** – Generates daily and weekly sales and inventory reports.
* **User Role Management** – Supports distinct access levels for Customers, Cashiers, Managers, and Owners.
* **Data Security** – Implements encrypted logins, role-based access control, and secure database storage.

---

## Technologies Used

| Component               | Technology              |
| ----------------------- | ----------------------- |
| Frontend                | HTML, CSS, JavaScript   |
| Backend                 | PHP (Laravel Framework) |
| Database                | MySQL                   |
| Hosting                 | Hostinger Web Server    |
| Data Visualization      | Chart.js / Power BI     |
| Development Methodology | Agile                   |
| Version Control         | GitHub                  |

---

## System Requirements

### Hardware

* Processor: Intel Core i3 or higher
* Memory: 4 GB RAM
* Storage: 500 GB HDD or 120 GB SSD
* Display: 1366 × 768 resolution
* Network: Stable Internet connection

### Software

* Operating System: Windows 10 / macOS / Linux
* Web Browser: Google Chrome (recommended)
* Local Server: XAMPP with PHP
* Database: MySQL

---

## User Roles

1. **Customer** – Browse products, filter by categories, reserve items for pickup, receive reservation confirmation.
2. **Cashier** – Record in-store sales, confirm pickup reservations, calculate totals, discounts, and update inventory.
3. **Manager** – Add/update stock, manage suppliers, monitor reservations, generate and export reports.
4. **Owner** – Access analytics dashboard, monitor real-time sales, inventory, product performance, and forecast demand.

---

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/yourusername/shoevault.git
   ```
2. Navigate to the project folder:

   ```bash
   cd shoevault
   ```
3. Install dependencies via Composer:

   ```bash
   composer install
   ```
4. Copy `.env.example` and configure environment variables:

   ```bash
   cp .env.example .env
   ```
5. Generate application key:

   ```bash
   php artisan key:generate
   ```
6. Run migrations:

   ```bash
   php artisan migrate
   ```
7. Start the development server:

   ```bash
   php artisan serve
   ```
8. Open in browser: `http://localhost:8000`

---

## Analytics Dashboard

* **Descriptive Analytics:** View past and current sales, inventory, and customer data.
* **Predictive Analytics:** Forecast sales trends and demand using historical data.
* **Data Visualization:** Charts and graphs for quick interpretation.
* **Performance Indicators:** Identify top-selling products and peak sales periods.

---

## Maintenance Recommendations

* Weekly backup of sales and inventory data.
* Monthly report validation by Manager.
* Quarterly software updates by Developer.

---

## Troubleshooting

| Issue                      | Possible Cause       | Solution                                    |
| -------------------------- | -------------------- | ------------------------------------------- |
| Login failed               | Wrong credentials    | Check username/password or reset login      |
| Inventory not updating     | Database delay       | Refresh page or reconnect to server         |
| Dashboard not loading      | Browser cache issue  | Clear cache and reload page                 |
| Reservation not confirming | Network/server delay | Wait and refresh module                     |
| POS freezing               | Low internet speed   | Ensure stable connection or restart browser |

---

## Security & Policies

* Always log out after each session.
* Only authorized personnel can access Manager and Owner modules.
* Do not share passwords; regularly update credentials.
* Follow the store’s data privacy policy in accordance with the Data Privacy Act of 2012 (RA 10173).

---
