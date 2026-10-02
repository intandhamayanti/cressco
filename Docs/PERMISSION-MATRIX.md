# Cressco - Permission Matrix

> **Document:** PERMISSION-MATRIX.md
> **Product:** Cressco
> **Scope:** Core Bimbel MVP + Super Admin Phase 2
> **Primary Roles:** Owner, Admin, Tutor/Tentor
> **Platform Role:** Super Admin
> **Purpose:** Mendefinisikan authorization matrix berdasarkan Business Rules sebelum database schema dan Laravel implementation.

---

# 1. Purpose

Permission Matrix menjawab:

> **Siapa boleh melakukan apa, terhadap resource apa, dan dalam scope mana?**

Model authorization Cressco:

```text
Role
  ↓
Resource
  ↓
Action
  ↓
Scope
  ↓
Business Rule
```

Contoh:

```text
Admin
→ Student
→ Create / Read / Update
→ Allowed Branch
```

Sedangkan:

```text
Owner
→ Student
→ Read
→ Entire Tenant
```

Permission Matrix bukan pengganti Business Rules.

- `BUSINESS-RULES.md` menjelaskan bagaimana sistem bekerja.
- `PERMISSION-MATRIX.md` menjelaskan siapa yang boleh mengaksesnya.

---

# 2. Authorization Principles

## PM-001 — Authentication Is Not Authorization

Login hanya membuktikan identitas user.

Setelah login, sistem tetap harus mengevaluasi:

```text
user.role
+
tenant
+
branch scope
+
resource ownership/assignment
+
action
```

---

## PM-002 — Never Authorize by Email

Permission tidak ditentukan berdasarkan:

```text
email domain
email address
```

Authorization menggunakan role dan relationship data.

---

## PM-003 — Tenant Isolation Is Mandatory

Tidak ada role tenant-side yang boleh mengakses tenant lain.

```text
Owner Tenant A → Tenant A only
Admin Tenant A → Tenant A only
Tutor Tenant A → Tenant A only
```

---

## PM-004 — Scope Is Part of Permission

Permission bukan sekadar:

```text
Admin can update Student
```

tetapi:

```text
Admin
→ Student
→ Update
→ Allowed Branches
```

---

## PM-005 — Owner Uses Tenant Scope

Owner memiliki full tenant scope.

Branch selector hanya mengubah data context.

---

## PM-006 — Admin Uses Branch Scope

Admin hanya boleh melakukan operational action pada branch yang diberikan kepadanya.

---

## PM-007 — Tutor Uses Teaching Scope

Tutor tidak mendapat akses seluruh branch hanya karena mengajar di branch tersebut.

Tutor scope berasal dari:

```text
Tutor
↓
Tutor Assignment
↓
Class
↓
Teaching Session
```

---

## PM-008 — UI Visibility Is Not Security

Menyembunyikan menu bukan authorization.

Backend harus tetap melakukan authorization check.

---

# 3. Action Vocabulary

Gunakan action berikut secara konsisten:

| Action | Meaning |
|---|---|
| View | Melihat detail/resource |
| List | Melihat collection |
| Create | Membuat resource |
| Update | Mengubah resource |
| Delete | Menghapus resource jika business rule mengizinkan |
| Activate | Mengaktifkan |
| Deactivate | Menonaktifkan |
| Invite | Mengundang user |
| Assign | Memberikan relationship/scope |
| Replace | Mengganti operational actor |
| Correct | Mengoreksi data historis |
| Generate | Membuat calculation/report/session |
| Finalize | Mengunci calculation |
| Mark Paid | Menandai honor sebagai dibayar |
| Configure | Mengubah policy/configuration |
| Export | Mengekspor data |
| Access | Masuk ke tenant/resource context |
| Support | Mengakses tenant untuk troubleshooting |

---

# 4. Scope Vocabulary

## 4.1 Tenant Scope

User dapat mengakses seluruh resource dalam tenant.

```text
tenant_id = current_user.tenant_id
```

Owner menggunakan scope ini.

---

## 4.2 Branch Scope

User hanya dapat mengakses branch yang terhubung melalui:

```text
branch_user
```

Admin menggunakan scope ini.

---

## 4.3 Teaching Scope

