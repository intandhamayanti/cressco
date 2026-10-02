# Cressco - Business Rules

> **Document:** BUSINESS-RULES.md
> **Product:** Cressco
> **Scope:** Core Bimbel MVP + platform boundary
> **Primary Roles:** Owner, Admin, Tutor/Tentor
> **Platform Role:** Super Admin - Phase 2
> **Database Target:** MySQL
> **Purpose:** Mengunci aturan bisnis sebelum DATABASE-SCHEMA.sql dan Laravel migrations

---

# 1. Purpose

Dokumen ini mendefinisikan aturan bisnis yang menentukan bagaimana Cressco harus berperilaku.

Business Rules menjadi penghubung antara:

```text
PRD
↓
USER-FLOWS
↓
BUSINESS-RULES
↓
ERD
↓
DATABASE-SCHEMA
↓
Laravel Implementation
```

Business Rules berbeda dari Permission.

- **Business Rule** = aturan bagaimana sistem dan data harus bekerja.
- **Permission** = siapa yang boleh melakukan sesuatu.

Contoh:

```text
Business Rule:
Scheduled Tutor dan Actual Tutor harus berbeda secara konsep.

Permission:
Admin boleh mengubah Actual Tutor.
```

---

# 2. Core Product Rules

## BR-001 — Tenant Isolation

Setiap tenant adalah logical data boundary.

User dari Tenant A tidak boleh membaca atau mengubah data Tenant B.

Tenant isolation harus diterapkan pada backend, bukan hanya navigation/UI.

---

## BR-002 — Tenant Context

Setiap request operational harus memiliki tenant context yang valid.

Target hostname:

```text
{tenant_slug}.cressco.app
```

Tenant context digunakan sebagai boundary query dan authorization.

---

## BR-003 — Branch Is Not a Role

Branch adalah organizational/operational scope.

Role tetap:

```text
owner
admin
tutor
super_admin
```

Tidak boleh membuat role berdasarkan branch.

---

## BR-004 — Owner Has Full Tenant Branch Scope

Owner memiliki akses seluruh branch dalam tenant.

Branch selector hanya mengubah context data yang sedang dilihat.

Branch selector tidak mengubah authority Owner.

---

## BR-005 — Admin Has Explicit Branch Scope

Admin dapat memiliki akses ke satu atau beberapa branch.

Admin tidak boleh mengakses branch yang tidak diberikan kepadanya.

---

## BR-006 — Tutor Scope Is Assignment-Based

Tutor tidak mendapatkan operational access hanya karena mengajar di sebuah branch.

Scope Tutor berasal dari:

```text
Tutor
↓
Tutor Assignment
↓
Class
↓
Branch
```

Tutor hanya dapat mengakses teaching data yang relevan dengan assignment-nya.

---

## BR-007 — One Human, One Account

Satu orang menggunakan satu user account/email.

Jika Admin memiliki akses beberapa branch:

```text
1 User
+
Multiple Branch Access
```

bukan:

```text
1 Login per Branch
```

---

# 3. User & Role Rules

## BR-008 — Role Stored in Database

Role tidak boleh ditentukan berdasarkan email.

Contoh:

```text
owner@bimbelceria.com
```

tidak otomatis berarti Owner.

Role berasal dari authorization data.

---

## BR-009 — Owner Creates Admin

Owner adalah pihak yang mengundang/membuat Admin.

Owner dapat menentukan branch access Admin.

---

## BR-010 — Admin Manages Tutor Operationally

Admin adalah operator utama penambahan/invitation Tutor.

Owner memiliki oversight terhadap Tutor, tetapi tidak harus menjadi operator harian penambahan Tutor.

---

## BR-011 — Tutor Cannot Manage Users

Tutor tidak dapat:

- membuat user,
- mengubah role,
- mengubah branch access,
- menonaktifkan user,
- mengelola tenant users.

---

## BR-012 — Admin Cannot Change Own Authority

Admin tidak dapat:

- mengubah role dirinya sendiri,
- memberikan role lebih tinggi kepada dirinya,
- menambahkan branch access untuk dirinya sendiri.

---

# 4. Branch Rules

## BR-013 — Branch Belongs to One Tenant

Satu Branch hanya dimiliki satu Tenant.

```text
Branch.tenant_id → Tenant.id
```

Branch tidak boleh berpindah tenant secara bebas.

---

## BR-014 — Operational Entity Has Tenant Context

Operational entities harus dapat ditelusuri ke Tenant.

Minimal:

```text
Student
Class
Enrollment
Tutor Assignment
Schedule
Teaching Session
Attendance
Payment
Honor Calculation
Tutor Replacement
```

memiliki tenant scope.

---

## BR-015 — Branch-Scoped Operational Data

Entity yang menjalankan aktivitas di lokasi tertentu menggunakan branch scope:

```text
Student
Class
Enrollment
Tutor Assignment
Schedule
Teaching Session
Attendance
Payment
Tutor Replacement
```

---

## BR-016 — Owner All Branch Context

Owner dapat:

```text
Semua Cabang
```

atau branch tertentu sebagai context.

Selecting a branch tidak mengubah role/permission.

---

# 5. Student Rules

## BR-017 — Student Belongs to Tenant

Student hanya dapat berada pada satu tenant.

---

## BR-018 — Student Has Operational Branch

MVP mengasumsikan Student memiliki branch operational.

```text
Student → Branch
```

Jika student mengikuti kelas lintas branch, rule ini harus dikembangkan sebelum fitur tersebut didukung.

---

## BR-019 — Student Can Have Multiple Enrollments

Satu Student dapat mengikuti banyak Class.

```text
Student
├── Enrollment → Class A
├── Enrollment → Class B
└── Enrollment → Class C
```

---

## BR-020 — Enrollment Is the Student-Class Relationship

Jangan menyimpan `class_id` langsung sebagai satu-satunya relationship pada Student.

Gunakan:

```text
Student
↓
Enrollment
↓
Class
```

---

## BR-021 — Enrollment Has Lifecycle

Enrollment minimal memiliki:

```text
active
completed
withdrawn
```

Enrollment historical tidak boleh dihapus hanya karena student sudah tidak aktif.

---

## BR-022 — Enrollment Belongs to Same Tenant

Student dan Class dalam satu Enrollment harus berasal dari tenant yang sama.

---

## BR-023 — Enrollment Branch Consistency

Untuk MVP:

```text
Enrollment.branch_id
=
Class.branch_id
```

dan Student harus berada pada branch yang sesuai.

Jika cross-branch enrollment diperlukan di masa depan, rule ini harus dievolusikan secara eksplisit.

---

# 6. Class Rules

## BR-024 — Class Belongs to One Branch

Setiap Class berada dalam satu Branch pada satu waktu.

---

## BR-025 — Class Can Have Multiple Tutors

Satu Class dapat memiliki lebih dari satu Tutor melalui Tutor Assignment.

---

## BR-026 — Tutor Can Teach Multiple Classes

Satu Tutor dapat memiliki banyak Tutor Assignment.

---

## BR-027 — Tutor Assignment Has Lifecycle

Assignment dapat:

```text
active
inactive
```

dan dapat menggunakan start/end date untuk histori.

---

# 7. Schedule Rules

## BR-028 — Schedule Is Recurring

Schedule adalah aturan jadwal berulang.

Contoh:

```text
Setiap Senin
19:00 - 20:30
```

Schedule bukan event aktual.

---

## BR-029 — Schedule Created Once

Admin tidak perlu membuat jadwal satu per satu setiap minggu.

Cressco menggunakan recurring schedule sebagai source.

---

## BR-030 — Schedule Belongs to Class

Setiap recurring schedule harus memiliki Class.

---

## BR-031 — Schedule Has Scheduled Tutor

Schedule menyimpan Tutor yang dijadwalkan secara normal.

```text
scheduled_tutor_id
```

