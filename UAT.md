# UAT Document — KaryaOne HRIS

> **Project:** KaryaOne Human Resource Information System
> **Version:** 1.0
> **Date:** 2026-05-06
> **Environment:** Production (`http://karyaone.klinikbukitraya.or.id`)

---

## System Context

KaryaOne is a web-based HRIS for managing employees, leave requests, overtime requests, and shift changes. It uses role-based access control (RBAC) with 6 roles.

### Roles

| Role | Code | Description |
|---|---|---|
| Super Admin | `super-admin` | Full system access |
| HR Admin | `hr-admin` | Employee & approval management |
| Manager | `manager` | Department-level oversight & approval |
| Kepala Ruangan | `karu` | First-line approval for their department |
| Direktur | `director` | Final approval for leave requests |
| Karyawan | `employee` | Self-service requests only |

### Approval Flows

| Module | Flow |
|---|---|
| Leave Request | Karu/Manager → HRD → Direktur → Approved |
| Overtime Request | Karu/Manager → HRD → Approved |
| Shift Change Request | Karu/Manager → HRD → Approved |

> **Bypass Rule:** HRD can approve at any pending stage, skipping intermediate steps.
> **Auto-Skip Rule:** If the requester IS a Karu/Manager, the request starts at `pending_hrd` (skips manager stage).

### Status Values

- `pending_manager` — Awaiting Karu/Manager approval
- `pending_hrd` — Awaiting HRD approval
- `pending_director` — Awaiting Director approval (leave only)
- `approved` — Fully approved
- `rejected` — Rejected at any stage
- `canceled` — Canceled by requester

---

## Test Accounts

| # | Role | Email | Password | Notes |
|---|---|---|---|---|
| 1 | Super Admin | `admin@admin.com` | `password` | Default seed account |
| 2 | HR Admin | *(to be created)* | — | Has `hr-admin` role |
| 3 | Manager | *(to be created)* | — | Manages a department |
| 4 | Karu | *(to be created)* | — | Head of a specific room/dept |
| 5 | Employee A | *(to be created)* | — | Under Karu's department |
| 6 | Employee B | *(to be created)* | — | Different department |
| 7 | Direktur | *(to be created)* | — | Has `director` role |

---

## Module 1: Authentication

### TC-AUTH-001: Login with valid credentials
- **Role:** Any
- **Precondition:** User account exists
- **Steps:**
  1. Open app URL
  2. Enter valid email and password
  3. Click "Login"
- **Expected:** User is redirected to Dashboard. Sidebar shows role-appropriate menu items.
- **Status:** ☐ Pass / ☐ Fail

### TC-AUTH-002: Login with invalid credentials
- **Role:** Any
- **Precondition:** None
- **Steps:**
  1. Open app URL
  2. Enter incorrect email or password
  3. Click "Login"
- **Expected:** Error message is displayed. User stays on login page.
- **Status:** ☐ Pass / ☐ Fail

### TC-AUTH-003: Unauthenticated access redirect
- **Role:** Guest (not logged in)
- **Precondition:** Not authenticated
- **Steps:**
  1. Navigate directly to `/dashboard`
- **Expected:** User is redirected to `/login`.
- **Status:** ☐ Pass / ☐ Fail

### TC-AUTH-004: Logout
- **Role:** Any authenticated user
- **Precondition:** User is logged in
- **Steps:**
  1. Click user avatar/menu
  2. Click "Logout"
- **Expected:** User is redirected to login page. Session is destroyed.
- **Status:** ☐ Pass / ☐ Fail

---

## Module 2: Dashboard

### TC-DASH-001: Admin/Manager dashboard shows aggregate stats
- **Role:** `super-admin`, `hr-admin`, or `manager`
- **Precondition:** Logged in with admin/manager role
- **Steps:**
  1. Navigate to Dashboard
- **Expected:** Dashboard shows: Total Employees, Pending Leaves, Pending Overtime, Approved Leaves This Month, Approved Overtime This Month.
- **Status:** ☐ Pass / ☐ Fail