Tutor hanya dapat mengakses resource yang relevan dengan:

```text
Tutor Assignment
→ Class
→ Teaching Session
```

---

## 4.4 Own Scope

Resource milik user sendiri.

Contoh:

```text
User Profile
```

---

## 4.5 Platform Scope

Super Admin dapat mengakses platform-level resources lintas tenant.

Ini adalah Phase 2.

---

# 5. Role Summary

| Capability | Owner | Admin | Tutor | Super Admin |
|---|---|---|---|---|
| Tenant-wide oversight | Yes | No | No | Platform |
| Branch management | Yes | No | No | Yes |
| Admin management | Yes | No | No | Yes |
| Tutor management | Oversight | Yes | No | Yes |
| Student management | Oversight | Yes | Limited teaching scope | Yes |
| Class management | Oversight | Yes | No | Yes |
| Schedule management | Oversight | Yes | No | Yes |
| Attendance | View | Manage/Correct | Record | Yes |
| Payment | Oversight | Manage | No | Yes |
| Honor policy | Configure | Operate | No | Yes |
| Honor calculation | Oversight | Generate/Finalize/Operate | No | Yes |
| Reports | Full tenant | Operational scope | Teaching scope | Platform |
| Tenant settings | Yes | No | No | Yes |
| Platform settings | No | No | No | Yes |
| Support Mode | No | No | No | Yes |

---

# 6. Owner Permission Matrix

## 6.1 Dashboard

| Resource | View | List | Create | Update | Delete | Scope |
|---|---:|---:|---:|---:|---:|---|
| Dashboard | Yes | - | - | - | - | Tenant |
| KPI | Yes | - | - | - | - | Tenant |
| Branch comparison | Yes | - | - | - | - | Tenant |

Owner dapat menggunakan:

```text
Semua Cabang
```

atau:

```text
Branch tertentu
```

sebagai viewing context.

---

## 6.2 Branch

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Delete | No |
| Assign Admin | Yes |

Scope:

```text
Tenant
```

---

## 6.3 Admin User

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create/Invite | Yes |
| Update | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Delete | No |
| Assign Branch | Yes |
| Remove Branch Access | Yes |
| Change Role | Limited/Yes* |

`*` Role change harus dibatasi agar Owner tidak dapat membuat privilege yang tidak sesuai model authorization.

Scope:

```text
Tenant
```

---

## 6.4 Tutor

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create/Invite | No |
| Update | Oversight only |
| Activate | Oversight |
| Deactivate | Oversight |
| Assign to Class | No |
| View Assignment | Yes |
| View Sessions | Yes |
| View Attendance | Yes |
| View Honor | Yes |

Tutor creation tetap merupakan workflow Admin.

Scope:

```text
Tenant
```

---

## 6.5 Student

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create | No |
| Update | Oversight only |
| Deactivate | Oversight |
| Delete | No |
| View Enrollment | Yes |
| View Attendance | Yes |
| View Payment | Yes |
| View Assessment | Yes |

Scope:

```text
Tenant
```

Owner dapat melihat student lintas branch.

---

## 6.6 Enrollment

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create | No |
| Update | No |
| Activate | Oversight |
| Deactivate | Oversight |

Scope:

```text
Tenant
```

Daily enrollment workflow adalah Admin.

---

## 6.7 Class

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create | No |
| Update | Oversight |
| Activate | Oversight |
| Deactivate | Oversight |
| Delete | No |
| View Tutor Assignment | Yes |

Scope:

```text
Tenant
```

---

## 6.8 Schedule

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create | No |
| Update | Oversight |
| Deactivate | Oversight |
| View Sessions | Yes |

Scope:

```text
Tenant
```

---

## 6.9 Teaching Session

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create | No* |
| Update | Oversight |
| Cancel | Oversight |
| View Attendance | Yes |
| View Actual Tutor | Yes |
| View Replacement | Yes |

`*` Session generation berasal dari Schedule.

Scope:

```text
Tenant
```

---

## 6.10 Attendance

| Action | Owner |
|---|---|
| View Student Attendance | Yes |
| Correct Student Attendance | No* |
| View Tutor Attendance | Yes |
| Create | No |
| Delete | No |

`*` Operational correction dilakukan Admin. Owner memiliki oversight.