Tutor tersebut harus memiliki assignment yang valid terhadap Class.

---

## BR-032 — Schedule Has Effective Period

Schedule memiliki:

```text
starts_on
ends_on
```

`ends_on` dapat nullable untuk schedule aktif tanpa tanggal akhir.

---

## BR-033 — One-Time Change Does Not Modify Recurring Rule

Jika satu sesi berubah:

```text
Schedule:
Monday 19:00
```

dan:

```text
12 Oct:
Tuesday 19:00
```

maka recurring Schedule tetap Monday 19:00.

Perubahan disimpan pada Teaching Session.

---

# 8. Teaching Session Rules

## BR-034 — Teaching Session Is Concrete Event

Teaching Session adalah kejadian mengajar aktual pada tanggal tertentu.

```text
Schedule
↓
Teaching Session
```

---

## BR-035 — Teaching Session Has Scheduled Tutor

Teaching Session menyimpan Tutor yang berasal dari schedule:

```text
scheduled_tutor_id
```

---

## BR-036 — Teaching Session Has Actual Tutor

Teaching Session juga menyimpan:

```text
actual_tutor_id
```

Actual Tutor merepresentasikan Tutor yang benar-benar mengajar.

---

## BR-037 — Normal Session

Untuk session normal:

```text
Scheduled Tutor = Actual Tutor
```

---

## BR-038 — Replacement Session

Untuk session replacement:

```text
Scheduled Tutor ≠ Actual Tutor
```

Contoh:

```text
Scheduled Tutor = Budi
Actual Tutor = Sinta
```

---

## BR-039 — Replacement Is Session-Specific

Tutor replacement tidak otomatis mengubah recurring schedule.

Future session tetap mengikuti recurring schedule kecuali Admin memang mengubah schedule.

---

## BR-040 — Session Status

Teaching Session minimal memiliki:

```text
scheduled
completed
cancelled
```

---

## BR-041 — Cancelled Session Does Not Count as Completed Session Honor

Teaching Session yang `cancelled` tidak dianggap sebagai completed session untuk honor per sesi.

---

## BR-042 — Session Completion

Teaching Session menjadi `completed` setelah aktivitas sesi selesai sesuai workflow aplikasi.

Detail exact transition harus dikunci saat implementasi state machine/UI.

---

# 9. Tutor Replacement Rules

## BR-043 — No Formal Approval Workflow in MVP

MVP tidak menggunakan:

```text
Request
→ Approval
→ Rejection
```

untuk tutor replacement.

---

## BR-044 — Tutor Communicates with Admin

Tutor yang berhalangan berkomunikasi dengan Admin di luar formal request workflow Cressco.

Admin kemudian melakukan operational replacement di sistem.

---

## BR-045 — Admin Controls Replacement

Admin yang memiliki branch scope terhadap session dapat mengganti Actual Tutor.

---

## BR-046 — Replacement Requires Reason

Replacement harus menyimpan reason.

Contoh:

```text
Tutor berhalangan
Bentrok jadwal
Kondisi darurat
Lainnya
```

---

## BR-047 — Replacement Is Auditable

Sistem mencatat:

- scheduled tutor,
- previous actual tutor,
- replacement tutor,
- actor,
- timestamp,
- reason.

---

## BR-048 — Replacement Does Not Rewrite Schedule

Replacement tidak mengubah:

```text
Recurring Schedule
```

---

## BR-049 — Replacement Affects Honor

Honor untuk session tersebut menggunakan:

```text
Actual Tutor
```

bukan Scheduled Tutor.

---

# 10. Student Attendance Rules

## BR-050 — Tutor Is Primary Attendance Operator

Tutor mengisi student attendance untuk Teaching Session yang diampunya.

---

## BR-051 — Attendance Status

MVP mendukung:

```text
Hadir
Izin
Sakit
Alpa
```

---

## BR-052 — One Attendance per Student per Session

Satu Student hanya memiliki satu active attendance record untuk satu Teaching Session.

