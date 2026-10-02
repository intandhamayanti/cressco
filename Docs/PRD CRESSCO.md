# Cressco MVP - Product Requirements Document

> **Status:** Product Definition / MVP
> **Product:** Cressco
> **Domain:** B2B SaaS untuk bimbel offline
> **Primary MVP Roles:** Owner, Admin, Tutor/Tentor
> **Platform Role:** Super Admin - Phase 2
> **Language:** Bahasa Indonesia
> **Architecture:** Multi-tenant SaaS

---

## 1. Product Overview

Cressco adalah B2B SaaS untuk membantu bimbingan belajar (bimbel) offline mengelola operasional akademik, siswa, tutor, kelas, jadwal, sesi mengajar, absensi, pembayaran, honor tutor, dan laporan dalam satu sistem.

Cressco dirancang untuk menggantikan proses operasional yang masih tersebar di spreadsheet, chat, catatan manual, dan proses administratif berulang.

### Product Positioning

Cressco bukan sekadar aplikasi pencatatan data.

> **Admin memasukkan atau mengatur data inti sekali, kemudian Cressco menggunakan data tersebut untuk mengotomatisasi pekerjaan berikutnya.**

Contoh:

```text
Student + Enrollment + Class + Schedule
+ Tutor Assignment + Payment Configuration
+ Honor Configuration
        ↓
Dashboard
Teaching Session
Attendance
Payment Reminder
Tutor Replacement
Honor Calculation
Reports
```

### Target Customer

Bimbel offline yang memiliki siswa, kelas, tutor, jadwal rutin, pembayaran siswa, honor tutor, dan kebutuhan koordinasi operasional. Cressco mendukung satu atau beberapa branch/cabang.

---

## 2. Problem Statement

Masalah operasional yang ingin diselesaikan:

1. Data siswa masih dikelola secara manual.
2. Satu siswa dapat mengikuti beberapa kelas sehingga enrollment sulit dilacak.
3. Jadwal rutin perlu dikelola secara manual.
4. Jadwal dan kejadian mengajar aktual tidak dibedakan.
5. Absensi siswa tidak terpusat.
6. Penggantian tutor tidak terdokumentasi dengan baik.
7. Pembayaran sulit dipantau secara konsisten.
8. Reminder pembayaran harus diketik ulang.
9. Honor tutor dihitung manual dan dapat berbeda antar tutor.
10. Owner sulit mendapatkan gambaran kondisi bisnis dengan cepat.
11. Data operasional tersebar antara Owner, Admin, Tutor, spreadsheet, dan komunikasi informal.

Cressco menjadi satu sumber data operasional yang saling terhubung.

---

## 3. Product Goals

Cressco MVP bertujuan untuk:

1. Memusatkan data operasional bimbel.
2. Mengurangi pekerjaan administratif yang berulang.
3. Menghubungkan siswa, enrollment, kelas, tutor, jadwal, dan sesi mengajar.
4. Menghasilkan Teaching Session dari recurring schedule.
5. Menjadikan Teaching Session sebagai sumber aktivitas mengajar aktual.
6. Mempermudah pencatatan absensi siswa.
7. Mencatat Tutor Aktual tanpa check-in/check-out manual.
8. Mempermudah pencatatan dan monitoring pembayaran.
9. Menghasilkan personalized payment reminder dari data aktual.
10. Mendukung kebijakan honor tutor yang configurable.
11. Memberikan Owner business overview dan financial oversight.
12. Memberikan Admin operational control.
13. Memberikan Tutor workflow mengajar yang sederhana.
14. Menjaga isolasi data antar tenant.

---

## 4. Product Principles

### 4.1 Owner = Control & Insight

Owner bertanggung jawab atas kontrol bisnis, branch, Admin, kebijakan honor, financial oversight, laporan, dan tenant settings.

Owner bukan operator utama aktivitas harian.

### 4.2 Admin = Daily Operations

Admin menjalankan operasional harian bimbel:

- siswa,
- enrollment,
- kelas,
- tutor,
- assignment,
- jadwal,
- teaching session,
- pembayaran,
- reminder,
- replacement,
- honor calculation,
- laporan operasional.

### 4.3 Tutor = Teaching