Scope:

```text
Tenant
```

---

## 6.11 Payment

| Action | Owner |
|---|---|
| List | Yes |
| View | Yes |
| Create | No |
| Update | No |
| Verify | Oversight |
| Correct | No |
| Export | Yes |

Scope:

```text
Tenant
```

Admin adalah payment operator.

---

## 6.12 Parent Reminder

| Action | Owner |
|---|---|
| View Outstanding | Yes |
| View Reminder Status | Yes |
| Generate | No |
| Copy | No |
| Open WhatsApp | No |

Reminder generation adalah operational workflow Admin.

---

## 6.13 Honor Scheme

| Action | Owner |
|---|---|
| View | Yes |
| Create | Yes |
| Update | Yes |
| Deactivate | Yes |
| Assign Default | Yes |
| Assign Tutor Override | Yes |
| Configure Effective Date | Yes |

Scope:

```text
Tenant
```

---

## 6.14 Honor Calculation

| Action | Owner |
|---|---|
| View | Yes |
| List | Yes |
| Generate | No |
| Update Draft | No |
| Finalize | No |
| Mark Paid | No |
| Adjust | No |
| Export | Yes |

Owner adalah financial oversight.

---

## 6.15 Reports

| Report | Owner |
|---|---|
| Business | Yes |
| Student | Yes |
| Attendance | Yes |
| Tutor | Yes |
| Financial | Yes |
| Branch Comparison | Yes |

Scope:

```text
Tenant
```

---

## 6.16 Tenant Settings

| Action | Owner |
|---|---|
| View | Yes |
| Update | Yes |
| Configure Payment Due Day | Yes |
| Configure Honor Policy | Yes |
| Update Branding | Yes |
| Update Contact | Yes |

Scope:

```text
Tenant
```

---

## 6.17 Profile

| Action | Owner |
|---|---|
| View Own Profile | Yes |
| Update Own Profile | Yes |
| Change Password | Yes |

Scope:

```text
Own
```

---

# 7. Admin Permission Matrix

## 7.1 Dashboard

| Action | Admin |
|---|---|
| View | Yes |
| View KPI | Yes |
| View Alerts | Yes |
| Quick Actions | Yes |

Scope:

```text
Allowed Branches
```

---

## 7.2 Branch

| Action | Admin |
|---|---|
| List | Yes* |
| View | Yes |
| Create | No |
| Update | No |
| Activate | No |
| Deactivate | No |
| Manage Access | No |

`*` Hanya branch yang diberikan access.

---

## 7.3 Student

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Delete | No |
| View Enrollment | Yes |
| View Attendance | Yes |
| View Payment | Yes |
| View Assessment | Yes |

Scope:

```text
Allowed Branches
```

---

## 7.4 Enrollment

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Withdraw | Yes |

Scope:

```text
Allowed Branches
```

Enrollment must satisfy Student/Class tenant and branch consistency rules.

---

## 7.5 Class

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Delete | No |
| Manage Capacity | Yes |
| Manage Tutor Assignment | Yes |

Scope:

```text
Allowed Branches
```

---

## 7.6 Tutor

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Invite | Yes |
| Create | Yes |
| Update | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Delete | No |
| View Assignment | Yes |
| Assign to Class | Yes |
| Remove Assignment | Yes |

Scope:

```text
Tutor/class must belong to allowed branch scope.
```

A Tutor may have assignments across multiple branches.

---

## 7.7 Tutor Assignment

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Delete | No |

Scope:

```text
Allowed Branches
```

Tutor assignment must reference a Tutor and Class within the same tenant.

---

## 7.8 Schedule

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Delete | No |
| Generate Sessions | Yes |

Scope:

```text
Allowed Branches
```

---

## 7.9 Teaching Session

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | System/Admin* |
| Update | Yes |
| Cancel | Yes |
| Reschedule | Yes |
| Set Actual Tutor | Yes |
| Replace Tutor | Yes |
| View Attendance | Yes |
| Correct Session Data | Yes |

`*` Normal sessions are generated from recurring schedules. Admin may create/manage exceptions according to the schedule workflow.

Scope:

```text
Allowed Branches
```

---

## 7.10 Student Attendance

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes* |
| Update/Correct | Yes |
| Delete | No |