Conceptual unique key:

```text
teaching_session_id + student_id
```

---

## BR-053 — Student Must Be Relevant to Class

Tutor hanya dapat mengisi attendance untuk Student yang memiliki Enrollment yang relevan dengan Class/Teaching Session.

---

## BR-054 — Admin Can Correct Attendance

Admin dapat melihat dan mengoreksi attendance sesuai branch scope.

Correction sebaiknya dapat dicatat sebagai audit action.

---

## BR-055 — No GPS/Selfie Attendance

MVP tidak menggunakan:

- GPS,
- geolocation,
- selfie,
- photo proof.

---

# 11. Tutor Attendance Rules

## BR-056 — No Manual Check-In/Check-Out

Tutor tidak melakukan:

```text
Check In
Check Out
```

untuk MVP.

---

## BR-057 — Tutor Attendance Is Derived from Attendance Submission

Flow:

```text
Tutor opens Teaching Session
↓
Fills Student Attendance
↓
Save succeeds
↓
Tutor Attendance recorded
```

---

## BR-058 — Opening Session Is Not Attendance

Membuka Teaching Session saja tidak membuat Tutor hadir.

---

## BR-059 — Actual Tutor Gets Tutor Attendance

Jika replacement:

```text
Scheduled Tutor = Budi
Actual Tutor = Sinta
```

dan Sinta berhasil menyelesaikan attendance workflow:

```text
Tutor Attendance → Sinta
```

Budi tidak otomatis mendapatkan attendance untuk session tersebut.

---

# 12. Assessment Rules

## BR-060 — Assessment Belongs to Class

MVP Assessment melekat pada Class.

```text
Class
↓
Assessment
```

---

## BR-061 — Assessment Types

MVP:

```text
Tugas
Quiz
Ujian
```

---

## BR-062 — Assessment Result Belongs to Student

```text
Assessment
↓
Assessment Result
↓
Student
```

---

## BR-063 — One Result per Student per Assessment

Satu Student hanya memiliki satu result aktif untuk satu Assessment.

---

# 13. Material & Notes Rules

## BR-064 — Material Belongs to Teaching Session

Material yang diajarkan pada suatu sesi melekat pada Teaching Session.

---

## BR-065 — Session Notes Belong to Teaching Session

Notes operasional pembelajaran melekat pada Teaching Session.

---

# 14. Payment Rules

## BR-066 — Admin Is Primary Payment Operator

Admin mencatat dan mengelola payment secara operasional.

---

## BR-067 — Owner Has Financial Oversight

Owner dapat melihat financial information dan payment overview.

Owner tidak harus menjadi operator pencatatan payment harian.

---

## BR-068 — Tutor Has No Payment Access

Tutor tidak dapat melihat atau mengelola payment.

---

## BR-069 — Payment Can Reference Enrollment

MVP payment idealnya dapat ditelusuri ke:

```text
Student
↓
Enrollment
↓
Payment
```

Hal ini diperlukan agar reminder dapat mengetahui kelas yang memiliki kewajiban pembayaran.

---

## BR-070 — Payment Status

MVP:

```text
Belum Bayar
Menunggu Verifikasi
Lunas
Terlambat
```

---

## BR-071 — Due Date Comes from Tenant Configuration

Payment due date berasal dari tenant configuration.

Contoh:

```text
Tanggal 5
```

atau:

```text
Tanggal 10
```

---

## BR-072 — Payment Reminder Uses Actual Data

Reminder tidak menggunakan template statis yang hanya diganti nama.

Cressco harus membaca:

```text
Student
+
Enrollment
+
Class
+
Payment
+
Due Date
```

---

## BR-073 — Multiple Classes Produce Relevant Combined Reminder

Jika satu Student memiliki beberapa kelas:

```text
Class A
Class B
Class C
```

Cressco harus menentukan kewajiban payment yang relevan dan dapat menghasilkan satu personalized reminder yang mencerminkan total yang sesuai.

---