### TC-DASH-002: Employee dashboard shows personal stats
- **Role:** `employee`
- **Precondition:** Logged in as employee with linked employee record
- **Steps:**
  1. Navigate to Dashboard
- **Expected:** Dashboard shows: Leave Quota, Pending Leaves (own), Approved Leaves (own), Pending Overtime (own), Approved Overtime This Month (own).
- **Status:** ☐ Pass / ☐ Fail

### TC-DASH-003: Karu can access dashboard
- **Role:** `karu`
- **Precondition:** Logged in as Karu
- **Steps:**
  1. Click "Dashboard" in sidebar
- **Expected:** Dashboard page loads without error. Dashboard menu item is visible.
- **Status:** ☐ Pass / ☐ Fail

---

## Module 3: Employee Management

### TC-EMP-001: List employees with filters
- **Role:** `super-admin` or `hr-admin`
- **Precondition:** Multiple employees exist
- **Steps:**
  1. Navigate to Employees page
  2. Search by name
  3. Filter by department
  4. Filter by position
  5. Filter by role
- **Expected:** Employee list updates correctly per each filter. Pagination works.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-002: Create new employee
- **Role:** `super-admin` or `hr-admin`
- **Precondition:** Departments, Positions, and Roles exist
- **Steps:**
  1. Click "Tambah Karyawan"
  2. Fill all required fields: NIP, Full Name, Email, Password, Role, Department, Position, Join Date, Leave Quota
  3. Click "Simpan"
- **Expected:** Employee is created. User account is created. Redirected to employee list with success message.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-003: View employee detail
- **Role:** `super-admin` or `hr-admin`
- **Precondition:** Employee exists
- **Steps:**
  1. Click on an employee name in the list
- **Expected:** Detail page shows: profile info, department, position, leave stats (quota, used, remaining), document list, recent leave history.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-004: Edit employee
- **Role:** `super-admin` or `hr-admin`
- **Precondition:** Employee exists
- **Steps:**
  1. Open employee detail
  2. Click "Edit"
  3. Change full name and department
  4. Click "Simpan"
- **Expected:** Employee data is updated. Redirected with success message.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-005: Delete employee
- **Role:** `super-admin` or `hr-admin`
- **Precondition:** Employee exists
- **Steps:**
  1. Click delete button on employee
  2. Confirm deletion
- **Expected:** Employee record is removed. Redirected with success message.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-006: Employee views own profile
- **Role:** `employee`
- **Precondition:** Logged in as employee
- **Steps:**
  1. Navigate to "Profil Saya" / My Profile
- **Expected:** Profile page shows own data (name, department, position, leave stats, documents).
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-007: Employee edits own profile
- **Role:** `employee`
- **Precondition:** Logged in as employee
- **Steps:**
  1. Navigate to "Profil Saya"
  2. Click "Edit"
  3. Change name and/or SIP
  4. Click "Simpan"
- **Expected:** Profile is updated. Redirected with success message.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-008: Import employees via Excel
- **Role:** `super-admin` or `hr-admin`
- **Precondition:** Valid Excel file prepared
- **Steps:**
  1. Navigate to Employees page
  2. Click "Import"
  3. Upload Excel file
- **Expected:** Employees are imported. Success message shows count. Errors (if any) are reported.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-009: Export employees
- **Role:** `super-admin` or `hr-admin`
- **Precondition:** Employees exist
- **Steps:**
  1. Click "Export" on employees page
- **Expected:** Excel file downloads with employee data.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-010: Upload employee document
- **Role:** Any user with `document.upload` permission
- **Precondition:** Employee exists, Document Types are configured
- **Steps:**
  1. Open employee detail
  2. Click upload document
  3. Select document type and file
  4. Submit
- **Expected:** Document is uploaded and visible in employee detail.
- **Status:** ☐ Pass / ☐ Fail