`*` Primary entry is Tutor; Admin may correct/operate attendance as needed.

Scope:

```text
Allowed Branches
```

---

## 7.11 Tutor Attendance

| Action | Admin |
|---|---|
| View | Yes |
| Correct | Yes |
| Create Manually | No* |
| Delete | No |

`*` Tutor attendance is derived from successful attendance submission.

---

## 7.12 Assessment

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Delete | No |
| View Results | Yes |

Scope:

```text
Allowed Branches
```

---

## 7.13 Assessment Result

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Delete | No |

Scope:

```text
Allowed Branches
```

---

## 7.14 Payment

| Action | Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Verify | Yes |
| Correct | Yes |
| Delete | No |
| Export | Yes |

Scope:

```text
Allowed Branches
```

---

## 7.15 Parent Reminder

| Action | Admin |
|---|---|
| View Outstanding | Yes |
| Generate | Yes |
| Copy | Yes |
| Open WhatsApp | Yes |
| View Reminder History | Yes* |

`*` If reminder history is persisted in MVP.

Scope:

```text
Allowed Branches
```

---

## 7.16 Honor Scheme

| Action | Admin |
|---|---|
| View | Yes |
| Create | No |
| Update | No |
| Deactivate | No |
| View Default | Yes |
| View Tutor Override | Yes |

Owner controls policy.

---

## 7.17 Honor Calculation

| Action | Admin |
|---|---|
| View | Yes |
| List | Yes |
| Generate | Yes |
| Update Draft | Yes |
| Finalize | Yes |
| Mark Paid | Yes |
| Adjust | Yes |
| Export | Yes |

Scope:

```text
Allowed Branches
```

Manual adjustment requires reason.

---

## 7.18 Reports

| Report | Admin |
|---|---|
| Student | Yes |
| Attendance | Yes |
| Class | Yes |
| Tutor | Yes |
| Payment | Yes |
| Honor | Yes |
| Business | Limited operational |
| Branch Comparison | Only if multiple allowed branches |

Scope:

```text
Allowed Branches
```

---

## 7.19 Tenant Settings

| Action | Admin |
|---|---|
| View | Limited* |
| Update | No |
| Payment Due Day | No |
| Honor Policy | No |
| Branding | No |
| Contact | No |

`*` Only settings explicitly exposed as operational read-only information.

---

## 7.20 Profile

| Action | Admin |
|---|---|
| View Own Profile | Yes |
| Update Own Profile | Yes |
| Change Password | Yes |

Scope:

```text
Own
```

---

# 8. Tutor Permission Matrix

## 8.1 Dashboard

| Action | Tutor |
|---|---|
| View | Yes |
| View Today's Schedule | Yes |
| View Attendance Tasks | Yes |
| View Assigned Classes | Yes |

Scope:

```text
Teaching Scope
```

---

## 8.2 Student

| Action | Tutor |
|---|---|
| List | Yes* |
| View | Yes |
| Create | No |
| Update | No |
| Delete | No |
| View Attendance | Yes |
| View Assessment | Yes* |
| View Payment | No |

`*` Only students relevant to assigned classes/session.

---

## 8.3 Class

| Action | Tutor |
|---|---|
| List | Yes |
| View | Yes |
| Create | No |
| Update | No |
| Delete | No |
| View Assigned Classes | Yes |

Scope:

```text
Assigned Classes
```

---

## 8.4 Schedule

| Action | Tutor |
|---|---|
| List | Yes |
| View | Yes |
| Create | No |
| Update | No |
| Delete | No |

Scope:

```text
Teaching Scope
```

---

## 8.5 Teaching Session

| Action | Tutor |
|---|---|
| List | Yes |
| View | Yes |
| Update Material | Yes |
| Update Notes | Yes |
| Complete Session Workflow | Yes |
| Set Actual Tutor | No |
| Replace Tutor | No |
| Cancel | No |

Scope:

```text
Actual Tutor / Scheduled Tutor context
```

A Tutor may view a session when they are relevant to the session according to assignment/session data.

---

## 8.6 Student Attendance

| Action | Tutor |
|---|---|
| View | Yes |
| Create | Yes |
| Update Own Session Attendance | Yes |
| Correct Historical Attendance | No |
| Delete | No |