## BR-074 — No WhatsApp API in MVP

MVP hanya:

```text
Generate Message
↓
Copy
OR
Open WhatsApp
```

WhatsApp API adalah scope future.

---

## BR-075 — Payment Gateway Out of Scope

MVP tidak membutuhkan payment gateway.

---

# 15. Honor Rules

## BR-076 — Honor Is Policy-Driven

Honor bukan metode yang dipilih ulang setiap kali calculation.

Model:

```text
Honor Scheme
↓
Honor Assignment
↓
Honor Calculation
```

---

## BR-077 — Tenant Has Default Honor Scheme

Tenant memiliki default honor policy.

Contoh:

```text
Default:
Per Sesi
Rp50.000
```

---

## BR-078 — Tutor Can Have Override

Tutor tertentu dapat memiliki override.

Contoh:

```text
Default:
Per Sesi Rp50.000

Sinta:
Fixed Monthly Rp3.000.000
```

---

## BR-079 — Hybrid Through Default + Override

MVP tidak membutuhkan method bernama `hybrid`.

Hybrid terjadi melalui:

```text
Tenant Default
+
Tutor Override
```

---

## BR-080 — Supported Honor Methods

MVP:

```text
Per Sesi
Per Siswa
Revenue Share
Fixed Monthly
```

---

## BR-081 — Honor Uses Effective Date

Honor configuration harus memiliki effective period:

```text
effective_from
effective_until
```

---

## BR-082 — Historical Honor Must Not Change

Jika rate berubah:

```text
Jan-Jun = Rp50.000
Jul-Dec = Rp60.000
```

calculation periode Jan-Jun tidak boleh berubah ketika policy baru dibuat.

---

## BR-083 — Per Session Uses Completed Sessions

Honor Per Sesi menggunakan Teaching Session yang:

```text
status = completed
```

---

## BR-084 — Cancelled Session Does Not Generate Per Session Honor

Session:

```text
cancelled
```

tidak dihitung sebagai completed session untuk Per Sesi.

---

## BR-085 — Replacement Uses Actual Tutor

Jika:

```text
Scheduled Tutor = Budi
Actual Tutor = Sinta
```

maka honor session tersebut masuk ke Sinta.

---

## BR-086 — Per Student Requires Deterministic Basis

Honor Per Siswa harus menggunakan basis yang deterministic.

Untuk MVP, basis yang direkomendasikan:

```text
jumlah Student aktif
pada periode honor
yang terkait dengan Class/Tutor Assignment yang berlaku
```

Detail partial-period/activation timing harus mengikuti effective date dan lifecycle Enrollment.

---

## BR-087 — Revenue Share Uses Recorded/Paid Revenue

Untuk MVP, Revenue Share menggunakan payment yang:

```text
recorded / paid
```

sebagai revenue basis.

Bukan hanya nominal invoice/tagihan yang belum dibayar.

---

## BR-088 — Revenue Share Percentage

Honor dihitung:

```text
Eligible Revenue × Tutor Percentage
```

Contoh:

```text
Revenue = Rp1.000.000
Tutor Share = 60%

Honor = Rp600.000
```

---

## BR-089 — Fixed Monthly

Fixed Monthly menggunakan nominal yang ditetapkan pada active Honor Scheme.

---

## BR-090 — Admin Runs Honor Calculation

Admin menjalankan:

```text
Generate
↓
Review
↓
Finalize
↓
Paid
```

---

## BR-091 — Owner Configures Honor Policy

Owner bertanggung jawab atas:

- Default Honor Scheme,
- Tutor Override,
- effective date,
- policy changes.

---

## BR-092 — Owner Oversees Honor

Owner dapat melihat:

- calculation,
- total,
- detail,
- branch,
- tutor,
- status.

---

## BR-093 — Honor Status

MVP:

```text
Draft
Final
Paid
```

---

## BR-094 — Manual Adjustment Requires Reason

Jika honor di-adjust manual:

```text
adjustment_amount
+
reason
```

