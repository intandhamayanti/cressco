# Cressco - User Flows

> **Document:** USER-FLOWS.md
> **Product:** Cressco
> **Scope:** Owner, Admin, Tutor/Tentor, Super Admin
> **Primary Focus:** Core Bimbel MVP
> **Platform Scope:** Super Admin Phase 2

---

# 1. Document Purpose

Dokumen ini mendefinisikan bagaimana pengguna berinteraksi dengan Cressco untuk menyelesaikan pekerjaan utama mereka.

PRD menjelaskan **apa yang harus dimiliki Cressco**.

Dokumen ini menjelaskan **bagaimana pengguna bergerak melalui sistem**.

User flow di dokumen ini digunakan sebagai dasar untuk:

- desain UI,
- struktur navigasi,
- page hierarchy,
- state dan action,
- authorization,
- business rules,
- ERD,
- database schema,
- dan implementasi Laravel/Livewire.

---

# 2. Flow Conventions

Notasi:

```text
↓
Next step

├──
Branch / decision

[Action]
User action

(System)
System action

✓
Success state

⚠
Exception / warning
```

Terminologi utama:

- **Tenant** = satu organisasi/bimbel di dalam Cressco.
- **Branch** = cabang di dalam tenant.
- **Owner** = pemilik bimbel.
- **Admin** = operator operasional bimbel.
- **Tutor** = pengajar.
- **Class** = kelompok pembelajaran.
- **Schedule** = aturan jadwal berulang.
- **Teaching Session** = kejadian mengajar aktual pada tanggal tertentu.
- **Scheduled Tutor** = Tutor yang dijadwalkan secara normal.
- **Actual Tutor** = Tutor yang benar-benar mengajar pada session tersebut.

---

# 3. Global User Flow

Flow umum pengguna:

```text
Open Cressco
↓
Authentication
↓
Identify User
↓
Resolve Tenant
↓
Resolve Role
↓
Resolve Branch Context
↓
Role Dashboard
↓
Role-specific Workflow
```

Authorization dilakukan pada setiap protected resource.

User tidak boleh mendapatkan data di luar:

```text
Tenant Scope
+
Role Permission
+
Branch Scope
+
Resource Scope
```

---

# 4. Authentication Flow

## 4.1 Login

```text
User
↓
Open Login
↓
Enter Email
↓
Enter Password
↓
[Login]
↓
System validates credentials
├── Invalid
│   ↓
│   Show validation error
│   ↓
│   Stay on Login
│
└── Valid
    ↓
    Resolve User
    ↓
    Resolve Tenant
    ↓
    Resolve Role
    ↓
    Resolve available Branch Access
    ↓
    Redirect to Role Dashboard
```

## 4.2 Logout

```text
Authenticated User
↓
[Logout]
↓
System invalidates authenticated session
↓
Redirect to Login
```

## 4.3 Unauthorized Access

```text
User
↓
Open protected resource
↓
System checks authorization
├── Authorized
│   ↓
│   Show resource
│
└── Unauthorized
    ↓
    Deny access
    ↓
    Show appropriate authorization response
```

User tidak boleh hanya mengandalkan hidden navigation sebagai security mechanism.

---

# 5. Tenant Context Flow

## 5.1 Resolve Tenant

Target tenant architecture:

```text
{tenant_slug}.cressco.app
```

Flow:

```text
Request
↓
Read Hostname
↓
Resolve tenant_slug
↓
Find Tenant
├── Found + Active
│   ↓
│   Set Tenant Context
│   ↓
│   Continue Request
│
├── Not Found
│   ↓
│   Show Tenant Not Found
│
└── Suspended/Inactive
    ↓
    Block Tenant Access
```

---

# 6. Branch Context Flow

Branch context digunakan untuk menentukan data branch yang sedang ditampilkan.

## 6.1 Owner

Owner memiliki seluruh branch.

```text
Owner Dashboard
↓
Branch Selector
├── Semua Cabang
│   ↓
│   Show aggregated tenant data
│
├── Surabaya
│   ↓
│   Show Surabaya data
│
├── Gresik
│   ↓
│   Show Gresik data
│
└── Lamongan
    ↓
    Show Lamongan data
```

