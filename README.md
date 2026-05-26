# GiveHope — Πλατφόρμα Δωρεών & Crowdfunding

Ολοκληρωμένη διαδικτυακή πλατφόρμα δωρεών και crowdfunding, σχεδιασμένη για μη κερδοσκοπικούς οργανισμούς και ιδιώτες που θέλουν να συγκεντρώσουν πόρους για κοινωνικούς σκοπούς. Αναπτύχθηκε ως πτυχιακή εργασία με PHP, MySQL και σύγχρονο responsive design.

> **Σημείωση:** Δεν γίνεται πραγματική ηλεκτρονική πληρωμή. Οι δωρεές καταχωρούνται ως «υποσχέσεις» στη βάση δεδομένων. Μπορεί να επεκταθεί εύκολα με Stripe/PayPal.

---

## Χαρακτηριστικά

### Δημόσια Ιστοσελίδα
- **Αρχική σελίδα** με featured καμπάνιες και στατιστικά πλατφόρμας
- **Εξερεύνηση καμπανιών** με φίλτρα ανά κατηγορία (Ιατρικά, Εκπαίδευση, Έκτακτη Ανάγκη, Ζώα κ.ά.)
- **Σελίδα καμπάνιας** με ιστορία, πρόοδο, ενημερώσεις και λίστα δωρητών
- **Φόρμα δωρεάς** με δυνατότητα ανώνυμης δωρεάς
- **Δημιουργία καμπάνιας** από ιδιώτες ή οργανισμούς
- **Αναφορά ύποπτων καμπανιών**
- **Φόρμα επικοινωνίας**
- **GiveHope Giving Guarantee** — σελίδα εγγύησης αξιοπιστίας

### Εγγραφή & Σύνδεση
- Εγγραφή ως **οργανισμός** ή **ιδιώτης**
- Σύνδεση με email/password (bcrypt hashing)
- CSRF protection σε όλες τις φόρμες

### Dashboard Χρήστη/Οργανισμού
- Επισκόπηση στατιστικών (συνολικές δωρεές, ενεργές καμπάνιες κ.λπ.)
- CRUD καμπανιών (δημιουργία, επεξεργασία, διαγραφή)
- Προβολή δωρεών ανά καμπάνια
- Analytics με γραφήματα
- Ενημέρωση προφίλ
- Διαχείριση προσωπικών δεδομένων (εξαγωγή, διαγραφή — GDPR)

### Admin Panel
- Dashboard με συνολικά στατιστικά πλατφόρμας
- Διαχείριση χρηστών & οργανισμών
- Έγκριση/απόρριψη καμπανιών (review workflow)
- Διαχείριση αναφορών (reports)
- Analytics πλατφόρμας

### Συμμόρφωση GDPR
- Πολιτική Απορρήτου & Όροι Χρήσης
- Πολιτική Cookies με banner συγκατάθεσης
- Αίτημα πρόσβασης, εξαγωγής, διαγραφής & διόρθωσης δεδομένων
- Καταγραφή συγκατάθεσης (consent logging)
- Αρχείο επεξεργασίας δεδομένων (Άρθρο 30)

---

## Τεχνολογίες

| Τεχνολογία | Χρήση |
|---|---|
| **PHP 8.x** | Backend logic |
| **MySQL / MariaDB** | Βάση δεδομένων |
| **HTML5 / CSS3 / JavaScript** | Frontend |
| **Apache** | Web server (XAMPP) |

---

## Απαιτήσεις

- PHP 8.x
- MySQL / MariaDB
- Apache (π.χ. XAMPP, Laragon, WAMP)

---

## Εγκατάσταση

### 1. Λήψη κώδικα

**Από GitHub (clone):**
```bash
git clone https://github.com/konstantinoshy/givehope.git
```
Τοποθέτησε τον φάκελο μέσα στο `htdocs` του XAMPP, π.χ.:
```
C:\xampp\htdocs\givehope
```

**Ή** αντιγραφή του project σε οποιονδήποτε φάκελο στο `htdocs`.

> Το όνομα του φακέλου (π.χ. `givehope` ή `donations-platform`) πρέπει να ταιριάζει με το `BASE_URL` στο βήμα 3.