Tutor fokus pada:

- jadwal mengajar,
- kelas,
- sesi mengajar,
- absensi siswa,
- penilaian,
- material,
- notes,
- histori mengajar.

### 4.4 One Input, Reuse Everywhere

Data inti yang sudah dimasukkan harus digunakan kembali oleh modul lain.

```text
Enrollment → Payment Reminder → Reports

Schedule → Teaching Session → Attendance → Honor → Reports
```

### 4.5 Reduce Administrative Work

Tutor tidak seharusnya menginput data yang sama berkali-kali.

Flow utama:

```text
Buka Sesi
↓
Isi Absensi
↓
Materi
↓
Catatan
↓
Selesai
```

Data lain seperti kehadiran Tutor, histori mengajar, dan dasar honor dapat dihasilkan dari aktivitas tersebut.

---

## 5. Product Scope

### 5.1 In Scope - MVP

**Foundation**
- Authentication
- Authorization
- Multi-tenant
- Tenant isolation
- Branch
- Role system
- Branch access
- Tenant context
- Responsive application shell
- Shared design system/component foundation

**Owner**
- Owner Dashboard
- Branch Management
- Admin Management
- User Overview
- Student Overview
- Class Overview
- Tutor Overview
- Payment Overview
- Honor Scheme Configuration
- Honor Oversight
- Reports
- Tenant Settings
- Payment Due Date Configuration
- Branch Context

**Admin**
- Admin Dashboard
- Branch Context
- Student Management
- Enrollment
- Class Management
- Tutor Management
- Tutor Assignment
- Recurring Schedule
- Teaching Session
- Student Attendance Oversight
- Tutor Replacement
- Payment Management
- Payment Reminder Generator
- Honor Calculation
- Honor History
- Operational Reports

**Tutor**
- Tutor Dashboard
- Jadwal Mengajar
- Kelas Saya
- Teaching Session
- Student Attendance
- Automatic Tutor Attendance
- Assessment/Tugas
- Riwayat Mengajar
- Material
- Session Notes
- Tutor Profile

### 5.2 Platform Scope - Phase 2

**Super Admin**
- Platform Dashboard
- Tenant/Bimbel Management
- User Management
- Support Access
- Platform Settings
- Platform Audit
- Subscription Management

Super Admin tidak menjadi dependency untuk core bimbel MVP.

---

## 6. User & Role Model

### 6.1 Owner

**Purpose:** Control, insight, policy, financial oversight, tenant management.

Owner:
- authority tertinggi dalam tenant,
- akses seluruh branch,
- mengelola Admin,
- mengatur kebijakan honor,
- melihat kondisi bisnis,
- melihat financial overview,
- mengelola tenant settings.

Owner bukan operator utama pekerjaan harian.

### 6.2 Admin

**Purpose:** Daily operations.

Admin:
- memiliki akses satu atau beberapa branch,
- mengelola siswa,
- enrollment,
- kelas,
- tutor,
- tutor assignment,
- recurring schedule,
- teaching session,
- attendance oversight,
- payment,
- reminder,
- replacement,
- honor calculation,
- operational reports.

### 6.3 Tutor/Tentor

**Purpose:** Teaching.

Tutor:
- melihat jadwal,
- melihat kelas,
- melihat siswa dalam kelas,
- menjalankan teaching session,
- mengisi absensi,
- mencatat penilaian,
- mencatat material,
- mencatat notes,
- melihat histori mengajar.

Tutor tidak mengakses payment, keuangan, honor, user management, tenant settings, platform settings, atau tenant lain.

### 6.4 Super Admin

Super Admin adalah role platform Cressco.

Untuk MVP bimbel, Super Admin adalah **Phase 2** dan tidak diperlukan untuk menjalankan workflow inti tenant.

---

## 7. Permission Model