Changing branch context tidak mengubah permission Owner.

## 6.2 Admin

```text
Admin Login
↓
Resolve Branch Access
├── One Branch
│   ↓
│   Use assigned branch
│   ↓
│   Branch selector may be hidden
│
└── Multiple Branches
    ↓
    Show Branch Selector
    ↓
    User selects branch
    ↓
    Validate access
    ↓
    Load selected branch context
```

Admin tidak dapat memilih branch yang tidak termasuk access scope.

## 6.3 Tutor

Tutor tidak menggunakan branch access sebagai permission utama.

```text
Tutor
↓
View assigned Classes
↓
Class
↓
Branch
↓
Teaching Sessions
```

Branch Tutor berasal dari class assignment.

---

# 7. OWNER FLOWS

# 7.1 Owner First-Time Setup

Tujuan: menyiapkan tenant sebelum operasional berjalan.

```text
Owner Login
↓
Owner Dashboard
↓
Tenant Settings
↓
Complete Bimbel Information
├── Name
├── Logo
├── Contact
├── Address
└── Business Information
↓
Branch Management
↓
Create Branch
↓
Honor Configuration
↓
Set Default Honor Scheme
↓
Payment Configuration
↓
Set Payment Due Date
↓
Admin Management
↓
Invite Admin
↓
Assign Branch Access
↓
Tenant Ready
```

Success state:

```text
Tenant
✓ Configured
✓ Branch configured
✓ Honor policy configured
✓ Payment policy configured
✓ Admin invited
```

---

# 7.2 Owner Manage Branch

```text
Owner
↓
Branch Management
↓
View Branch List
↓
[Create Branch]
↓
Enter Branch Information
↓
[Save]
↓
Validate
├── Invalid
│   ↓
│   Show validation errors
│
└── Valid
    ↓
    Create Branch
    ↓
    Return to Branch List
```

Edit:

```text
Branch List
↓
Select Branch
↓
[Edit]
↓
Update Information
↓
[Save]
↓
Branch Updated
```

Deactivate:

```text
Branch Detail
↓
[Deactivate]
↓
Confirmation
↓
[Confirm]
↓
Branch becomes inactive
```

---

# 7.3 Owner Invite Admin

```text
Owner
↓
Users
↓
Admin
↓
[Invite Admin]
↓
Enter Admin Information
↓
Select Branch Access
├── One Branch
└── Multiple Branches
↓
[Send Invitation]
↓
System creates/invites Admin
↓
Invitation Status
├── Pending
├── Accepted
└── Expired
```

Owner menentukan branch access Admin.

Owner tidak membuat role berdasarkan branch.

Contoh:

```text
Role = Admin
Branch Access = Surabaya + Gresik
```

bukan:

```text
Role = Admin Surabaya
```

---

# 7.4 Owner Monitor Dashboard

```text
Owner Login
↓
Dashboard
↓
Select Branch Context
↓
View KPI
├── Active Students
├── Classes
├── Tutors
├── Admins
├── Revenue
├── Outstanding
├── Tutor Honor
├── Sessions
└── Attendance
↓
View Trends / Overview
↓
Drill Down
├── Students
├── Classes
├── Tutors
├── Payment
└── Honor
```

Owner dashboard berfungsi sebagai business overview, bukan operational task list.

---

# 7.5 Owner View Student

```text
Owner
↓
Siswa
↓
Student List
↓
Select Student
↓
Student Detail
├── Overview
├── Classes
├── Attendance
├── Payment
├── Assessment
└── History
```

Owner memiliki oversight.

Daily student CRUD tetap merupakan workflow Admin.

---

# 7.6 Owner View Class

```text
Owner
↓
Kelas
↓
Class List
↓
Select Class
↓
Class Detail
├── Overview
├── Students
├── Tutors
├── Schedule
└── Sessions
```

Owner dapat melihat kondisi class tanpa harus menjadi operator harian.

---

# 7.7 Owner View Tutor

```text
Owner
↓
Tutor
↓
Tutor List
↓
Select Tutor
↓
Tutor Detail
├── Profile
├── Assigned Classes
├── Schedule
├── Sessions
├── Replacement History
└── Honor
```

Owner memiliki oversight terhadap Tutor.

---