### 2. Βάση δεδομένων

1. Άνοιξε **phpMyAdmin** (`http://localhost/phpmyadmin`)
2. Δημιούργησε κενή βάση: `donations_platform`
3. Import το αρχείο **`sql/schema.sql`** (περιέχει πίνακες + demo data)

> Το `sql/migrations/` είναι μόνο για ενημέρωση **υπάρχουσας** βάσης. Σε νέα εγκατάσταση αρκεί το `schema.sql`.

### 3. Ρυθμίσεις (`config.php`)

Το `includes/config.php` **δεν υπάρχει στο repo** (τοπικές ρυθμίσεις). Δημιούργησέ το:

**Windows (PowerShell):**
```powershell
copy includes\config.example.php includes\config.php
```

**Linux / macOS:**
```bash
cp includes/config.example.php includes/config.php
```

Άνοιξε το `includes/config.php` και έλεγξε:

| Ρύθμιση | Προεπιλογή (XAMPP) | Σημείωση |
|---|---|---|
| `DB_HOST` | `127.0.0.1` | — |
| `DB_NAME` | `donations_platform` | Ίδιο όνομα με τη βάση |
| `DB_USER` | `root` | — |
| `DB_PASS` | `''` (κενό) | Default XAMPP |
| `BASE_URL` | `/givehope` | **Πρέπει να ταιριάζει με το όνομα φακέλου** |

**Παράδειγμα:** Αν ο φάκελος λέγεται `donations-platform`, άλλαξε:
```php
define('BASE_URL', '/donations-platform');
```

### 4. Εκκίνηση

1. XAMPP → **Start** Apache + MySQL
2. Άνοιξε browser:
   ```
   http://localhost/givehope/
   ```
   (ή `http://localhost/<όνομα-φακέλου>/`)

**Admin panel:** `http://localhost/givehope/admin/login.php`

---

## Demo Λογαριασμοί

Μετά το import του `schema.sql`, υπάρχουν έτοιμα seed data (οργανισμοί, χρήστες, καμπάνιες, δωρεές).

| Ρόλος | Email | Password | Σύνδεση |
|---|---|---|---|
| **Admin** | `admin@platform.gr` | `password` | `/admin/login.php` |
| **Οργανισμός** (Hope Foundation) | `demo@hope.org` | `password` | `/login.php?type=org` |
| **Χρήστης** (Μαρία Παπαδοπούλου) | `maria@example.com` | `password` | `/login.php?type=user` |

---

## Δομή Project