### TC-EMP-011: Unauthorized access to employee management
- **Role:** `employee`
- **Precondition:** Logged in as regular employee
- **Steps:**
  1. Navigate directly to `/employees`
- **Expected:** Access denied (403 Forbidden).
- **Status:** ☐ Pass / ☐ Fail

---

## Module 4: Leave Requests (Pengajuan Cuti)

### TC-LEAVE-001: Employee creates leave request
- **Role:** `employee`
- **Precondition:** Logged in, has leave quota > 0
- **Steps:**
  1. Navigate to "Pengajuan Cuti"
  2. Click "Buat Pengajuan"
  3. Select leave type, start date, end date, fill reason
  4. Submit
- **Expected:** Request is created with status `pending_manager`. Redirected with success. Quota info displayed during creation.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-002: Quota validation — annual leave
- **Role:** `employee`
- **Precondition:** Employee has 2 days remaining quota
- **Steps:**
  1. Create a "Cuti Tahunan" request for 5 days
- **Expected:** Error message: "Kuota cuti tahunan tidak cukup."
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-003: Monthly limit validation
- **Role:** `employee`
- **Precondition:** Employee already used 4 days in current month (limit = 5)
- **Steps:**
  1. Create "Cuti Tahunan" request for 3 days within same month
- **Expected:** Error message about monthly limit exceeded.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-004: Per-type annual quota validation
- **Role:** `employee`
- **Precondition:** Leave type has max_days_per_year = 3, employee already used 2
- **Steps:**
  1. Create leave request of that type for 2 days
- **Expected:** Error message about per-type quota exceeded.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-005: Manager/Karu approves leave
- **Role:** `manager` or `karu`
- **Precondition:** Leave request exists with status `pending_manager`
- **Steps:**
  1. Open leave request detail
  2. Click "Setujui"
  3. Confirm
- **Expected:** Status changes to `pending_hrd`. Approval history shows manager name and timestamp.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-006: HRD approves leave
- **Role:** `hr-admin`
- **Precondition:** Leave request at `pending_hrd`
- **Steps:**
  1. Open leave request detail
  2. Click "Setujui"
- **Expected:** Status changes to `pending_director`.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-007: Director gives final approval
- **Role:** `director`
- **Precondition:** Leave request at `pending_director`
- **Steps:**
  1. Open leave request detail
  2. Click "Setujui"
- **Expected:** Status changes to `approved`. Employee's leave quota is decremented (for Cuti Tahunan).
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-008: HRD bypass approval from pending_manager
- **Role:** `hr-admin`
- **Precondition:** Leave request at `pending_manager`
- **Steps:**
  1. Open leave request detail
  2. Click "Setujui"
- **Expected:** Status changes to `pending_director` (skipping HRD stage too). Both manager and HRD columns are filled.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-009: Reject leave request
- **Role:** `manager`, `hr-admin`, or `director`
- **Precondition:** Leave request at any pending status
- **Steps:**
  1. Open leave request detail
  2. Click "Tolak"
  3. Confirm
- **Expected:** Status changes to `rejected`. No further approval possible.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-010: Employee cancels own leave request
- **Role:** `employee`
- **Precondition:** Own leave request at any `pending_*` status
- **Steps:**
  1. Open leave request detail
  2. Click "Batalkan"
  3. Confirm
- **Expected:** Status changes to `canceled`.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-011: Employee cannot cancel others' request
- **Role:** `employee`
- **Precondition:** Leave request belongs to another employee
- **Steps:**
  1. Attempt to cancel via direct URL
- **Expected:** 403 Forbidden.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-012: Karu/Manager cannot cancel request
- **Role:** `karu` or `manager`
- **Precondition:** Leave request at pending status
- **Steps:**
  1. Open leave request detail
- **Expected:** Cancel button is NOT shown. Only approve/reject is available.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-013: Karu creates leave for department member
- **Role:** `karu`
- **Precondition:** Karu manages a department with employees
- **Steps:**
  1. Navigate to "Buat Pengajuan Cuti"
  2. Select an employee from dropdown
  3. Fill form and submit