| Area | Owner | Admin | Tutor |
|---|---|---|---|
| Dashboard | Business | Operational | Teaching |
| Branch | Manage | Access sesuai scope | Derived |
| Admin | Manage | No | No |
| Tutor | View/Oversight | Manage sesuai scope | Own context |
| Student | View/Oversight | Manage | Class scope |
| Enrollment | View | Manage | View relevant |
| Class | View/Oversight | Manage | View assigned |
| Tutor Assignment | View | Manage | View relevant |
| Schedule | View/Oversight | Manage | View assigned |
| Teaching Session | View | Manage | Own/assigned |
| Attendance | View | View + Correction | Input |
| Assessment | View | View | Manage own teaching context |
| Payment | Financial oversight | Manage | No |
| Parent Reminder | View | Manage | No |
| Tutor Replacement | Oversight | Manage | Operational need |
| Honor Scheme | Manage | Operate | No |
| Honor Calculation | Oversight | Manage | No |
| Reports | Full tenant | Operational scope | Teaching scope |
| Tenant Settings | Manage | No | No |
| Platform Settings | No | No | No |
| Tenant lain | No | No | No |

Authorization harus diterapkan di backend. Menyembunyikan menu saja bukan security boundary.

---

## 8. Multi-Tenant & Branch Model

### 8.1 Tenant

```text
Cressco
├── Tenant A
│   ├── Branch
│   ├── Users
│   ├── Students
│   ├── Classes
│   └── Operational Data
└── Tenant B
    ├── Branch
    ├── Users
    ├── Students
    ├── Classes
    └── Operational Data
```

Data operasional harus terikat pada tenant. User Tenant A tidak boleh mengakses data Tenant B.

### 8.2 Tenant Subdomain

Target:

```text
{tenant_slug}.cressco.app
```

Contoh:

```text
bimbelceria.cressco.app
smartcourse.cressco.app
```

Laravel membaca hostname, menentukan tenant context, lalu seluruh request berjalan dalam konteks tenant tersebut.

### 8.3 Onboarding

MVP menggunakan guided/manual-assisted onboarding.

Arsitektur tetap disiapkan agar provisioning tenant dapat diotomatisasi di masa depan.

### 8.4 Branch

Branch adalah scope operasional, bukan role.

Owner:
- akses semua branch,
- dapat menggunakan branch context.

Admin:
- dapat memiliki akses satu atau beberapa branch,
- tidak dapat mengakses branch di luar scope.

Tutor:
- scope diturunkan dari `Tutor → Class Assignment → Class → Branch`.

Satu Tutor dapat mengajar di beberapa branch tanpa memperoleh akses administratif ke seluruh data branch.

---

## 9. Core Domain Model

Entity utama:

```text
Tenant
Branch
User
Student
Enrollment
Class
Tutor Assignment
Schedule
Teaching Session
Student Attendance
Assessment
Assessment Result
Payment
Honor Scheme
Honor Assignment
Honor Calculation
Tutor Replacement
```

Relasi inti:

```text
Student
  ↓
Enrollment
  ↓
Class
```

```text
Class
  ↕
Tutor Assignment
  ↕
Tutor
```

```text
Class
  ↓
Recurring Schedule
  ↓
Teaching Session
```

```text
Teaching Session
  ├── Student Attendance
  ├── Tutor Actual
  ├── Material
  └── Session Notes
```

```text
Class
  ↓
Assessment
  ↓
Assessment Result
  ↓
Student
```

```text
Tutor
  ↓
Honor Assignment
  ↓
Honor Scheme
  ↓
Honor Calculation
```

---

## 10. Core Product Modules

### 10.1 Dashboard

**Owner:** business overview, students, classes, tutors, revenue, outstanding, honor, sessions, attendance, growth, branch overview.

**Admin:** operational overview, today's sessions, attendance, payment alerts, overdue, quick actions.

**Tutor:** today's schedule, classes, students, completed sessions, quick actions.

### 10.2 Student Management

Admin adalah operator utama.

Data dapat mencakup:
- nama,
- tanggal lahir,
- jenis kelamin,
- WhatsApp siswa,
- alamat,
- orang tua/wali,
- WhatsApp orang tua/wali,
- catatan,
- branch,
- tanggal bergabung,
- status.

Status:
```text
Aktif
Nonaktif
```

### 10.3 Enrollment

Satu siswa dapat mengikuti banyak kelas.

```text
Student
↓
Enrollment
↓
Class
```

Data:
- student,
- class,
- branch,
- tanggal mulai,
- tanggal selesai,
- status.

Status:
```text
Aktif
Selesai
Dikeluarkan
```