Scope:

```text
Teaching Session assigned to Tutor
```

---

## 8.7 Tutor Attendance

| Action | Tutor |
|---|---|
| View Own | Yes |
| Create Check-In | No |
| Create Check-Out | No |
| Manual Edit | No |

Tutor attendance is derived by the system from successful student attendance submission.

---

## 8.8 Assessment

| Action | Tutor |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes* |
| Update | Yes* |
| Delete | No |
| View Results | Yes |

`*` For assessments/results related to classes the Tutor teaches.

Scope:

```text
Assigned Classes
```

---

## 8.9 Assessment Result

| Action | Tutor |
|---|---|
| View | Yes |
| Create | Yes |
| Update | Yes |
| Delete | No |

Scope:

```text
Assigned Classes
```

---

## 8.10 Payment

| Action | Tutor |
|---|---|
| List | No |
| View | No |
| Create | No |
| Update | No |
| Verify | No |
| Export | No |

---

## 8.11 Parent Reminder

| Action | Tutor |
|---|---|
| View Payment Outstanding | No |
| Generate Payment Reminder | No |
| Copy Payment Reminder | No |
| Open WhatsApp Payment Reminder | No |

---

## 8.12 Honor

| Action | Tutor |
|---|---|
| View Honor Calculation | No* |
| View Honor Detail | No |
| Create | No |
| Update | No |
| Adjust | No |
| Finalize | No |
| Mark Paid | No |

`*` Tutor honor visibility is intentionally excluded from MVP Tutor scope. Honor is operational/financial data handled by Owner/Admin.

---

## 8.13 Reports

| Report | Tutor |
|---|---|
| Teaching Schedule | Yes |
| Attendance | Yes |
| Student Learning/Assessment | Yes |
| Payment | No |
| Honor | No |
| Financial | No |
| Business | No |

Scope:

```text
Teaching Scope
```

---

## 8.14 Profile

| Action | Tutor |
|---|---|
| View Own Profile | Yes |
| Update Own Profile | Yes |
| Change Password | Yes |

Scope:

```text
Own
```

---

# 9. Super Admin Permission Matrix - Phase 2

Super Admin is a platform role, not a tenant operational role.

## 9.1 Tenant

| Action | Super Admin |
|---|---|
| List | Yes |
| View | Yes |
| Create | Yes |
| Update | Yes |
| Activate | Yes |
| Suspend | Yes |
| Delete | No* |
| Access Tenant Dashboard | Yes |
| Support Mode | Yes |

`*` Tenant deletion is intentionally restricted.

Scope:

```text
Platform
```

---

## 9.2 Tenant Users

| Action | Super Admin |
|---|---|
| List | Yes |
| View | Yes |
| Activate | Yes |
| Deactivate | Yes |
| Reset Access | Yes* |

`*` Exact password/reset mechanism should follow authentication architecture.

Scope:

```text
Platform
```

---

## 9.3 Support Mode

| Action | Super Admin |
|---|---|
| Access Tenant Dashboard | Yes |
| Exit Tenant | Yes |
| Mutate Tenant Data | Yes* |
| Impersonate User | No |

`*` Tenant data mutation in support mode is allowed only where the underlying resource permission supports the action and every action is audited with Super Admin actor + support mode context.

---

## 9.4 Platform Settings

| Action | Super Admin |
|---|---|
| View | Yes |
| Update | Yes |

Scope:

```text
Platform
```

---

## 9.5 Audit

| Action | Super Admin |
|---|---|
| List | Yes |
| View | Yes |
| Filter | Yes |
| Export | Yes |

Scope:

```text
Platform
```

---

# 10. Resource-Level Matrix

This condensed matrix is the primary implementation reference.

Legend:

- **F** = Full management
- **O** = Oversight/read-oriented access
- **T** = Teaching scope
- **B** = Allowed branch scope
- **S** = Own scope
- **N** = No access
- **P2** = Phase 2