# 7.8 Owner Configure Honor

```text
Owner
↓
Honor Tutor
↓
Honor Configuration
↓
Configure Default Scheme
├── Per Sesi
├── Per Siswa
├── Revenue Share
└── Fixed Monthly
↓
Set Rate / Percentage / Amount
↓
Set Effective Date
↓
[Save]
↓
Configuration Active
```

Tutor override:

```text
Honor Configuration
↓
Tutor Override
↓
Select Tutor
↓
Select Scheme
↓
Set Value
↓
Set Effective Date
↓
[Save]
↓
Override Active
```

Future configuration tidak mengubah histori sebelumnya.

---

# 7.9 Owner Financial Oversight

```text
Owner
↓
Pembayaran
↓
Select Period
↓
Select Branch Context
↓
View
├── Paid
├── Outstanding
└── Overdue
↓
Drill Down
↓
Payment Details
```

Owner melihat financial data.

Admin tetap menjalankan daily payment operations.

---

# 7.10 Owner Honor Oversight

```text
Owner
↓
Honor Tutor
↓
Select Period
↓
Select Branch
↓
View Honor Summary
↓
Select Tutor
↓
View Honor Calculation
├── Scheme
├── Effective Date
├── Source Sessions / Students / Revenue
├── Adjustment
└── Final Amount
```

Owner tidak perlu menjalankan operational calculation setiap periode.

---

# 7.11 Owner Reports

```text
Owner
↓
Laporan
↓
Select Report
├── Business
├── Student
├── Attendance
├── Tutor
├── Financial
└── Branch Comparison
↓
Select Period
↓
Select Branch Context
↓
Generate / View Report
↓
Drill Down
```

---

# 7.12 Owner Tenant Settings

```text
Owner
↓
Pengaturan Bimbel
↓
Select Setting
├── Business Information
├── Payment
├── Honor
└── Operational Settings
↓
Edit
↓
Validate
↓
Save
↓
Settings Updated
```

---

# 8. ADMIN FLOWS

# 8.1 Admin Daily Start

```text
Admin Login
↓
Admin Dashboard
↓
Select Branch Context if needed
↓
View
├── Today's Sessions
├── Attendance Alerts
├── Payment Alerts
├── Overdue
└── Quick Actions
↓
Choose Operational Task
```

---

# 8.2 Add Student

```text
Admin
↓
Siswa
↓
Student List
↓
[Tambah Siswa]
↓
Enter Student Information
↓
Enter Parent/Guardian Information
↓
Select Branch
↓
[Save]
↓
Validate
├── Invalid
│   ↓
│   Show errors
│
└── Valid
    ↓
    Create Student
    ↓
    Student Detail
```

---

# 8.3 Student Enrollment

```text
Admin
↓
Student Detail
↓
Kelas / Enrollment
↓
[Tambah Enrollment]
↓
Select Class
↓
Set Start Date
↓
Set Status
↓
[Save]
↓
Enrollment Created
```

Student dapat memiliki lebih dari satu enrollment.

Example:

```text
Student A
├── Mathematics
├── English
└── Physics
```

---

# 8.4 Create Class

```text
Admin
↓
Kelas
↓
[Tambah Kelas]
↓
Enter
├── Class Name
├── Subject
├── Level
├── Branch
└── Capacity
↓
[Save]
↓
Class Created
```

---

# 8.5 Add / Invite Tutor

```text
Admin
↓
Tutor
↓
[Tambah / Invite Tutor]
↓
Enter Tutor Information
↓
[Send Invitation]
↓
Tutor Account / Invitation Created
↓
Tutor appears in Tutor List
```

Owner memiliki oversight terhadap Tutor, tetapi Admin menjadi operator utama.

---

# 8.6 Assign Tutor to Class

```text
Admin
↓
Class Detail
↓
Tutor Assignment
↓
[Assign Tutor]
↓
Select Tutor
↓
Set Assignment
↓
[Save]
↓
Tutor assigned to Class
```

Satu Class dapat memiliki lebih dari satu Tutor.

---

# 8.7 Create Recurring Schedule