### 10.4 Class Management

Admin mengelola:
- nama kelas,
- mata pelajaran,
- branch,
- level,
- kapasitas,
- status.

Status:
```text
Aktif
Nonaktif
```

Satu class dapat memiliki banyak Tutor.

### 10.5 Tutor Assignment

```text
Class
↕
Tutor Assignment
↕
Tutor
```

Assignment menentukan Tutor yang secara normal dapat menjalankan sesi.

### 10.6 Recurring Schedule

Schedule adalah aturan berulang.

Contoh:

```text
Matematika 9A
Setiap Senin
19.00 - 20.30
Tutor: Budi
```

Data:
- class,
- branch,
- hari,
- jam,
- scheduled tutor,
- ruangan,
- tanggal mulai,
- tanggal berakhir,
- status.

### 10.7 Teaching Session

```text
Class
= kelompok pembelajaran

Schedule
= aturan berulang

Teaching Session
= kejadian mengajar pada tanggal tertentu
```

Teaching Session menyimpan:
- tanggal,
- class,
- branch,
- scheduled tutor,
- actual tutor,
- waktu,
- ruangan,
- status,
- material,
- notes,
- attendance.

Status:
```text
Terjadwal
Selesai
Dibatalkan
```

Perubahan satu kali dilakukan pada Teaching Session, bukan recurring Schedule.

---

## 11. Tutor Replacement

MVP menggunakan operational replacement, bukan formal approval workflow.

Flow:

```text
Tutor berhalangan
↓
Komunikasi dengan Admin
↓
Admin memilih Tutor pengganti
↓
Admin mengubah Actual Tutor
↓
Tutor pengganti mengajar
```

Teaching Session membedakan:

```text
Scheduled Tutor
Actual Tutor
```

Normal:
```text
Scheduled Tutor = Budi
Actual Tutor = Budi
```

Replacement:
```text
Scheduled Tutor = Budi
Actual Tutor = Sinta
```

Alasan dapat mencakup:
- Tutor berhalangan,
- bentrok jadwal,
- kondisi darurat,
- lainnya.

Dicatat:
- siapa yang mengubah,
- Tutor sebelumnya,
- Tutor aktual,
- waktu,
- alasan.

Owner melihat histori replacement, tetapi tidak perlu menyetujui setiap replacement.

---

## 12. Attendance

### Student Attendance

Tutor mengisi:
```text
Hadir
Izin
Sakit
Alpa
```

Admin dapat melihat dan mengoreksi sesuai permission.

### Tutor Attendance

Tidak ada check-in/check-out manual.

```text
Tutor membuka Teaching Session
↓
Mengisi attendance siswa
↓
Save berhasil
↓
Tutor otomatis tercatat hadir
```

Membuka halaman attendance saja tidak dianggap hadir.

MVP tidak menggunakan:
- GPS,
- geolocation,
- selfie,
- foto sebagai bukti kehadiran.

---

## 13. Assessment, Material & Notes

Tutor dapat membuat:
- Tugas,
- Quiz,
- Ujian.

Relasi:

```text
Class
↓
Assessment
↓
Assessment Result
↓
Student
```

Satu siswa dapat memiliki banyak hasil assessment.

Tutor juga dapat mencatat material dan notes pada Teaching Session.

Data ini dapat digunakan untuk histori pembelajaran, monitoring siswa, dan laporan perkembangan.

---

## 14. Payment System

Admin adalah operator utama.

MVP menggunakan payment recording manual. Payment gateway bukan dependency MVP.

Data:
- student,
- class/enrollment,
- periode,
- nominal,
- due date,
- payment date,
- status.

Status:
```text
Belum Bayar
Menunggu Verifikasi
Lunas
Terlambat
```

Owner memiliki financial oversight. Tutor tidak memiliki akses payment.

### Payment Due Date

Tenant memiliki konfigurasi, misalnya tanggal 5 atau 10 setiap bulan.

Owner menentukan policy. Admin menggunakan policy tersebut.

### Parent Payment Reminder

Cressco menghasilkan personalized message dari:
- nama siswa,
- kelas,
- jumlah kelas,
- periode,
- nominal,
- total,
- due date,
- status.

Flow:

```text
Payment Due
↓
Admin memilih siswa
↓
Cressco membaca Enrollment + Payment
↓
Generate personalized message
↓
Copy Message / Open WhatsApp
```

MVP tidak menggunakan WhatsApp API.

---

## 15. Honor Tutor

MVP mendukung:

1. Per Sesi
2. Per Siswa
3. Revenue Share
4. Fixed Monthly

### Default Honor Scheme

Tenant memiliki default.

```text
Default:
Per Sesi
Rp50.000 / sesi
```

### Tutor Override

Tutor tertentu dapat memiliki override.

```text
Default:
Per Sesi Rp50.000

Sinta:
Fixed Monthly Rp3.000.000
```

Hybrid terjadi melalui Default + Override.

### Effective Date

Perubahan honor memiliki effective date dan tidak boleh merusak histori.

### Honor Calculation

Admin:

```text
Generate
↓
Review
↓
Finalize
↓
Paid
```

Cressco membaca:
- Actual Tutor,
- Teaching Session,
- Enrollment,
- Honor Scheme,
- Effective Date,
- payment data jika diperlukan.

**Per Sesi:**
```text
18 completed sessions × Rp50.000 = Rp900.000
```

**Per Siswa:**
basis harus eksplisit, misalnya jumlah siswa aktif pada kelas yang diampu dalam periode.

**Revenue Share:**
basis MVP adalah pembayaran tercatat/lunas, bukan sekadar nominal tagihan.

**Fixed Monthly:**
menggunakan monthly rate.

Status:
```text
Draft
Final
Paid
```

Manual adjustment wajib memiliki alasan dan histori.

---

## 16. Reports

### Owner
- Business
- Student
- Attendance
- Tutor
- Financial
- Branch comparison

### Admin
- Student
- Attendance
- Class
- Payment
- Tutor
- Honor
- Operational sessions

### Tutor
Hanya informasi yang relevan dengan teaching scope.

---

## 17. Tenant Settings

Owner mengelola:
- nama bimbel,
- logo,
- deskripsi,
- alamat,
- nomor WhatsApp,
- email,
- informasi bisnis,
- payment due date,
- honor configuration,
- operational settings yang tersedia.

Tenant settings berbeda dari platform settings.

---

## 18. Authentication & Authorization

### Authentication

Menjawab:
> Siapa user?

MVP menggunakan Laravel Breeze.

### Authorization

Menjawab:
> Apa yang boleh dilakukan user?

Ditentukan oleh:
- role,
- tenant,
- branch access,
- resource scope,
- assignment.

Role tidak ditentukan berdasarkan email.

Authorization wajib ditegakkan pada backend. User tidak boleh memperoleh data terlarang melalui URL, ID resource, request, atau parameter tenant/branch.

---

## 19. Non-Functional Requirements

### Responsive
- Mobile
- Tablet
- Desktop

### Security
- Tenant isolation
- Backend authorization
- Secure authentication
- Audit untuk perubahan penting
- Role tidak dapat diubah sembarangan
- Admin tidak dapat menambah branch access dirinya sendiri
- Tutor tidak dapat mengakses data di luar teaching scope

### Maintainability
- Shared reusable components
- Shared design system
- Role-specific feature modules
- Tidak menduplikasi UI yang sama

### Data Consistency

```text
Enrollment → Payment Reminder
Schedule → Teaching Session
Teaching Session → Attendance
Actual Tutor → Honor
```

---

## 20. Technical Architecture Assumptions

MVP menggunakan:

```text
Laravel
├── Laravel Breeze
├── Livewire
├── Flux UI
├── Blade
└── MySQL
```

Arsitektur:

```text
One Laravel Application
One Database
One Authentication System
One Design System
Multi-Tenant
```

Role-specific areas tetap modular:

```text
Owner
Admin
Tutor
```

Shared UI components digunakan lintas role.

---

## 21. Design System Requirements

Figma menjadi visual source of truth.

Design system minimal mencakup:
- typography,
- color,
- spacing,
- radius,
- shadows,
- buttons,
- inputs,
- select,
- checkbox,
- radio,
- tables,
- cards,
- badges,
- modal,
- dropdown,
- tabs,
- navigation,
- sidebar,
- pagination,
- alerts,
- empty states,
- loading states,
- error states.