| Resource | Owner | Admin | Tutor | Super Admin |
|---|---|---|---|---|
| Dashboard | F | F | T | F P2 |
| Branch | F | O | N | F P2 |
| Admin Users | F | N | N | F P2 |
| Tutor Users | O | F | N | F P2 |
| Students | O | F/B | T | F P2 |
| Enrollment | O | F/B | T View | F P2 |
| Classes | O | F/B | T View | F P2 |
| Tutor Assignment | O | F/B | T View | F P2 |
| Schedule | O | F/B | T View | F P2 |
| Teaching Session | O | F/B | T | F P2 |
| Student Attendance | O | F/B | T Create/Update | F P2 |
| Tutor Attendance | O | F/B | S View | F P2 |
| Assessment | O | F/B | T | F P2 |
| Assessment Result | O | F/B | T | F P2 |
| Payment | O | F/B | N | F P2 |
| Parent Reminder | O View | F/B | N | F P2 |
| Honor Scheme | F | O | N | F P2 |
| Honor Calculation | O | F/B | N | F P2 |
| Reports | F | F/B | T | F P2 |
| Tenant Settings | F | N | N | F P2 |
| Profile | S | S | S | S |
| Platform Settings | N | N | N | F P2 |
| Platform Audit | N | N | N | F P2 |
| Support Mode | N | N | N | F P2 |

---

# 11. Owner vs Admin Boundary

This boundary must remain explicit.

| Capability | Owner | Admin |
|---|---|---|
| Business control | Yes | No |
| Branch management | Yes | No |
| Admin management | Yes | No |
| Tutor operational management | Oversight | Yes |
| Student daily management | Oversight | Yes |
| Enrollment | Oversight | Yes |
| Class daily management | Oversight | Yes |
| Schedule management | Oversight | Yes |
| Attendance correction | Oversight | Yes |
| Payment recording | Oversight | Yes |
| Payment verification | Oversight | Yes |
| Parent reminder | View | Operate |
| Honor policy | Configure | No |
| Honor calculation | Oversight | Operate |
| Financial oversight | Yes | Operational |
| Tenant settings | Yes | No |

Important:

> Admin having more operational menu items than Owner does not mean Admin has higher authority.

---

# 12. Branch Authorization Rules

## Owner

```text
Owner
→ all branches
```

No branch restriction.

---

## Admin

```text
Admin
→ branch_user
→ allowed branches
```

Every branch-scoped query must enforce allowed branches.

---

## Tutor

```text
Tutor
→ tutor_assignment
→ class
→ branch
→ teaching session
```

Tutor does not receive generic branch access.

---

# 13. Resource Authorization Examples

## Example 1 — Admin Reads Student

Allowed:

```text
Student.tenant_id
=
Admin.tenant_id

AND

Student.branch_id
IN Admin.allowed_branches
```

---

## Example 2 — Owner Reads Student

Allowed if:

```text
Student.tenant_id
=
Owner.tenant_id
```

Branch selection is only filtering/context.

---

## Example 3 — Tutor Reads Student

Allowed if Student is relevant to a Class assigned to Tutor.

Conceptually:

```text
Tutor
↓
Tutor Assignment
↓
Class
↓
Enrollment
↓
Student
```

---

## Example 4 — Admin Updates Payment

Allowed if:

```text
Payment.tenant_id
=
Admin.tenant_id

AND

Payment.branch_id
IN Admin.allowed_branches
```

---

## Example 5 — Tutor Attempts Payment

Denied regardless of branch.

```text
Tutor
→ Payment
→ N
```

---

# 14. Authorization Layers in Laravel

Recommended authorization layers:

```text
Authentication
    ↓
Role Authorization
    ↓
Tenant Authorization
    ↓
Branch/Teaching Scope Authorization
    ↓
Resource Policy
    ↓
Business Rule Validation
    ↓
Action
```

---

## 14.1 Middleware

Middleware is appropriate for broad context:

```text
auth
role
tenant
```

Examples:

```text
auth
role:owner
role:admin
role:tutor
```

Do not rely on role middleware alone for branch/resource access.

---

## 14.2 Policies

Laravel Policies should handle resource-level authorization.

Conceptually:

```text
StudentPolicy
ClassPolicy
SchedulePolicy
TeachingSessionPolicy
PaymentPolicy
HonorCalculationPolicy
```

---

## 14.3 Branch Scope Service

A reusable authorization/query scope should determine:

```text
Accessible Branch IDs
```

For example:

```text
Owner → all tenant branches
Admin → branch_user branches
Tutor → derived teaching scope
```

---

## 14.4 Query Scoping

Authorization must also be reflected in queries.

Do not:

```php
Student::all();
```

for branch-scoped Admin pages.

Use tenant/branch-scoped query builders or services.

---

# 15. Permission Implementation Pattern

Recommended application pattern:

```text
Route
↓
Middleware
↓
Livewire Component / Controller
↓
Policy
↓
Scoped Query
↓
Business Validation
↓
Service
↓
Database
```

For Livewire:

```text
mount()
→ authorize view

save()
→ authorize update/create
→ validate
→ execute service
```

---

# 16. Permission Naming Convention

Recommended permission concepts for future granular permission support:

```text
students.view
students.create
students.update

classes.view
classes.create
classes.update

schedules.view
schedules.create
schedules.update

attendance.view
attendance.create
attendance.correct

payments.view
payments.create
payments.update
payments.verify

honor.view
honor.calculate
honor.finalize
honor.pay

reports.view

settings.view
settings.update
```

However:

> **MVP should not necessarily implement a full permission-table/RBAC system.**

Role + Policies + Scope is sufficient initially.

---

# 17. Role-Based Access Control Strategy

MVP authorization model:

```text
Role
+
Tenant
+
Branch Scope
+
Resource Relationship
```

rather than:

```text
Role
+
Hundreds of manually assigned permissions
```

This keeps the initial product simpler.

Future granular permissions can be added if enterprise requirements emerge.

---

# 18. Explicit Denials

The following are explicit MVP denials.

## Owner

Owner cannot:

- access another tenant,
- access platform settings,
- use Super Admin support mode,
- perform platform administration.

## Admin

Admin cannot:

- manage tenant branches,
- manage Admin users,
- change tenant settings,
- configure honor policy,
- access other tenants,
- access platform settings.

## Tutor

Tutor cannot:

- manage users,
- manage branches,
- manage classes,
- manage schedules,
- manage payments,
- manage honor,
- access financial reports,
- access other tenant data,
- change tutor replacement,
- perform platform administration.

---

# 19. Support Mode Authorization - Phase 2

Support Mode must be distinguishable from ordinary tenant user access.

Context:

```text
Actor:
Super Admin

Tenant:
Bimbel Ceria

Mode:
Support
```

UI:

```text
SUPPORT MODE - Bimbel Ceria

You are accessing this tenant as Super Admin.

[ Exit Tenant ]
```

Every mutation must preserve:

```text
actor_user_id = Super Admin
support_mode = true
tenant_id = target tenant
```

The system must not mutate the Super Admin's stored role into Owner/Admin/Tutor.

---

# 20. Permission Matrix and Business Rules Relationship

Permission alone is insufficient.

Example:

```text
Admin
→ Honor Calculation
→ Finalize
```

is allowed by permission.

But business rules still require:

```text
Calculation.status = Draft
```

before Finalize.

Similarly:

```text
Admin
→ Replace Tutor
```

is allowed.

Business rules require:

```text
Session belongs to allowed branch
+
replacement reason exists
+
actual tutor is valid
+
audit event created
```

Therefore:

```text
Permission
+
Business Rule
=
Valid Action
```

---

# 21. Authorization Test Matrix

Minimum authorization tests before MVP release:

| Test | Expected |
|---|---|
| Owner reads own tenant | Allow |
| Owner reads another tenant | Deny |
| Admin reads allowed branch | Allow |
| Admin reads unauthorized branch | Deny |
| Admin updates allowed student | Allow |
| Admin updates unauthorized student | Deny |
| Tutor reads assigned student | Allow |
| Tutor reads unrelated student | Deny |
| Tutor records attendance for assigned session | Allow |
| Tutor records attendance for unrelated session | Deny |
| Tutor reads payment | Deny |
| Tutor changes schedule | Deny |
| Admin changes schedule in allowed branch | Allow |
| Admin changes schedule in unauthorized branch | Deny |
| Owner changes honor scheme | Allow |
| Admin changes honor scheme | Deny |
| Admin runs honor calculation | Allow |
| Tutor runs honor calculation | Deny |
| Super Admin accesses tenant in Phase 2 | Allow |
| Tenant user accesses another tenant | Deny |