```text
Admin
↓
Jadwal
↓
[Tambah Jadwal]
↓
Select Class
↓
Select Scheduled Tutor
↓
Select Day
↓
Set Start Time
↓
Set End Time
↓
Set Room
↓
Set Effective Period
↓
[Save]
↓
Recurring Schedule Created
↓
System generates / prepares Teaching Sessions
```

Schedule dibuat sebagai aturan.

Admin tidak perlu memasukkan jadwal setiap minggu.

---

# 8.8 Teaching Session Generation

```text
Recurring Schedule
↓
System evaluates schedule
↓
Create concrete Teaching Session
↓
Teaching Session stores
├── Date
├── Class
├── Branch
├── Scheduled Tutor
├── Time
└── Room
```

Concept:

```text
Schedule:
Every Monday 19.00

↓
Sessions:
5 Oct 19.00
12 Oct 19.00
19 Oct 19.00
```

---

# 8.9 One-Time Schedule Change

Jika hanya satu sesi berubah:

```text
Admin
↓
Teaching Session
↓
Select Session
↓
[Edit Session]
↓
Change Date / Time / Room
↓
[Save]
↓
Session Updated
```

Recurring Schedule tetap sama.

---

# 8.10 Cancel One Session

```text
Admin
↓
Teaching Session
↓
Select Session
↓
[Cancel Session]
↓
Confirmation
↓
[Confirm]
↓
Session status = Dibatalkan
```

Recurring Schedule tidak ikut berubah.

---

# 8.11 Tutor Replacement

MVP tidak menggunakan formal request/approval workflow.

```text
Tutor berhalangan
↓
Tutor communicates with Admin
↓
Admin opens affected Teaching Session
↓
[Change Tutor]
↓
Select Replacement Tutor
↓
Enter Reason
↓
[Save]
↓
Scheduled Tutor remains original
↓
Actual Tutor becomes replacement
↓
Replacement history recorded
```

Contoh:

```text
Scheduled Tutor = Budi
Actual Tutor = Sinta
Reason = Tutor berhalangan
```

Future recurring sessions tetap menggunakan Scheduled Tutor dari schedule normal.

---

# 8.12 Attendance Oversight

```text
Admin
↓
Absensi
↓
Select Date / Class / Session
↓
View Attendance
↓
Select Student
↓
[Correction]
↓
Change Status
↓
Enter reason if required
↓
[Save]
↓
Attendance Updated
```

Admin tidak perlu menggantikan flow Tutor untuk attendance normal.

---

# 8.13 Payment Recording

```text
Admin
↓
Pembayaran
↓
[Tambah Pembayaran]
↓
Select Student
↓
Select relevant Enrollment / Class
↓
Select Period
↓
Enter Amount
↓
Set Status
↓
[Save]
↓
Payment Recorded
```

Jika status membutuhkan verifikasi:

```text
Menunggu Verifikasi
↓
Admin verifies
↓
Lunas
```

---

# 8.14 Payment Monitoring

```text
Admin
↓
Pembayaran
↓
Select Period
↓
View
├── Belum Bayar
├── Menunggu Verifikasi
├── Lunas
└── Terlambat
↓
Select Payment
↓
View Detail
```

---

# 8.15 Parent Payment Reminder

```text
Admin
↓
Pembayaran / Reminder
↓
Select Student
↓
System reads
├── Student
├── Active Enrollment
├── Class
├── Payment
└── Due Date Configuration
↓
Generate Personalized Message
↓
Preview
↓
[Copy Message]
OR
[Open WhatsApp]
```

Jika siswa memiliki beberapa kelas:

```text
Student
├── Class A → Payment
├── Class B → Payment
└── Class C → Payment
        ↓
System calculates relevant total
        ↓
One personalized reminder
```

Tidak ada WhatsApp API pada MVP.

---

# 8.16 Honor Calculation

```text
Admin
↓
Honor Tutor
↓
Select Period
↓
System loads active Honor Scheme
↓
System determines source data
├── Teaching Sessions
├── Active Students
├── Recorded/Paid Revenue
└── Fixed Monthly
↓
[Generate]
↓
Honor Calculation = Draft
↓
Review
↓
Check Details
↓
[Finalize]
↓
Honor Calculation = Final
↓
[Mark Paid]
↓
Honor Calculation = Paid
```