- **Expected:** Request is created for selected employee. Status starts at `pending_hrd` (auto-skips manager stage).
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-014: Employee can only see own requests
- **Role:** `employee`
- **Precondition:** Multiple employees have requests
- **Steps:**
  1. Navigate to leave request index
- **Expected:** Only own requests are shown. Cannot see other employees' requests.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-015: Karu sees only department requests
- **Role:** `karu`
- **Precondition:** Requests exist in and outside managed department
- **Steps:**
  1. Navigate to leave request index
- **Expected:** Only requests from managed department (and own) are visible.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-016: Filter leave requests
- **Role:** `hr-admin`
- **Precondition:** Multiple requests exist
- **Steps:**
  1. Filter by status (pending, approved, rejected)
  2. Filter by date range
  3. Filter by leave type
  4. Search by employee name
- **Expected:** Results filter correctly for each criterion.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-017: Export leave requests to Excel
- **Role:** `hr-admin`
- **Steps:**
  1. Click "Export Excel" on leave index
- **Expected:** Excel file downloads with filtered data.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-018: Export leave requests to PDF
- **Role:** `hr-admin`
- **Steps:**
  1. Click "Export PDF" on leave index
- **Expected:** PDF file downloads with data.
- **Status:** ☐ Pass / ☐ Fail

