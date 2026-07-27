# 🚀 KaryaOne HRIS

[![Version](https://img.shields.io/badge/version-0.1.0--alpha-blue.svg)](https://github.com/reyhanasta/karyaone/releases)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![React](https://img.shields.io/badge/React-19.x-blue.svg)](https://react.dev)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**KaryaOne** is a modern, high-performance Human Resource Information System (HRIS) specialized for **clinics and hospitals (HRIS Klinik & Rumah Sakit)**. Built to handle complex healthcare operations, it streamlines 24/7 shift rotations, medical staff schedule changes, and multi-tier clinical approval workflows involving roles like Kepala Ruangan (Karu), HRD, and Director.

---

## ✨ Key Features

### 👥 Employee Management

- **Centralized Profiles:** Manage comprehensive employee data, departments, and positions.
- **Document Tracking:** Digital storage and management of employee-specific documents (Employment Letters, Contracts, etc.).
- **Dynamic Org Structure:** Manage hierarchical relationships between departments and positions (aligned with Klinik SOTK).

### 🕒 Attendance & Shift Management

- **Shift Configuration:** Create flexible work schedules (Morning, Afternoon, Night, etc.).
- **Shift Assignments:** Assign schedules to employees with ease.
- **Shift Change Requests:** Integrated workflow for medical staff to request shift swaps or changes.

### 🏖 Leave & Overtime

- **Leave Request System:** Automated quota calculation and multi-level approval.
- **Overtime Management:** Request and track overtime hours with granular display/export controls.
- **Interactive Date Range Filter:** Popover `DateRangePicker` for filtering data by custom start and end date periods across all tables.
- **Instant Filter Reset:** Quick "Reset Filter" action to restore default view state.
- **Export Capabilities:** Generate professional recapitulations in **PDF** and **Excel** formats with period range header tracking.

### 🛡 Core Foundation

- **Advanced Approval Hierarchy:** Customizable approval flows (Karu → HRD → Director).
- **RBAC (Role-Based Access Control):** Granular permissions powered by Spatie (Super Admin, HR Admin, Manager, Director, Karu, Employee).
- **Modern UI/UX:** Clean, responsive interface built with Shadcn UI and Tailwind CSS 4.0 (Dark mode supported).

---

## 📋 Organizational Structure & SOTK

For details on the organizational hierarchy, department mapping, and clinical role assignments, refer to [sotk.md](sotk.md).

---

## 🛠 Tech Stack

- **Backend:** [Laravel 12](https://laravel.com)
- **Frontend:** [React](https://react.dev) via [Inertia.js](https://inertiajs.com)
- **Styling:** [Tailwind CSS 4.0](https://tailwindcss.com) & [Shadcn UI](https://ui.shadcn.com)
- **Database:** MySQL / PostgreSQL
- **Real-time:** [Laravel Reverb](https://reverb.laravel.com)
- **Testing:** [Pest PHP](https://pestphp.com)

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.3+
- Node.js 20+
- Composer
- MySQL 8.0+

### Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/reyhanasta/karyaone.git
   cd KaryaOne
   ```

2. **Install PHP dependencies:**

   ```bash
   composer install
   ```

3. **Install JS dependencies:**

   ```bash
   npm install
   ```

4. **Environment Setup:**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Run Migrations & Seeders:**

   ```bash
   php artisan migrate --seed
   ```

6. **Start Development Server:**

   ```bash
   # Run Laravel & Vite concurrently
   composer run dev
   ```

---

## 🗺 Roadmap

- [x] Core HR Modules (Employee, Dept, Position)
- [x] Shift & Leave Management
- [x] Date Range Filtering & Reset Actions
- [x] Multi-level Approval Workflow
- [x] Document Export (PDF/Excel) with Period Headers
- [ ] **Attendance System (GPS & Geofencing)** - *Upcoming*
- [ ] **Payroll Management** - *Upcoming*
- [ ] Mobile App Integration

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

The KaryaOne HRIS is open-sourced software licensed under the [MIT license](LICENSE).