Actual Tutor digunakan untuk sesi yang memiliki replacement.

---

# 8.17 Honor Calculation - Per Session

```text
Select Tutor
↓
Select Period
↓
Find completed Teaching Sessions
↓
Determine Actual Tutor
↓
Count eligible sessions
↓
Multiply by session rate
↓
Generate Draft
```

Example:

```text
18 sessions × Rp50.000
= Rp900.000
```

---

# 8.18 Honor Calculation - Per Student

```text
Select Tutor
↓
Select Period
↓
Find applicable students
↓
Determine active basis
↓
Multiply by student rate
↓
Generate Draft
```

Basis harus mengikuti rule yang ditetapkan dalam Business Rules.

---

# 8.19 Honor Calculation - Revenue Share

```text
Select Tutor
↓
Select Period
↓
Find applicable recorded/paid revenue
↓
Apply revenue share percentage
↓
Generate Draft
```

MVP menggunakan recorded/paid revenue sebagai basis.

---

# 8.20 Honor Calculation - Fixed Monthly

```text
Select Tutor
↓
Select Period
↓
Load active Fixed Monthly Scheme
↓
Generate fixed amount
↓
Create Draft
```

---

# 8.21 Operational Reports

```text
Admin
↓
Laporan
↓
Select Report
├── Student
├── Attendance
├── Class
├── Payment
├── Tutor
└── Honor
↓
Select Period / Branch
↓
Generate
↓
View Result
```

Admin hanya mendapatkan data sesuai branch scope.

---

# 9. TUTOR FLOWS

# 9.1 Tutor Dashboard

```text
Tutor Login
↓
Tutor Dashboard
↓
View Today's Schedule
↓
View
├── Upcoming Sessions
├── Classes
└── Pending Teaching Tasks
↓
Select Teaching Session
```

Dashboard menjawab:

> "Hari ini saya mengajar apa?"

---

# 9.2 View Teaching Schedule

```text
Tutor
↓
Jadwal Mengajar
↓
Select Date
↓
View Sessions
├── Time
├── Class
├── Branch
├── Room
└── Session Status
↓
Select Session
```

Tutor hanya melihat session yang berada dalam teaching scope.

---

# 9.3 View Class

```text
Tutor
↓
Kelas Saya
↓
Select Class
↓
Class Detail
├── Class Information
├── Students
├── Schedule
├── Sessions
└── Assessment
```

Tutor tidak mendapatkan operational management access.

---

# 9.4 Open Teaching Session

```text
Tutor
↓
Jadwal Mengajar
↓
Select Teaching Session
↓
Teaching Session Detail
↓
View
├── Class
├── Date
├── Time
├── Room
├── Students
└── Session Status
```

---

# 9.5 Student Attendance

```text
Tutor
↓
Teaching Session
↓
Absensi
↓
View Student List
↓
Set status per Student
├── Hadir
├── Izin
├── Sakit
└── Alpa
↓
[Save Attendance]
↓
System validates
├── Invalid
│   ↓
│   Show errors
│
└── Valid
    ↓
    Save Student Attendance
    ↓
    Record Tutor Attendance
    ↓
    Continue Session
```

Tutor attendance otomatis tercatat setelah student attendance berhasil disimpan.

---

# 9.6 Tutor Attendance Rule

```text
Tutor opens session
        ↓
No attendance yet
        ↓
Tutor saves student attendance
        ↓
Success
        ↓
Tutor marked present
```

Bukan:

```text
Open session
↓
Automatically present
```

---

# 9.7 Add Material

```text
Tutor
↓
Teaching Session
↓
Material
↓
Enter Material
↓
[Save]
↓
Material attached to session
```

---

# 9.8 Add Session Notes

```text
Tutor
↓
Teaching Session
↓
Notes
↓
Enter Notes
↓
[Save]
↓
Notes attached to session
```

---

# 9.9 Assessment

```text
Tutor
↓
Kelas Saya
↓
Select Class
↓
Assessment
↓
[Create Assessment]
↓
Enter
├── Name
├── Type
├── Date
├── Material
└── Max Score
↓
[Save]
↓
Assessment Created
↓
Enter Student Results
↓
Save Results
```

Assessment type:

```text
Tugas
Quiz
Ujian
```