Flux UI dapat digunakan sebagai base primitive yang disesuaikan dengan visual Cressco.

Komponen tidak dibuat ulang per role apabila fungsi dan behavior-nya sama.

---

## 22. Core User Flows

### 22.1 Initial Tenant Setup

```text
Owner
↓
Tenant Settings
↓
Bimbel Information
↓
Branch Setup
↓
Honor Configuration
↓
Payment Configuration
↓
Invite Admin
↓
Tenant Ready
```

### 22.2 Operational Setup

```text
Admin
↓
Tambah Student
↓
Enrollment
↓
Create Class
↓
Assign Tutor
↓
Create Recurring Schedule
↓
Teaching Session Generated
```

### 22.3 Teaching

```text
Tutor
↓
Dashboard
↓
Jadwal Hari Ini
↓
Teaching Session
↓
Isi Attendance
↓
Save
↓
Tutor otomatis tercatat hadir
↓
Material
↓
Notes
↓
Session selesai
```

### 22.4 Tutor Replacement

```text
Tutor berhalangan
↓
Komunikasi dengan Admin
↓
Admin menentukan replacement
↓
Actual Tutor berubah
↓
Tutor pengganti mengajar
↓
Attendance
↓
Honor menggunakan Actual Tutor
```

### 22.5 Payment

```text
Payment Data
↓
Outstanding
↓
Admin Generate Reminder
↓
Personalized Message
↓
Copy / Open WhatsApp
```

### 22.6 Honor

```text
Honor Scheme
↓
Teaching Session / Enrollment / Payment
↓
Generate
↓
Review
↓
Finalize
↓
Paid
```

---

## 23. Core Business Rules

1. Satu aplikasi dapat memiliki banyak tenant.
2. Data tenant harus terisolasi.
3. Branch bukan role.
4. Owner memiliki akses seluruh branch tenant.
5. Admin hanya dapat mengakses branch yang diberikan.
6. Satu user dapat memiliki akses lebih dari satu branch.
7. Tutor mendapatkan scope melalui class assignment.
8. Satu siswa dapat mengikuti banyak kelas.
9. Satu Tutor dapat mengajar banyak kelas.
10. Satu kelas dapat memiliki banyak Tutor.
11. Class, Schedule, dan Teaching Session adalah entity berbeda.
12. Schedule bersifat recurring.
13. Teaching Session adalah kejadian mengajar pada tanggal tertentu.
14. Perubahan satu kali dilakukan pada Teaching Session, bukan recurring Schedule.
15. Scheduled Tutor dan Actual Tutor harus dibedakan.
16. Replacement dicatat pada Teaching Session.
17. MVP tidak menggunakan formal Owner approval untuk operational tutor replacement.
18. Admin menentukan Tutor pengganti.
19. Actual Tutor adalah dasar histori Tutor yang benar-benar mengajar.
20. Tutor mengisi student attendance.
21. Tutor otomatis tercatat hadir setelah student attendance berhasil disimpan.
22. Membuka halaman attendance saja tidak dianggap hadir.
23. MVP tidak menggunakan GPS/selfie sebagai attendance mechanism.
24. Payment reminder menggunakan data aktual Student + Enrollment + Payment.
25. Payment deadline berasal dari tenant configuration.
26. MVP menggunakan message generation, bukan WhatsApp API.
27. Honor scheme configurable per tenant.
28. Tenant dapat memiliki Default Honor Scheme.
29. Tutor tertentu dapat memiliki Override.
30. Admin tidak memilih metode honor setiap kali melakukan calculation.
31. Perubahan honor menggunakan effective date.
32. Histori honor tidak boleh berubah karena konfigurasi masa depan.
33. Honor per sesi menggunakan Teaching Session yang selesai.
34. Replacement menggunakan Actual Tutor untuk honor.
35. Revenue share MVP menggunakan recorded/paid revenue sebagai basis.
36. Honor calculation harus dapat ditelusuri ke data sumber.
37. Owner mengelola kebijakan honor.
38. Admin menjalankan operational honor calculation.
39. Owner mengelola Admin.
40. Admin mengelola Tutor secara operasional.
41. Owner tidak perlu menjadi operator penambahan Tutor.
42. Tutor tidak mengelola user, class, schedule, payment, atau honor.
43. Admin tidak dapat mengubah role atau branch access dirinya sendiri.
44. User tidak boleh mendapatkan data terlarang hanya dengan memanipulasi URL/resource ID.
45. Perubahan penting pada data operasional dan finansial harus dapat diaudit.