harus dapat ditelusuri.

---

## BR-095 — Honor Must Be Traceable

Honor calculation harus dapat dijelaskan berdasarkan source data.

Contoh:

```text
Rp900.000
=
18 completed sessions
× Rp50.000
```

---

# 16. Multi-Branch Honor Rules

## BR-096 — Tutor Can Teach Across Branches

Tutor dapat memiliki assignment di:

```text
Surabaya
Gresik
Lamongan
```

dalam tenant yang sama.

---

## BR-097 — Honor Calculation Scope Must Be Explicit

MVP perlu memilih salah satu model:

### Model A — Consolidated

```text
Tutor
↓
One Honor Calculation
↓
All Branches
```

### Model B — Per Branch

```text
Tutor
├── Honor Calculation Surabaya
├── Honor Calculation Gresik
└── Honor Calculation Lamongan
```

**Recommended MVP:** Model B untuk traceability dan operational clarity.

Jika Owner membutuhkan total tutor:

```text
Sum branch calculations
```

---

# 17. Report Rules

## BR-098 — Reports Use Operational Source Data

Report tidak memiliki duplicate source of truth.

Contoh:

```text
Attendance Report
→ Student Attendance

Revenue Report
→ Payment

Tutor Cost
→ Honor Calculation

Teaching Session Report
→ Teaching Session
```

---

## BR-099 — Branch Filter Must Respect Scope

Owner:

```text
All Branches / Selected Branch
```

Admin:

```text
Allowed Branches
```

Tutor:

```text
Teaching Scope
```

---

# 18. Authorization-Relevant Business Rules

## BR-100 — Owner Tenant Scope

Owner hanya boleh berada dalam tenant-nya sendiri.

---

## BR-101 — Admin Branch Scope

Admin hanya boleh mengoperasikan data pada branch yang diberikan.

---

## BR-102 — Tutor Teaching Scope

Tutor hanya dapat mengakses:

- assigned classes,
- relevant teaching sessions,
- relevant students,
- attendance,
- assessment,
- material,
- notes.

---

## BR-103 — Tutor Cannot Access Financial Data

Tutor tidak boleh membaca:

```text
Payment
Honor Calculation
Financial Report
```

---

## BR-104 — Cross-Tenant Resource Access Is Forbidden

Memiliki resource ID yang valid tidak cukup untuk mendapatkan akses.

Contoh:

```text
GET /students/{student_id}
```

tetap harus memvalidasi:

```text
student.tenant_id == currentTenant.id
```

---

# 19. Audit Rules

## BR-105 — Important Financial Changes Are Auditable

Minimal:

- Payment changes,
- Honor scheme changes,
- Honor calculation finalization,
- Manual honor adjustments.

---

## BR-106 — Operational Changes Are Auditable

Minimal:

- Tutor replacement,
- Branch access changes,
- Role changes,
- important tenant setting changes.

---

## BR-107 — Audit Actor Is Preserved

Audit harus mencatat siapa yang melakukan action.

```text
actor_user_id
```

---

# 20. Deletion Rules

## BR-108 — Historical Records Should Not Be Hard Deleted

Data yang menjadi dasar histori:

- Attendance,
- Payment,
- Honor,
- Assessment,
- Teaching Session,
- Enrollment history,

tidak boleh dihapus sembarangan.

---

## BR-109 — Prefer Deactivation

Untuk master data:

```text
Student
Class
Tutor Assignment
Schedule
Branch
User
```

gunakan status/deactivation jika memungkinkan.

---

# 21. Tenant Configuration Rules

## BR-110 — Tenant Configuration Is Tenant Scoped

Configuration tenant tidak boleh dibagi lintas tenant.

---

## BR-111 — Payment Due Day Is Tenant-Level

MVP:

```text
Tenant
↓
Payment Due Day
```

Branch-specific payment due date bukan requirement MVP.

---

## BR-112 — Honor Policy Is Tenant-Level by Default

Default Honor Scheme berlaku pada tenant.