### TC-LEAVE-019: Upload attachment with leave request
- **Role:** `employee`
- **Steps:**
  1. Create leave request
  2. Attach a file (e.g., doctor's note)
  3. Submit
- **Expected:** Attachment is saved and viewable on detail page.
- **Status:** ☐ Pass / ☐ Fail

---

## Module 5: Overtime Requests (Pengajuan Lembur)

### TC-OT-001: Employee creates overtime request
- **Role:** `employee`
- **Precondition:** Logged in as employee
- **Steps:**
  1. Navigate to "Pengajuan Lembur"
  2. Click "Buat Pengajuan"
  3. Fill: date, start time, end time, description
  4. Submit
- **Expected:** Request created with status `pending_manager`. Redirected with success.
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-002: Prevent duplicate overtime for same date
- **Role:** `employee`
- **Precondition:** Overtime request already exists for today
- **Steps:**
  1. Create another overtime for the same date
- **Expected:** Validation error: "This employee already has an overtime request for this date."
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-003: Manager approves overtime
- **Role:** `manager` or `karu`
- **Precondition:** Overtime at `pending_manager`
- **Steps:**
  1. Open overtime detail
  2. Click "Setujui"
- **Expected:** Status changes to `pending_hrd`.
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-004: HRD approves overtime
- **Role:** `hr-admin`
- **Precondition:** Overtime at `pending_hrd`
- **Steps:**
  1. Open overtime detail
  2. Click "Setujui"
- **Expected:** Status changes to `approved`.
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-005: HRD bypass from pending_manager
- **Role:** `hr-admin`
- **Precondition:** Overtime at `pending_manager`
- **Steps:**
  1. Open overtime detail
  2. Click "Setujui"
- **Expected:** Status changes directly to `approved`. Both manager and HRD columns are filled.
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-006: Reject overtime request
- **Role:** `manager` or `hr-admin`
- **Precondition:** Overtime at any pending status
- **Steps:**
  1. Click "Tolak" and confirm
- **Expected:** Status changes to `rejected`.
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-007: Employee cancels own overtime
- **Role:** `employee`
- **Precondition:** Own overtime at pending status
- **Steps:**
  1. Click "Batalkan" and confirm
- **Expected:** Status changes to `canceled`.
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-008: Toggle export visibility
- **Role:** `hr-admin`
- **Precondition:** Approved overtime exists
- **Steps:**
  1. Toggle "Export" switch on overtime detail
- **Expected:** `is_display_export` value flips. Only toggled items appear in export.
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-009: Cannot modify after final status
- **Role:** Any approver
- **Precondition:** Overtime is `approved` or `rejected`
- **Steps:**
  1. Attempt to change status via detail page or direct URL
- **Expected:** Error message: "This request has already been processed completely."
- **Status:** ☐ Pass / ☐ Fail

### TC-OT-010: Export overtime to Excel and PDF
- **Role:** `hr-admin` or `manager`
- **Steps:**
  1. Click "Export Excel"
  2. Click "Export PDF"
- **Expected:** Both files download with correct data.
- **Status:** ☐ Pass / ☐ Fail

---

## Module 6: Shift Change Requests (Tukar Shift)

### TC-SHIFT-001: Employee creates shift change request
- **Role:** `employee`
- **Precondition:** Active shifts exist, target employee with same position exists
- **Steps:**
  1. Navigate to "Pengajuan Tukar Shift"
  2. Click "Buat Pengajuan"
  3. Select: date, own shift, target employee, reason
  4. Submit
- **Expected:** Request created with `pending_manager`. Target employee dropdown only shows employees with same position.
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-002: Prevent duplicate shift change
- **Role:** `employee`
- **Precondition:** Pending request exists for same date and target
- **Steps:**
  1. Create another request with same parameters
- **Expected:** Error: "Permintaan penggantian shift untuk tanggal dan rekan tersebut sudah ada."
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-003: Manager/Karu approves shift change
- **Role:** `manager` or `karu`
- **Precondition:** Request at `pending_manager`
- **Steps:**
  1. Open detail, click "Setujui"
- **Expected:** Status changes to `pending_hrd`.
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-004: HRD approves shift change
- **Role:** `hr-admin`
- **Precondition:** Request at `pending_hrd`
- **Steps:**
  1. Open detail, click "Setujui"
- **Expected:** Status changes to `approved`.
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-005: HRD bypass from pending_manager
- **Role:** `hr-admin`
- **Precondition:** Request at `pending_manager`
- **Steps:**
  1. Click "Setujui"
- **Expected:** Status jumps to `approved`. Both manager and HRD columns filled.
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-006: Reject with notes
- **Role:** `manager` or `hr-admin`
- **Precondition:** Request at pending status
- **Steps:**
  1. Click "Tolak"
  2. Fill rejection notes (required, max 500 chars)
  3. Confirm
- **Expected:** Status = `rejected`. Notes are saved and visible.
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-007: Employee cancels own shift change
- **Role:** `employee`
- **Precondition:** Own request at pending status
- **Steps:**
  1. Click "Batalkan"
- **Expected:** Status = `canceled`.
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-008: Employee sees only involved requests
- **Role:** `employee`
- **Steps:**
  1. Navigate to shift change index
- **Expected:** Only requests where user is requester OR target are visible.
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-009: Export shift changes to Excel/PDF
- **Role:** User with `shift-change-request.export` permission
- **Steps:**
  1. Click "Export Excel"
  2. Click "Export PDF"
- **Expected:** Files download correctly.
- **Status:** ☐ Pass / ☐ Fail

---

## Module 7: Master Data Management

### TC-DEPT-001: CRUD Departments
- **Role:** `super-admin` or `hr-admin`
- **Steps:**
  1. Navigate to Departments
  2. Create new department
  3. Edit department name
  4. Delete a department (with no linked employees)
- **Expected:** All CRUD operations work. Validation prevents empty names.
- **Status:** ☐ Pass / ☐ Fail

### TC-POS-001: CRUD Positions
- **Role:** `super-admin` or `hr-admin`
- **Steps:**
  1. Navigate to Positions
  2. Create, edit, and delete a position
- **Expected:** All CRUD operations work correctly.
- **Status:** ☐ Pass / ☐ Fail

### TC-LT-001: CRUD Leave Types
- **Role:** `super-admin` or `hr-admin`
- **Steps:**
  1. Navigate to Leave Types
  2. Create leave type with name and max_days_per_year
  3. Edit it
  4. Toggle active/inactive
- **Expected:** Leave types are manageable. Only active types appear in leave request form.
- **Status:** ☐ Pass / ☐ Fail

### TC-DT-001: CRUD Document Types
- **Role:** `super-admin` or `hr-admin`
- **Steps:**
  1. Navigate to Document Types
  2. Create with name and assigned positions (use "Select All" feature)
  3. Edit, toggle active
- **Expected:** Document types work. Only types matching employee's position appear on their profile.
- **Status:** ☐ Pass / ☐ Fail

### TC-SHIFT-M-001: CRUD Shifts
- **Role:** `super-admin` or `hr-admin`
- **Steps:**
  1. Navigate to Shifts
  2. Create shift (name, start_time, end_time, department, is_active)
  3. Edit and toggle active
- **Expected:** Shifts are manageable. Only active shifts appear in shift change form.
- **Status:** ☐ Pass / ☐ Fail

---

## Module 8: Notifications & UI

### TC-NOTIF-001: Notification on request submission
- **Role:** `employee` (submitter), `manager`/`karu` (receiver)
- **Precondition:** Employee submits a leave/overtime/shift request
- **Steps:**
  1. Employee submits a request
  2. Manager checks notification bell
- **Expected:** Manager receives real-time notification about the new request.
- **Status:** ☐ Pass / ☐ Fail

### TC-NOTIF-002: Notification on approval/rejection
- **Role:** Approver (actor), Employee (receiver)
- **Steps:**
  1. Approver approves or rejects a request
  2. Employee checks notifications
- **Expected:** Employee receives notification about status change.
- **Status:** ☐ Pass / ☐ Fail

### TC-NOTIF-003: Mark notification as read
- **Role:** Any
- **Steps:**
  1. Click on notification
  2. Or click "Mark all as read"
- **Expected:** Notification count decreases. Read notifications are marked.
- **Status:** ☐ Pass / ☐ Fail

### TC-UI-001: Dark mode / Light mode
- **Role:** Any
- **Steps:**
  1. Toggle theme in settings
- **Expected:** All pages render correctly in both modes. No broken colors or unreadable text.
- **Status:** ☐ Pass / ☐ Fail

### TC-UI-002: Approval history timeline
- **Role:** Any
- **Precondition:** A request with multi-level approval exists
- **Steps:**
  1. Open any approved request detail
- **Expected:** Approval timeline shows each stage with approver name, role, timestamp, and correct status (approved/bypassed/pending).
- **Status:** ☐ Pass / ☐ Fail

### TC-UI-003: Confirmation modal on destructive actions
- **Role:** Any
- **Precondition:** A request exists
- **Steps:**
  1. Click approve, reject, or cancel on any request
- **Expected:** A styled confirmation modal appears (NOT browser native `confirm()`). User must confirm before action proceeds.
- **Status:** ☐ Pass / ☐ Fail

### TC-UI-004: Responsive sidebar navigation
- **Role:** Any
- **Steps:**
  1. Check sidebar on desktop (>1024px)
  2. Check on mobile viewport (<768px)
- **Expected:** Sidebar collapses on mobile. All menu items accessible. Role-based items hidden/shown correctly.
- **Status:** ☐ Pass / ☐ Fail

---

## UAT Sign-Off

| Role | Tester Name | Date | Signature |
|---|---|---|---|
| Product Owner | | | |
| HR Representative | | | |
| IT Lead | | | |
| End User (Employee) | | | |
| End User (Karu) | | | |

### Result Summary

| Module | Total Cases | Pass | Fail | Blocked |
|---|---|---|---|---|
| Authentication | 4 | | | |
| Dashboard | 3 | | | |
| Employee Management | 11 | | | |
| Leave Requests | 19 | | | |
| Overtime Requests | 10 | | | |
| Shift Change Requests | 9 | | | |
| Master Data | 5 | | | |
| Notifications & UI | 4 | | | |
| **TOTAL** | **65** | | | |

> **Acceptance Criteria:** All test cases must PASS. Any FAIL requires a fix and re-test before production sign-off.