---

# 9.10 Teaching History

```text
Tutor
↓
Riwayat Mengajar
↓
Select Period
↓
View completed sessions
↓
Select Session
↓
View
├── Class
├── Date
├── Attendance
├── Material
└── Notes
```

---

# 9.11 Tutor Replacement Visibility

Tutor tidak mengajukan formal replacement request pada MVP.

Jika Tutor digantikan:

```text
Admin updates Teaching Session
↓
Actual Tutor changes
↓
Tutor dashboard/schedule reflects assignment
```

Tutor dapat melihat session yang relevan dengan assignment aktual.

---

# 10. CROSS-ROLE FLOWS

# 10.1 Student-to-Teaching Flow

```text
Admin creates Student
↓
Admin creates Enrollment
↓
Admin creates/uses Class
↓
Admin assigns Tutor
↓
Admin creates Recurring Schedule
↓
System generates Teaching Session
↓
Tutor sees Session
↓
Tutor teaches
↓
Tutor records Attendance
↓
Teaching data becomes available
```

---

# 10.2 Schedule-to-Honor Flow

```text
Recurring Schedule
↓
Teaching Session
↓
Session completed
↓
Actual Tutor determined
↓
Honor Calculation
↓
Apply Honor Scheme
↓
Draft
↓
Final
↓
Paid
```

---

# 10.3 Enrollment-to-Payment-Reminder Flow

```text
Student
↓
Enrollment
↓
Payment Obligation
↓
Payment Status
├── Lunas
│   ↓
│   No outstanding reminder needed
│
└── Belum Bayar / Terlambat
    ↓
    Admin Generate Reminder
    ↓
    Cressco reads relevant enrollment/payment
    ↓
    Generate Message
    ↓
    Copy / Open WhatsApp
```

---

# 10.4 Replacement-to-Honor Flow

```text
Recurring Schedule
↓
Scheduled Tutor = Budi
↓
Teaching Session
↓
Budi unavailable
↓
Admin selects Sinta
↓
Actual Tutor = Sinta
↓
Sinta teaches
↓
Session completed
↓
Honor calculation uses Actual Tutor
```

Future sessions:

```text
Future Schedule
↓
Scheduled Tutor remains Budi
```

Replacement is session-specific unless the recurring schedule itself is intentionally changed.

---

# 10.5 Branch Context Flow

```text
User
↓
Select Branch Context
↓
System validates branch access
├── Allowed
│   ↓
│   Update context
│   ↓
│   Reload branch-scoped data
│
└── Not Allowed
    ↓
    Deny
```

For Owner:

```text
All Branches
```

is a valid context.

For Admin:

```text
Only assigned branches
```

are valid contexts.

For Tutor:

```text
Teaching scope
```

is derived from assignments.

---

# 11. Exception & Empty State Flows

## 11.1 No Teaching Session

```text
Tutor
↓
Jadwal Mengajar
↓
No session found
↓
Show Empty State
```

## 11.2 Attendance Not Yet Filled

```text
Teaching Session
↓
Attendance
↓
No attendance
↓
Show action:
[Isi Absensi]
```

## 11.3 Session Already Completed

```text
Teaching Session
↓
Status = Selesai
↓
View existing data
↓
Edit capability according to permission
```

## 11.4 No Payment Outstanding

```text
Payment
↓
Student
↓
No outstanding payment
↓
Show:
Tidak ada tagihan tertunggak
```

## 11.5 No Honor Calculation

```text
Honor
↓
Select Period
↓
No calculation
↓
Show:
Belum ada perhitungan honor
↓
[Generate]
```

## 11.6 No Branch Access

```text
User Login
↓
Resolve Branch Access
↓
No permitted branch
↓
Deny operational access
↓
Show appropriate account/access state
```

---

# 12. Role Boundary Flow

Cressco tidak menggunakan role switcher dalam UX.

User masuk ke area berdasarkan role.

```text
Login
↓
Role
├── Owner
│   ↓
│   Owner Area
│
├── Admin
│   ↓
│   Admin Area
│
├── Tutor
│   ↓
│   Tutor Area
│
└── Super Admin
    ↓
    Super Admin Area
```

Tidak ada:

```text
Dashboard
↓
[Switch Role]
```

Role adalah authorization boundary, bukan UI preference.

---

# 13. Super Admin Flows - Phase 2

Super Admin tetap bagian dari product definition, tetapi bukan core MVP bimbel.

# 13.1 Super Admin Dashboard

```text
Super Admin Login
↓
Super Admin Dashboard
↓
View Platform Overview
├── Active Tenants
├── Inactive Tenants
├── Users
├── Platform Activity
└── Subscription Overview
```

---

# 13.2 Create / Provision Tenant

Target architecture:

```text
Super Admin
↓
Bimbel
↓
[Create Tenant]
↓
Enter Tenant Information
↓
System validates unique tenant slug
↓
Create Tenant
↓
Provision Tenant Context
↓
Tenant becomes available
```

Self-service onboarding may be introduced later; guided onboarding remains valid for initial customer acquisition.

---

# 13.3 Manage Tenant

```text
Super Admin
↓
Bimbel
↓
Tenant List
↓
Select Tenant
↓
Tenant Detail
├── Overview
├── Users
├── Subscription
└── Activity
```

---

# 13.4 Access Tenant Dashboard / Support Mode

MVP platform flow:

```text
Super Admin
↓
Tenant Detail
↓
[Access Tenant Dashboard]
↓
Confirmation
↓
Enter Support Mode
↓
Tenant Dashboard
↓
Support Mode Banner
↓
Perform support/troubleshooting action if authorized
↓
[Exit Tenant]
↓
Return to Super Admin
```

Important:

- Super Admin tetap merupakan Super Admin.
- Role tidak berubah menjadi Owner/Admin/Tutor.
- Tenant identity tidak dimutasi.
- Support access dicatat.
- Actions performed during support mode dapat diaudit.
- MVP tidak menyediakan arbitrary "Login as Owner/Tutor".

---

# 13.5 Suspend Tenant

```text
Super Admin
↓
Tenant Detail
↓
[Deactivate / Suspend]
↓
Confirmation
↓
[Confirm]
↓
Tenant becomes inactive/suspended
↓
Tenant access blocked
```

---

# 13.6 Platform User Management

```text
Super Admin
↓
Users
↓
Search / Filter
↓
Select User
↓
View User Detail
├── Identity
├── Role
├── Tenant
└── Status
```

Super Admin platform access berbeda dari operational user management milik Owner/Admin.

---

# 13.7 Platform Audit

```text
Super Admin
↓
Tenant Detail
↓
Activity
↓
Filter
├── Actor
├── Action
├── Date
└── Tenant
↓
View Audit Entry
```

---

# 14. End-to-End Core Scenario

Scenario: sebuah kelas berjalan normal.

```text
OWNER
↓
Configure Tenant
↓
Create Branch
↓
Invite Admin
↓
Configure Honor
        ↓
ADMIN
↓
Create Student
↓
Create Enrollment
↓
Create Class
↓
Assign Tutor
↓
Create Recurring Schedule
        ↓
SYSTEM
↓
Generate Teaching Session
        ↓
TUTOR
↓
View Schedule
↓
Open Teaching Session
↓
Take Student Attendance
↓
System records Tutor Attendance
↓
Add Material
↓
Add Notes
↓
Complete Session
        ↓
ADMIN
↓
Monitor Attendance
↓
Record Payment
↓
Generate Reminder if needed
↓
Calculate Honor
        ↓
OWNER
↓
Monitor Business
↓
Review Payment
↓
Review Honor
↓
Review Reports
```

---

# 15. End-to-End Replacement Scenario

```text
Recurring Schedule
↓
Scheduled Tutor = Budi
↓
Teaching Session Created
↓
Budi informs Admin he cannot teach
↓
Admin opens Teaching Session
↓
Select replacement Sinta
↓
Enter replacement reason
↓
Save
↓
Scheduled Tutor = Budi
Actual Tutor = Sinta
↓
Sinta opens session
↓
Sinta records attendance
↓
Session completed
↓
Honor calculation attributes session to Sinta
↓
Future recurring sessions remain assigned to Budi
```

---

# 16. End-to-End Payment Scenario