Tutor-specific exception menggunakan override.

---

# 22. Session Generation Rules

## BR-113 — Schedule Is Source of Session

Teaching Session berasal dari recurring Schedule.

---

## BR-114 — Session Generation Must Be Idempotent

Jika sistem menjalankan generation lebih dari sekali, sistem tidak boleh membuat duplicate Teaching Session untuk kombinasi schedule/date yang sama.

Conceptual uniqueness:

```text
schedule_id + session_date
```

dengan pengecualian jika business model nantinya mengizinkan multiple sessions pada satu date dari satu schedule.

---

## BR-115 — Existing Session Is Preserved

Jika Teaching Session sudah dibuat, perubahan recurring schedule tidak boleh secara otomatis merusak histori session yang sudah terjadi.

---

# 23. Data Consistency Rules

## BR-116 — Same Tenant Across Relationship

Relasi berikut harus berada dalam tenant yang sama:

```text
Student ↔ Enrollment ↔ Class
Tutor ↔ Assignment ↔ Class
Schedule ↔ Class ↔ Tutor
Teaching Session ↔ Class ↔ Tutor
Payment ↔ Student ↔ Enrollment
Honor Assignment ↔ Tutor
Honor Calculation ↔ Tutor ↔ Honor Scheme
```

---

## BR-117 — Branch Consistency

Untuk branch-scoped records:

```text
tenant_id
```

harus konsisten dengan:

```text
branch.tenant_id
```

---

## BR-118 — Session Branch Comes from Class/Schedule Context

Teaching Session harus memiliki branch yang konsisten dengan Class dan Schedule.

---

## BR-119 — Attendance Branch Comes from Session

Student Attendance dan Tutor Attendance menggunakan branch context dari Teaching Session.

---

# 24. Lifecycle Rules

## BR-120 — Inactive Student

Student yang inactive:

- tidak dapat menerima enrollment baru secara normal,
- tetap memiliki histori attendance/payment/assessment.

---

## BR-121 — Inactive Class

Class inactive:

- tidak dapat menerima enrollment baru,
- tidak dapat menerima schedule baru,
- histori tetap tersedia.

---

## BR-122 — Inactive Tutor Assignment

Inactive assignment:

- tidak digunakan untuk future scheduling,
- histori tetap tersedia.

---

## BR-123 — Inactive Schedule

Inactive schedule:

- tidak menghasilkan future sessions,
- existing historical sessions tetap tersedia.

---

# 25. Support Mode Rules - Phase 2

## BR-124 — Super Admin Remains Super Admin

Support Mode tidak mengubah:

```text
users.role
```

---

## BR-125 — Support Mode Is Tenant-Bound

Ketika Super Admin mengakses tenant:

```text
current tenant = selected tenant
```

tetapi actor tetap Super Admin.

---

## BR-126 — Support Actions Are Audited

Action yang dilakukan dalam Support Mode harus menyimpan:

- actor,
- tenant,
- action,
- timestamp,
- support mode flag.

---

## BR-127 — No Arbitrary User Impersonation in MVP

MVP platform access hanya:

```text
Access Tenant Dashboard
```

bukan:

```text
Login as Owner
Login as Admin
Login as Tutor
```

---

# 26. Business Rule Dependencies

Relationship antar rule:

```text
Tenant Isolation
↓
Branch Scope
↓
Role Scope
↓
Student / Class / Tutor Data
↓
Schedule
↓
Teaching Session
↓
Attendance
↓
Actual Tutor
↓
Honor
↓
Reports
```

Payment flow:

```text
Tenant Payment Config
↓
Enrollment
↓
Payment
↓
Outstanding
↓
Reminder
↓
Financial Report
```

---

# 27. Rule-to-Entity Mapping