---

# 22. Permission-to-Route Mapping

Recommended route boundaries:

```text
/admin/...
```

for Super Admin platform area.

```text
/owner/...
```

for Owner area.

```text
/admin-ops/...
```

or a clearly separated Admin operational namespace if needed.

```text
/tutor/...
```

for Tutor area.

The exact URL naming can be simplified during Laravel implementation, but role-specific areas must remain authorization boundaries.

Recommended practical structure:

```text
/admin        → Super Admin
/owner        → Owner
/operations   → Admin
/tutor        → Tutor
```

Navigation must correspond to the authenticated role.

---

# 23. Navigation vs Permission

Sidebar visibility is derived from role/capability.

Example:

```text
Owner
→ Branch
→ Users
→ Siswa
→ Kelas
→ Jadwal
→ Tutor
→ Pembayaran
→ Honor
→ Laporan
→ Pengaturan
```

Admin:

```text
Siswa
→ Kelas
→ Jadwal
→ Absensi
→ Tutor
→ Pembayaran
→ Honor
→ Reminder
→ Laporan
```

Tutor:

```text
Dashboard
→ Jadwal
→ Kelas
→ Absensi
→ Penilaian
→ Riwayat
→ Profil
```

But backend authorization remains authoritative.

---

# 24. Permission Priority

## P0

### Owner

- Dashboard
- Branch
- Admin Management
- Student Oversight
- Class Oversight
- Tutor Oversight
- Payment Oversight
- Honor Scheme
- Honor Oversight
- Reports
- Tenant Settings

### Admin

- Dashboard
- Student
- Enrollment
- Class
- Tutor
- Tutor Assignment
- Schedule
- Teaching Session
- Attendance
- Tutor Replacement
- Payment
- Parent Reminder
- Honor Calculation
- Reports

### Tutor

- Dashboard
- Schedule
- Assigned Classes
- Teaching Session
- Student Attendance
- Assessment
- Material
- Notes
- Profile

---

# 25. P1

- Advanced Admin permission controls
- More granular Owner/Admin permission configuration
- Advanced financial permissions
- Advanced honor permissions
- More detailed report export permissions

---

# 26. P2

- Custom RBAC
- Permission groups
- Custom role builder
- Fine-grained branch permission policies
- Super Admin platform permission administration
- Advanced Support Mode controls

---

# 27. Final Authorization Model

Cressco MVP authorization is defined as:

```text
                 ┌───────────────┐
                 │ Authenticated │
                 │     User      │
                 └───────┬───────┘
                         ↓
                    ┌─────────┐
                    │  Role   │
                    └────┬────┘
                         ↓
              ┌─────────────────────┐
              │ Tenant Authorization│
              └──────────┬──────────┘
                         ↓
              ┌─────────────────────┐
              │ Scope Authorization │
              └──────────┬──────────┘
                         ↓
            ┌──────────────────────────┐
            │ Resource Authorization  │
            │        (Policy)          │
            └────────────┬─────────────┘
                         ↓
              ┌─────────────────────┐
              │ Business Validation │
              └──────────┬──────────┘
                         ↓
                       Action
```

Core principle:

> **Role menentukan authority, branch/assignment menentukan scope, Policy menentukan resource access, dan Business Rules menentukan apakah action valid.**

---

# 28. Ready for Database Schema

Permission Matrix now establishes the authorization requirements needed by the database.

The schema must support at minimum:

```text
users.role
users.tenant_id
branches.tenant_id
branch_user
students.tenant_id
students.branch_id
enrollments.tenant_id
enrollments.branch_id
classes.tenant_id
classes.branch_id
tutor_assignments.tenant_id
tutor_assignments.branch_id
schedules.tenant_id
schedules.branch_id
teaching_sessions.tenant_id
teaching_sessions.branch_id
payments.tenant_id
payments.branch_id
honor_schemes.tenant_id
honor_assignments.tenant_id
honor_calculations.tenant_id
audit_logs.tenant_id
```

plus the relationships required to derive Tutor teaching scope.

The next artifact can therefore be:

```text
DATABASE-SCHEMA.sql
```

followed by:

```text
Laravel migrations
↓
Models + relationships
↓
Policies / Gates
↓
Seeders
```