---

## 24. MVP Priority

Prioritas MVP ditentukan dari sisi **core bimbel**, bukan Super Admin.

### P0 - Foundation
- Authentication
- Tenant
- Tenant isolation
- Branch
- Role
- Authorization
- Branch access
- Shared application shell

### P0 - Owner
- Owner Dashboard
- Branch Management
- Admin Management
- User Overview
- Student Overview
- Class Overview
- Tutor Overview
- Payment Overview
- Honor Scheme Configuration
- Honor Oversight
- Reports
- Tenant Settings
- Payment Due Date
- Branch Context

### P0 - Admin
- Admin Dashboard
- Branch Context
- Student Management
- Enrollment
- Class Management
- Tutor Management
- Tutor Assignment
- Recurring Schedule
- Teaching Session
- Attendance Oversight
- Tutor Replacement
- Payment Management
- Payment Reminder Generator
- Honor Calculation
- Honor History
- Operational Reports

### P0 - Tutor
- Tutor Dashboard
- Jadwal Mengajar
- Kelas Saya
- Teaching Session
- Student Attendance
- Automatic Tutor Attendance
- Assessment/Tugas

### P1

**Owner**
- Owner Profile
- Branch Comparison
- Advanced Financial Analytics

**Admin**
- Admin Profile
- Advanced Honor
- Bonus/Deduction
- Advanced operational reporting

**Tutor**
- Tutor Profile
- Riwayat Mengajar
- Material
- Session Notes
- Student Progress Report

### P2

- Super Admin
- Notifications
- WhatsApp API
- Payment Gateway
- Advanced Honor Formula Builder
- Advanced Approval Workflow
- AI Quiz
- Advanced subscription/billing
- Advanced branch-specific configuration

---

## 25. Out of Scope

MVP tidak mencakup:

- Free trial system.
- Complex subscription billing.
- Payment gateway.
- WhatsApp API.
- Parent portal.
- Parent chat.
- Complex LMS.
- Complex homework management.
- Tutor payroll management oleh Tutor.
- Manual Tutor check-in/check-out.
- Selfie attendance.
- GPS attendance.
- Geolocation attendance.
- Advanced notification center.
- AI-generated student report.
- AI Quiz production.
- Custom honor formula builder.
- Cross-tenant business analytics.
- Formal Owner approval untuk setiap tutor replacement.
- Specific-user impersonation untuk Super Admin.

---

## 26. MVP Success Criteria

### Tenant & Access
- Tenant dapat dibuat.
- User dapat login.
- Role menentukan authorization.
- Data antar tenant terisolasi.
- Owner dapat mengakses seluruh branch.
- Admin hanya dapat mengakses branch yang diizinkan.

### Operational Setup
- Owner dapat membuat branch.
- Owner dapat mengundang Admin.
- Admin dapat membuat Student.
- Admin dapat membuat Enrollment.
- Admin dapat membuat Class.
- Admin dapat menambahkan/menugaskan Tutor.
- Admin dapat membuat recurring Schedule.

### Teaching
- Sistem menghasilkan Teaching Session.
- Tutor dapat melihat sesi miliknya.
- Tutor dapat membuka sesi.
- Tutor dapat mengisi attendance.
- Attendance dapat disimpan.
- Tutor otomatis tercatat hadir.
- Tutor dapat mengisi assessment dasar.

### Replacement
- Admin dapat mengganti Actual Tutor untuk sesi tertentu.
- Scheduled Tutor tetap tersimpan.
- Replacement tidak mengubah recurring schedule.
- Actual Tutor menjadi dasar histori dan honor.

### Payment
- Admin dapat mencatat payment.
- Sistem dapat menentukan outstanding/overdue berdasarkan data.
- Admin dapat menghasilkan personalized payment reminder.
- Message dapat disalin atau dibuka melalui WhatsApp secara manual.