| Rule Area | Main Entities |
|---|---|
| Tenant isolation | Tenant, User, all operational entities |
| Branch scope | Branch, Branch User |
| Student | Student |
| Enrollment | Student, Enrollment, Class |
| Tutor | User, Tutor Assignment |
| Schedule | Schedule |
| Session | Teaching Session |
| Attendance | Student Attendance, Tutor Attendance |
| Assessment | Assessment, Assessment Result |
| Payment | Payment, Enrollment |
| Reminder | Payment, Enrollment, Student, Tenant Settings |
| Honor | Honor Scheme, Honor Assignment, Honor Calculation |
| Replacement | Teaching Session, Tutor Replacement |
| Audit | Audit Log |
| Platform support | User, Tenant, Audit Log |

---

# 28. Decisions Locked for DATABASE-SCHEMA.sql

The following rules are sufficiently defined for schema generation:

1. Multi-tenant architecture.
2. Tenant ID as operational boundary.
3. Branch belongs to Tenant.
4. Owner has full tenant branch access.
5. Admin uses explicit branch access.
6. Tutor scope derives from class assignment.
7. Student ↔ Class uses Enrollment.
8. Tutor ↔ Class uses Tutor Assignment.
9. Schedule is recurring.
10. Teaching Session is concrete.
11. Scheduled Tutor and Actual Tutor are separate.
12. Replacement is session-specific.
13. Replacement has reason and audit trail.
14. Student attendance is per session/student.
15. Tutor attendance is derived from attendance submission.
16. Assessment belongs to Class.
17. Assessment Result belongs to Student + Assessment.
18. Payment can reference Enrollment.
19. Payment due date is tenant-level.
20. Honor uses Scheme → Assignment → Calculation.
21. Default Honor Scheme exists.
22. Tutor override exists.
23. Honor has effective dates.
24. Supported methods are Per Session, Per Student, Revenue Share, Fixed Monthly.
25. Revenue Share uses recorded/paid revenue for MVP.
26. Honor per session uses completed sessions.
27. Replacement uses Actual Tutor.
28. Honor calculation status is Draft/Final/Paid.
29. Important operational/financial changes are auditable.
30. Super Admin is Phase 2 platform role.

---

# 29. Remaining Decisions Before Final SQL

The following should be treated as implementation-level decisions before final production schema:

### 29.1 Student Cross-Branch

MVP assumes:

```text
Student → Branch
Enrollment.branch_id = Class.branch_id
```

If cross-branch enrollment becomes a requirement, this needs redesign.

### 29.2 Honor Per Student Partial Period

Need exact rule for:

- student joins mid-month,
- student leaves mid-month,
- enrollment starts/ends during period.

### 29.3 Revenue Share Allocation

Need exact treatment for:

- one payment covering multiple classes,
- partial payments,
- refunds,
- payment date vs service period.

### 29.4 Honor Calculation Multi-Branch

Recommended:

```text
One calculation per Tutor + Branch + Period
```

with Owner aggregation across branches.

### 29.5 Session Generation Horizon

Need implementation policy for how far into the future sessions are generated.

Possible:

```text
30 days
60 days
90 days
rolling generation
```

This is an application workflow concern, not a conceptual ERD requirement.

---

# 30. Final Data Model Principle

Cressco should maintain a single connected operational data model.

```text
TENANT
  ↓
BRANCH
  ↓
STUDENT
  ↓
ENROLLMENT
  ↓
CLASS
  ↓
TUTOR ASSIGNMENT
  ↓
SCHEDULE
  ↓
TEACHING SESSION
  ├── STUDENT ATTENDANCE
  ├── TUTOR ATTENDANCE
  ├── MATERIAL
  ├── NOTES
  └── TUTOR REPLACEMENT
          ↓
      ACTUAL TUTOR
          ↓
   HONOR CALCULATION

STUDENT
  ↓
PAYMENT
  ↓
PAYMENT REMINDER

CLASS
  ↓
ASSESSMENT
  ↓
ASSESSMENT RESULT

ALL IMPORTANT OPERATIONS
  ↓
AUDIT LOG
```

The core principle is:

> **Operational data should be entered once and reused by downstream workflows rather than duplicated across modules.**