```
donations-platform/
│
├── index.php                 # Αρχική σελίδα
├── explore.php               # Εξερεύνηση καμπανιών
├── campaign.php              # Προβολή μεμονωμένης καμπάνιας
├── campaign-create.php       # Δημιουργία νέας καμπάνιας
├── campaign-edit.php         # Επεξεργασία καμπάνιας
├── campaign-delete.php       # Διαγραφή/απενεργοποίηση καμπάνιας χρήστη
├── donate.php                # Φόρμα δωρεάς
├── register.php              # Εγγραφή χρήστη/οργανισμού
├── login.php / logout.php    # Σύνδεση / Αποσύνδεση
├── contact.php               # Φόρμα επικοινωνίας
├── about.php                 # Σελίδα πληροφοριών σχετικά με την πλατφόρμα
├── how-it-works.php          # Αναλυτικός οδηγός λειτουργίας για δωρητές & δημιουργούς
├── report.php                # Αναφορά καμπάνιας
├── my-campaigns.php          # Οι καμπάνιες μου
├── giving-guarantee.php      # Σελίδα εγγύησης
├── privacy.php               # Πολιτική Απορρήτου
├── terms.php                 # Όροι Χρήσης
├── cookies.php               # Πολιτική Cookies
├── gdpr-request.php          # Αίτημα δικαιωμάτων GDPR
│
├── dashboard/                # Dashboard χρήστη/οργανισμού
│   ├── index.php             #   Επισκόπηση
│   ├── campaign-new.php      #   Νέα καμπάνια
│   ├── campaign-edit.php     #   Επεξεργασία καμπάνιας
│   ├── campaign-delete.php   #   Διαγραφή καμπάνιας
│   ├── donations.php         #   Δωρεές
│   ├── analytics.php         #   Στατιστικά
│   ├── profile.php           #   Προφίλ
│   └── my-data.php           #   Τα δεδομένα μου (GDPR)
│
├── admin/                    # Admin Panel
│   ├── index.php             #   Dashboard
│   ├── login.php / logout.php
│   ├── users.php             #   Διαχείριση χρηστών
│   ├── campaigns.php         #   Διαχείριση καμπανιών
│   ├── campaign-review.php   #   Έγκριση/Απόρριψη
│   ├── reports.php           #   Αναφορές
│   ├── analytics.php         #   Analytics
│   ├── messages.php          #   Διαχείριση μηνυμάτων επικοινωνίας
│   ├── sidebar.php           #   Πλευρικό μενού πλοήγησης admin panel
│   └── includes/             #   Κοινά αρχεία admin panel
│       ├── header.php        #     Κεφαλίδα admin panel
│       └── footer.php        #     Υποσέλιδο admin panel
│
├── includes/                 # Κοινά αρχεία
│   ├── config.example.php    #   Πρότυπο ρυθμίσεων (αντιγραφή → config.php)
│   ├── config.php            #   Τοπικές ρυθμίσεις (δημιουργείται τοπικά, όχι στο GitHub)
│   ├── db.php                #   Σύνδεση βάσης (PDO)
│   ├── auth.php              #   Authentication & Authorization
│   ├── csrf.php              #   CSRF token protection
│   ├── functions.php         #   Helper functions
│   ├── header.php            #   Header / Navbar
│   ├── footer.php            #   Footer
│   └── partials/             #   Επαναχρησιμοποιήσιμα μέρη κώδικα
│       └── campaign-card.php #     Κάρτα εμφάνισης καμπάνιας
│
├── public/                   # Στατικά αρχεία
│   ├── css/style.css         #   Κύριο stylesheet
│   ├── js/app.js             #   JavaScript εφαρμογής
│   ├── images/               #   Εικόνες
│   └── uploads/              #   Ανεβασμένα αρχεία χρηστών
│
└── sql/
    ├── schema.sql            # Σχήμα βάσης & seed data
    └── migrations/           # SQL migrations για ενημέρωση της βάσης
        └── 001_data_processing_gdpr_request.sql # SQL migration για GDPR
```

---

## Βάση Δεδομένων

Η βάση `donations_platform` περιλαμβάνει 13 πίνακες:

| Πίνακας | Περιγραφή |
|---|---|
| `admins` | Διαχειριστές πλατφόρμας |
| `users` | Εγγεγραμμένοι ιδιώτες |
| `organizations` | Εγγεγραμμένοι οργανισμοί |
| `categories` | Κατηγορίες καμπανιών (8 προεπιλεγμένες) |
| `campaigns` | Καμπάνιες (τύποι: money, goods, volunteer) |
| `donations` | Εγγραφές δωρεών |
| `documents` | Έγγραφα επαλήθευσης καμπανιών |
| `campaign_updates` | Ενημερώσεις καμπανιών |
| `reports` | Αναφορές ύποπτων καμπανιών |
| `messages` | Μηνύματα επικοινωνίας |
| `gdpr_requests` | Αιτήματα δικαιωμάτων GDPR |
| `cookie_consents` | Συγκαταθέσεις cookies |
| `data_processing_log` | Αρχείο επεξεργασίας δεδομένων (Άρθρο 30) |

---

## Κατηγορίες Καμπανιών

| Κατηγορία | Απαιτεί Επαλήθευση |
|---|---|
| Ιατρικά & Υγεία | Ναι |
| Εκπαίδευση | Ναι |
| Έκτακτη Ανάγκη | Ναι |
| Ζώα | Όχι |
| Κοινωνική Αλληλεγγύη | Όχι |
| Περιβάλλον | Όχι |
| Πολιτισμός & Τέχνη | Όχι |
| Άλλο | Όχι |

---

## Ασφάλεια

- Password hashing με **bcrypt**
- **CSRF tokens** σε κάθε φόρμα
- **Prepared statements** (PDO) για προστασία από SQL Injection
- **Session-based** authentication
- Ξεχωριστό σύστημα login για admin panel