```text
Owner
↓
Set Payment Due Date = 10
        ↓
Student enrolled in:
├── Mathematics
├── English
└── Physics
        ↓
Payment obligations created/recorded
        ↓
Payment status:
├── Mathematics = Lunas
├── English = Belum Bayar
└── Physics = Belum Bayar
        ↓
Admin opens Reminder
↓
Select Student
↓
System checks relevant unpaid obligations
↓
Generate combined message
↓
Admin reviews
↓
Copy / Open WhatsApp
```

The message should reflect actual relevant data, not hardcoded text.

---

# 17. End-to-End Honor Scenario

```text
Owner
↓
Default Scheme = Per Sesi
↓
Rate = Rp50.000
        ↓
Admin
↓
Recurring Schedule created
↓
Teaching Sessions generated
↓
Sessions completed
↓
Actual Tutor identified
↓
Admin selects period
↓
Generate Honor
↓
System loads active scheme
↓
Calculate
↓
Draft
↓
Admin reviews
↓
Finalize
↓
Paid
        ↓
OWNER
↓
Review Honor Summary
```

---

# 18. Navigation-to-Flow Relationship

## Owner

```text
Dashboard
├── Branch
├── Users
├── Siswa
├── Kelas
├── Jadwal
├── Tutor
├── Pembayaran
├── Honor Tutor
├── Laporan
├── Pengaturan Bimbel
├── Profil
└── Logout
```

## Admin

```text
Dashboard
├── Siswa
├── Kelas
├── Jadwal
├── Absensi
├── Tutor
├── Pembayaran
├── Honor Tutor
├── Reminder Orang Tua
├── Laporan
├── Profil
└── Logout
```

## Tutor

```text
Dashboard
├── Jadwal Mengajar
├── Kelas Saya
├── Absensi / Teaching Session
├── Penilaian
├── Riwayat Mengajar
├── Profil
└── Logout
```

## Super Admin - Phase 2

```text
Dashboard
├── Bimbel
├── Users
├── Settings
├── Profile
└── Logout
```

---

# 19. Flow Completion Criteria

Cressco core user flows dianggap siap untuk masuk ke Business Rules dan ERD apabila:

- Owner dapat menyelesaikan tenant setup.
- Owner dapat membuat branch.
- Owner dapat mengundang Admin.
- Admin dapat menjalankan setup operasional.
- Student dapat di-enroll ke Class.
- Tutor dapat di-assign ke Class.
- Recurring Schedule dapat menghasilkan Teaching Session.
- Tutor dapat menjalankan Teaching Session.
- Attendance dapat dicatat.
- Tutor attendance dapat tercatat berdasarkan attendance submission.
- Admin dapat melakukan operational tutor replacement.
- Scheduled Tutor dan Actual Tutor tetap dapat dibedakan.
- Payment dapat dicatat.
- Payment reminder dapat dihasilkan dari data aktual.
- Honor dapat dihitung berdasarkan scheme.
- Owner dapat melakukan oversight.
- Branch context tidak melanggar authorization.
- Tenant isolation berlaku pada seluruh workflow.
- Super Admin dapat didefinisikan sebagai platform flow tanpa menjadi dependency core MVP.

---

# 20. Flow-to-Data Dependencies

User flow menghasilkan kebutuhan data berikut:

```text
Authentication
→ User
→ Role
→ Tenant

Tenant Setup
→ Tenant
→ Branch
→ User
→ Branch Access
→ Tenant Settings

Student Setup
→ Student
→ Enrollment
→ Class

Tutor Setup
→ User/Tutor
→ Tutor Assignment

Schedule
→ Schedule
→ Teaching Session

Teaching
→ Teaching Session
→ Student Attendance
→ Tutor Attendance
→ Material
→ Session Notes

Assessment
→ Assessment
→ Assessment Result

Payment
→ Payment
→ Payment Status

Reminder
→ Enrollment
→ Payment
→ Tenant Payment Configuration

Honor
→ Honor Scheme
→ Honor Assignment
→ Honor Calculation

Replacement
→ Teaching Session
→ Scheduled Tutor
→ Actual Tutor
→ Replacement Record

Reporting
→ Operational entities above
```

Dokumen `ERD.md` harus menggunakan dependency ini sebagai salah satu dasar pembentukan data model.