### Honor
- Owner dapat menentukan Default Honor Scheme.
- Tutor dapat memiliki override.
- Admin dapat generate honor.
- Honor dapat direview dan difinalisasi.
- Actual Tutor digunakan untuk perhitungan sesi.
- Histori honor konsisten terhadap effective date.

### Insight
- Owner dapat melihat kondisi tenant.
- Owner dapat melihat financial overview.
- Admin dapat melihat operational overview.
- Reports menggunakan data operasional yang sama sebagai source of truth.

---

## 27. Recommended Development Order

```text
1. Product Specification
↓
2. User Flow
↓
3. Business Rules
↓
4. ERD / Data Model
↓
5. Database Schema
↓
6. Laravel Foundation
↓
7. Authentication
↓
8. Tenant + Branch + Authorization
↓
9. Design System
↓
10. Shared Components
↓
11. Owner Dashboard
↓
12. Admin Dashboard
↓
13. Student + Enrollment
↓
14. Class + Tutor Assignment
↓
15. Recurring Schedule
↓
16. Teaching Session
↓
17. Tutor Attendance
↓
18. Payment
↓
19. Payment Reminder
↓
20. Tutor Replacement
↓
21. Honor
↓
22. Assessment
↓
23. Reports
↓
24. Testing & Refinement
↓
25. Super Admin / Platform Layer
↓
26. Landing Page
```

Feature implementation menggunakan vertical slice:

```text
Database
↓
Model
↓
Business Logic / Service
↓
Validation
↓
Authorization
↓
Livewire / UI
↓
Test
```

---

## 28. Documentation Architecture

```text
docs/
├── PRD.md
├── roles/
│   ├── OWNER.md
│   ├── ADMIN.md
│   ├── TUTOR.md
│   └── SUPER-ADMIN.md
├── product/
│   ├── USER-FLOWS.md
│   └── BUSINESS-RULES.md
├── architecture/
│   ├── ERD.md
│   ├── DATABASE-SCHEMA.sql
│   └── AUTHORIZATION.md
└── design/
    └── DESIGN-SYSTEM.md
```

Source of truth:

- `PRD.md` = product requirements tingkat tinggi.
- `roles/*.md` = role-specific behavior dan permissions.
- `USER-FLOWS.md` = detailed user journeys.
- `BUSINESS-RULES.md` = detailed business rules.
- `ERD.md` = conceptual data model.
- `DATABASE-SCHEMA.sql` = database blueprint.
- `DESIGN-SYSTEM.md` = design system rules.
- Laravel migrations = actual database implementation.
- Figma = visual source of truth.

---

## 29. Product Boundary: Super Admin

Super Admin tetap merupakan bagian dari Cressco sebagai platform SaaS, tetapi tidak menjadi bagian dari core validation loop MVP bimbel.

```text
CORE BIMBEL
│
├── Owner
├── Admin
└── Tutor
        ↓
Core operational value
        ↓
PLATFORM
│
└── Super Admin
```

Super Admin Phase 2 dapat menyediakan:
- tenant management,
- user oversight,
- support mode,
- platform settings,
- platform audit,
- subscription.

Core bimbel harus dapat berjalan tanpa Super Admin melakukan pekerjaan operasional setiap hari.

---

## 30. Final Product Definition

> **Cressco adalah B2B SaaS multi-tenant untuk membantu bimbel offline mengelola operasional siswa, kelas, tutor, jadwal, sesi mengajar, absensi, pembayaran, honor, dan laporan dalam satu sistem.**

Pembagian tanggung jawab:

```text
OWNER
Control
Insight
Policy
Financial Oversight
Tenant Management

        ↓

ADMIN
Daily Operations
Data Management
Scheduling
Payment Operations
Honor Operations

        ↓

TUTOR
Teaching
Attendance
Assessment
Material
Session Notes
```

Prinsip utama:

> **Owner membuat kebijakan dan mengawasi hasil. Admin menjalankan operasional. Tutor menjalankan aktivitas pengajaran. Cressco menghubungkan data tersebut sehingga satu aktivitas dapat menghasilkan data operasional yang dapat digunakan kembali oleh modul lain.**
