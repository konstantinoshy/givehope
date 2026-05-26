-- DB: donations_platform
-- Import in phpMyAdmin or mysql client.

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS data_processing_log;
DROP TABLE IF EXISTS cookie_consents;
DROP TABLE IF EXISTS gdpr_requests;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS campaign_updates;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS campaigns;
DROP TABLE IF EXISTS needs;
DROP TABLE IF EXISTS organizations;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS messages;
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- ADMINS - Platform administrators
-- =============================================
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- CATEGORIES - Campaign categories
-- =============================================
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  icon VARCHAR(10) NOT NULL DEFAULT '📋',
  description TEXT NULL,
  requires_verification TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- USERS - Individual fundraisers
-- =============================================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(60) NULL,
  id_verified TINYINT(1) NOT NULL DEFAULT 0,
  profile_image VARCHAR(500) NULL,
  -- GDPR Compliance Fields (Άρθρο 7 GDPR)
  privacy_consent TINYINT(1) NOT NULL DEFAULT 0,
  privacy_consent_at DATETIME NULL,
  marketing_consent TINYINT(1) NOT NULL DEFAULT 0,
  marketing_consent_at DATETIME NULL,
  data_processing_consent TINYINT(1) NOT NULL DEFAULT 0,
  data_processing_consent_at DATETIME NULL,
  ip_address VARCHAR(45) NULL,
  last_login DATETIME NULL,
  deleted_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- ORGANIZATIONS - NGOs (existing, enhanced)
-- =============================================
CREATE TABLE organizations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(60) NULL,
  website VARCHAR(255) NULL,
  description TEXT NULL,
  logo_path VARCHAR(255) NULL,
  image_url VARCHAR(500) NULL,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  -- GDPR Compliance Fields
  privacy_consent TINYINT(1) NOT NULL DEFAULT 0,
  privacy_consent_at DATETIME NULL,
  terms_consent TINYINT(1) NOT NULL DEFAULT 0,
  terms_consent_at DATETIME NULL,
  ip_address VARCHAR(45) NULL,
  last_login DATETIME NULL,
  deleted_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- CAMPAIGNS - Unified campaigns table (replaces needs)
-- =============================================
CREATE TABLE campaigns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  
  -- Owner (one of these will be set)
  org_id INT NULL,
  user_id INT NULL,
  
  -- Campaign details
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  story TEXT NULL,
  
  -- Categorization
  category_id INT NOT NULL,
  type ENUM('money','goods','volunteer') NOT NULL DEFAULT 'money',
  
  -- Financial
  target_amount INT NOT NULL DEFAULT 0,
  current_amount INT NOT NULL DEFAULT 0,
  
  -- Media
  image_url VARCHAR(500) NULL,
  
  -- Verification & Status
  status ENUM('draft','pending','approved','rejected','suspended','completed','deleted') NOT NULL DEFAULT 'pending',
  rejection_reason TEXT NULL,
  
  -- Timestamps
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  approved_at DATETIME NULL,
  
  FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DOCUMENTS - Verification documents
-- =============================================
CREATE TABLE documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  file_url VARCHAR(500) NOT NULL,
  type ENUM('medical','identity','invoice','receipt','other') NOT NULL DEFAULT 'other',
  verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- CAMPAIGN_UPDATES - Updates from campaign owner
-- =============================================
CREATE TABLE campaign_updates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  title VARCHAR(180) NOT NULL,
  content TEXT NOT NULL,
  image_url VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DONATIONS
-- =============================================
CREATE TABLE donations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  donor_name VARCHAR(120) NULL,
  donor_email VARCHAR(190) NULL,
  amount INT NOT NULL DEFAULT 0,
  message VARCHAR(280) NULL,
  is_anonymous TINYINT(1) NOT NULL DEFAULT 0,
  -- GDPR Compliance Fields
  privacy_consent TINYINT(1) NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- REPORTS - User reports for suspicious campaigns
-- =============================================
CREATE TABLE reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  reporter_email VARCHAR(190) NOT NULL,
  reason ENUM('scam','fake_info','inappropriate','other') NOT NULL,
  description TEXT NULL,
  status ENUM('new','reviewed','resolved') NOT NULL DEFAULT 'new',
  -- GDPR Compliance Fields
  privacy_consent TINYINT(1) NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- MESSAGES (existing)
-- =============================================
CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT NULL,
  campaign_id INT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(190) NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('new','read') NOT NULL DEFAULT 'new',
  -- GDPR Compliance Fields
  privacy_consent TINYINT(1) NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NULL,
  FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- GDPR_REQUESTS - Αιτήματα δικαιωμάτων υποκειμένων δεδομένων
-- Άρθρα 15, 17, 20 GDPR
-- =============================================
CREATE TABLE gdpr_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('access', 'export', 'delete', 'rectification') NOT NULL,
  requester_type ENUM('user', 'organization', 'donor', 'visitor') NOT NULL,
  requester_id INT NULL,
  requester_email VARCHAR(190) NOT NULL,
  requester_name VARCHAR(120) NULL,
  verification_token VARCHAR(64) NULL,
  verified_at DATETIME NULL,
  status ENUM('pending', 'verified', 'processing', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
  rejection_reason TEXT NULL,
  handled_by INT NULL,
  handled_at DATETIME NULL,
  notes TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  FOREIGN KEY (handled_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- COOKIE_CONSENTS - Συγκατάθεση Cookies (ePrivacy & GDPR)
-- =============================================
CREATE TABLE cookie_consents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  org_id INT NULL,
  session_id VARCHAR(64) NULL,
  essential TINYINT(1) NOT NULL DEFAULT 1,
  analytics TINYINT(1) NOT NULL DEFAULT 0,
  marketing TINYINT(1) NOT NULL DEFAULT 0,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DATA_PROCESSING_LOG - Αρχείο επεξεργασίας δεδομένων (Άρθρο 30 GDPR)
-- =============================================
CREATE TABLE data_processing_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entity_type ENUM('user', 'organization', 'donation', 'message', 'campaign', 'gdpr_request') NOT NULL,
  entity_id INT NOT NULL,
  action ENUM('create', 'read', 'update', 'delete', 'export', 'anonymize') NOT NULL,
  actor_type ENUM('user', 'organization', 'admin', 'system') NOT NULL,
  actor_id INT NULL,
  description TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- INDEXES
-- =============================================
CREATE INDEX idx_campaigns_status ON campaigns(status);
CREATE INDEX idx_campaigns_category ON campaigns(category_id);
CREATE INDEX idx_campaigns_org ON campaigns(org_id);
CREATE INDEX idx_campaigns_user ON campaigns(user_id);
CREATE INDEX idx_donations_campaign ON donations(campaign_id);
CREATE INDEX idx_reports_status ON reports(status);
CREATE INDEX idx_gdpr_requests_status ON gdpr_requests(status);
CREATE INDEX idx_gdpr_requests_email ON gdpr_requests(requester_email);
CREATE INDEX idx_cookie_consents_session ON cookie_consents(session_id);
CREATE INDEX idx_data_processing_entity ON data_processing_log(entity_type, entity_id);

-- =============================================
-- SEED DATA
-- =============================================

-- Admin account (password: password)
INSERT INTO admins (username, email, password_hash) VALUES
('admin', 'admin@platform.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Categories
INSERT INTO categories (name, slug, icon, description, requires_verification) VALUES
('Ιατρικά & Υγεία', 'medical', '', 'Επεμβάσεις, θεραπείες, φάρμακα', 1),
('Εκπαίδευση', 'education', '', 'Σπουδές, υποτροφίες, σχολικά είδη', 1),
('Έκτακτη Ανάγκη', 'emergency', '', 'Φυσικές καταστροφές, ατυχήματα', 1),
('Ζώα', 'animals', '', 'Περίθαλψη, στειρώσεις, καταφύγια', 0),
('Κοινωνική Αλληλεγγύη', 'social', '', 'Τρόφιμα, ρούχα, στέγαση', 0),
('Περιβάλλον', 'environment', '', 'Οικολογικές δράσεις', 0),
('Πολιτισμός & Τέχνη', 'culture', '', 'Καλλιτεχνικά projects', 0),
('Άλλο', 'other', '', 'Διάφορες ανάγκες', 0);

-- Demo Organization (password: password)
INSERT INTO organizations (name, email, password_hash, phone, website, description, image_url, verified) VALUES
('Hope Foundation', 'demo@hope.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+30 210 000 0000', 'https://hope.org', 'Στηρίζουμε οικογένειες και ευάλωτες ομάδες με διαφάνεια και συνέπεια.', 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800&q=80', 1),
('Animal Care Greece', 'animals@care.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+30 231 000 0000', NULL, 'Περίθαλψη και προστασία αδέσποτων ζώων.', 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=800&q=80', 1),
('Παιδικό Χαμόγελο', 'info@paidiko-xamogelo.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+30 210 555 1234', 'https://xamogelo.gr', 'Προστασία παιδιών σε ανάγκη και στήριξη οικογενειών.', 'https://images.unsplash.com/photo-1509099836639-18ba1795216d?w=800&q=80', 1),
('Πράσινη Ελλάδα', 'contact@prasini-ellada.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+30 210 777 8899', 'https://prasini-ellada.org', 'Περιβαλλοντικές δράσεις, αναδασώσεις και οικολογική ευαισθητοποίηση.', 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=800&q=80', 1),
('Γιατροί της Ελπίδας', 'volunteer@doctors-hope.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+30 211 999 0000', 'https://doctors-hope.gr', 'Εθελοντική ιατρική βοήθεια σε ευάλωτες κοινωνικές ομάδες.', 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=800&q=80', 1);

-- Demo User (password: password)
INSERT INTO users (name, email, password_hash, phone, id_verified) VALUES
('Μαρία Παπαδοπούλου', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+30 694 000 0000', 1),
('Γιώργος Νικολάου', 'giorgos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+30 697 000 0000', 0);

-- Demo Campaigns
INSERT INTO campaigns (org_id, user_id, title, description, story, category_id, type, target_amount, current_amount, image_url, status, approved_at) VALUES
-- Organization campaigns
(1, NULL, 'Ιατρική Υποστήριξη για 10 οικογένειες', 'Κάλυψη βασικών ιατρικών εξόδων για οικογένειες σε ανάγκη.', 'Η οικογένειά μας αντιμετωπίζει μια πολύ δύσκολη περίοδο. Ο πατέρας μας, ένας εργαζόμενος που πάντα φρόντιζε την οικογένεια, διαγνώστηκε πρόσφατα με σοβαρή ασθένεια που απαιτεί συνεχή ιατρική παρακολούθηση και φαρμακευτική αγωγή.

Τα έξοδα για τη θεραπεία είναι πολύ μεγάλα και η ασφάλειά μας δεν καλύπτει όλα τα απαραίτητα. Κάθε μήνα χρειαζόμαστε φάρμακα που κοστίζουν εκατοντάδες ευρώ, ενώ οι επισκέψεις σε ειδικούς γιατρούς και οι εξετάσεις προσθέτουν επιπλέον βάρος.

Με τη δική σας βοήθεια, μπορούμε να διασφαλίσουμε ότι ο πατέρας μας θα λάβει την καλύτερη δυνατή φροντίδα. Κάθε δωρεά, μικρή ή μεγάλη, μας φέρνει πιο κοντά στο να ξεπεράσουμε αυτή τη δοκιμασία. Σας ευχαριστούμε από καρδιάς για τη συμπαράστασή σας.', 1, 'money', 3000, 650, 'https://images.unsplash.com/photo-1584515933487-779824d29309?w=800&q=80', 'approved', NOW()),

(1, NULL, 'Συλλογή τροφίμων', 'Χρειαζόμαστε ρύζι, όσπρια, γάλα εβαπορέ.', 'Η ενορία μας βρίσκεται σε μια περιοχή όπου πολλές οικογένειες αντιμετωπίζουν οικονομικές δυσκολίες. Κάθε εβδομάδα, δεκάδες άνθρωποι έρχονται στο κοινωνικό μας παντοπωλείο αναζητώντας βασικά είδη διατροφής.

Οι ανάγκες είναι τεράστιες, ειδικά τους χειμερινούς μήνες. Οικογένειες με παιδιά, ηλικιωμένοι με χαμηλές συντάξεις, άνεργοι που προσπαθούν να ξαναβρούν τα πόδια τους - όλοι τους χρειάζονται τη στήριξή μας.

Με τη δική σας βοήθεια, μπορούμε να γεμίσουμε τα ράφια μας με βασικά τρόφιμα: ρύζι, μακαρόνια, λάδι, όσπρια, γάλα, και κονσέρβες. Κάθε ευρώ που δωρίζετε μετατρέπεται σε ένα πιάτο φαγητό για κάποιον που το χρειάζεται.

Σας ευχαριστούμε που στέκεστε δίπλα στους συνανθρώπους μας που δοκιμάζονται.', 5, 'money', 2500, 780, 'https://images.unsplash.com/photo-1593113598332-cd288d649433?w=800&q=80', 'approved', NOW()),

(2, NULL, 'Κτηνιατρικά έξοδα αδέσποτων', 'Εμβολιασμοί για 30 ζώα.', 'Τα αδέσποτα ζώα στην περιοχή μας χρειάζονται επειγόντως τη βοήθειά σας. Κάθε μέρα βρίσκουμε γατάκια και σκυλάκια εγκαταλελειμμένα, πεινασμένα και τρομαγμένα, να αναζητούν καταφύγιο.

Ο σύλλογός μας φροντίζει αυτή τη στιγμή περισσότερα από 50 ζώα. Τα στειρώνουμε, τα εμβολιάζουμε, τα θεραπεύουμε όταν είναι άρρωστα και τα ετοιμάζουμε για υιοθεσία. Όμως τα έξοδα είναι τεράστια: κτηνιατρικές επισκέψεις, φάρμακα, τροφές, και καθημερινή φροντίδα.

Με τη δωρεά σας, μπορούμε να συνεχίσουμε αυτό το έργο αγάπης. 10 ευρώ αγοράζουν τροφή για μια εβδομάδα. 50 ευρώ πληρώνουν μια στείρωση. 100 ευρώ καλύπτουν ένα πλήρες εμβολιαστικό πρόγραμμα.

Κάθε βοήθεια μετράει. Μαζί μπορούμε να δώσουμε μια δεύτερη ευκαιρία σε αυτές τις αθώες ψυχές.', 4, 'money', 2000, 420, 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=800&q=80', 'approved', NOW()),

-- Νέοι οργανισμοί
(3, NULL, 'Χριστουγεννιάτικα δώρα για 100 παιδιά', 'Φέτος κανένα παιδί χωρίς δώρο! Μαζεύουμε χρήματα για να αγοράσουμε παιχνίδια και ρούχα.', 'Τα Χριστούγεννα πλησιάζουν, αλλά για πολλά παιδιά στην περιοχή μας, οι γιορτές δεν σημαίνουν δώρα και χαρά. Πολλές οικογένειες δυσκολεύονται να βάλουν φαγητό στο τραπέζι, πόσο μάλλον να αγοράσουν παιχνίδια.

Ο οργανισμός μας έχει αναγνωρίσει 100 παιδιά ηλικίας 3-12 ετών που χρειάζονται τη βοήθειά μας. Θέλουμε κάθε ένα από αυτά τα παιδιά να ξυπνήσει το πρωί της Πρωτοχρονιάς με ένα δώρο να τους περιμένει - ένα παιχνίδι, ρούχα, ή σχολικά είδη.

Με 30 ευρώ μπορούμε να αγοράσουμε ένα πλήρες πακέτο δώρων για ένα παιδί. Η χαρά στα μάτια τους όταν ανοίγουν το δώρο τους είναι ανεκτίμητη. Για πολλά από αυτά τα παιδιά, αυτό μπορεί να είναι το μόνο δώρο που θα λάβουν φέτος.

Βοηθήστε μας να φέρουμε τη μαγεία των Χριστουγέννων σε κάθε παιδί. Κάθε δωρεά, μικρή ή μεγάλη, κάνει τη διαφορά.', 5, 'money', 5000, 2350, 'https://images.unsplash.com/photo-1512909006721-3d6018887383?w=800&q=80', 'approved', NOW()),

(4, NULL, 'Αναδάσωση στην Εύβοια', 'Φυτεύουμε 10.000 δέντρα στις καμένες περιοχές της Εύβοιας.', 'Η καταστροφική πυρκαγιά του 2021 στην Εύβοια άφησε πίσω της χιλιάδες καμένα στρέμματα δάσους. Τα πεύκα που κάλυπταν τα βουνά για δεκαετίες εξαφανίστηκαν μέσα σε λίγες ημέρες, αφήνοντας πίσω τους ένα τοπίο γεμάτο στάχτες.

Ο οργανισμός μας έχει αναλάβει τη δύσκολη αποστολή της αναδάσωσης. Δουλεύουμε με τοπικούς φορείς, εθελοντές και ειδικούς δασολόγους για να επαναφέρουμε το δάσος στην αρχική του κατάσταση.

Κάθε δέντρο που φυτεύουμε κοστίζει περίπου 5 ευρώ, συμπεριλαμβανομένου του δενδρυλλίου, της φύτευσης και της αρχικής φροντίδας. Με 50 ευρώ φυτεύουμε 10 δέντρα. Με 100 ευρώ αναδασώνουμε ένα μικρό τμήμα του βουνού.

Η φύση χρειάζεται χρόνο για να επουλώσει τις πληγές της, αλλά μαζί μπορούμε να επιταχύνουμε αυτή τη διαδικασία. Βοηθήστε μας να επαναφέρουμε το πράσινο στην Εύβοια.', 6, 'money', 8000, 3750, 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800&q=80', 'approved', NOW()),

-- Individual campaigns
(NULL, 1, 'Βοήθεια για επέμβαση καρδιάς', 'Χρειάζομαι επειγόντως επέμβαση καρδιάς και η οικογένειά μου δεν μπορεί να καλύψει τα έξοδα.', 'Είμαι η Μαρία, 42 ετών, μητέρα δύο παιδιών. Πριν τρεις μήνες, η ζωή μου άλλαξε δραματικά όταν διαγνώστηκα με σοβαρό καρδιακό πρόβλημα. Οι γιατροί είπαν ότι χρειάζομαι επείγουσα επέμβαση για να μπορέσω να συνεχίσω να ζω.

Το κόστος της επέμβασης ανέρχεται σε 15.000 ευρώ. Η ασφάλειά μου καλύπτει μόνο ένα μικρό μέρος, και η οικογένειά μου δεν έχει τη δυνατότητα να καλύψει το υπόλοιπο ποσό. Δουλεύαμε σκληρά όλη μας τη ζωή, αλλά αυτό το απρόσμενο χτύπημα μας βρήκε απροετοίμαστους.

Τα παιδιά μου, ο Γιάννης 12 ετών και η Ελένη 9 ετών, με χρειάζονται. Θέλω να είμαι εκεί για τα πρώτα τους βήματα στην εφηβεία, για τις σχολικές τους επιτυχίες, για τα όνειρά τους. Με τη δική σας βοήθεια, μπορώ να πάρω μια δεύτερη ευκαιρία στη ζωή.

Κάθε ευρώ που δωρίζετε με φέρνει πιο κοντά στο χειρουργείο που θα μου επιστρέψει την υγεία μου. Σας ευχαριστώ από τα βάθη της καρδιάς μου.', 1, 'money', 15000, 3200, 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&q=80', 'approved', NOW()),

(NULL, 2, 'Σπουδές στο εξωτερικό', 'Κέρδισα υποτροφία αλλά χρειάζομαι βοήθεια για έξοδα διαβίωσης.', 'Από μικρός ονειρευόμουν να σπουδάσω σε ένα κορυφαίο πανεπιστήμιο του εξωτερικού. Με σκληρή δουλειά και αφοσίωση, κατάφερα να κερδίσω υποτροφία για μεταπτυχιακές σπουδές στην Επιστήμη Υπολογιστών σε ένα από τα καλύτερα πανεπιστήμια του κόσμου.

Όμως η υποτροφία δεν καλύπτει όλα τα έξοδα. Χρειάζομαι βοήθεια για τα έξοδα διαβίωσης - στέγαση, διατροφή, μεταφορικά, και σχολικά βιβλία. Η οικογένειά μου έχει κάνει ό,τι μπορεί, αλλά δεν τους αντέχει οικονομικά.

Αυτή η ευκαιρία δεν είναι μόνο για μένα. Θέλω να γυρίσω πίσω και να χρησιμοποιήσω τις γνώσεις μου για να συνεισφέρω στην ανάπτυξη της τεχνολογίας στην Ελλάδα. Ονειρεύομαι να δημιουργήσω θέσεις εργασίας και να εμπνεύσω άλλους νέους να κυνηγήσουν τα όνειρά τους.

Με τη βοήθειά σας, αυτό το όνειρο μπορεί να γίνει πραγματικότητα. Κάθε δωρεά μετράει και θα σας είμαι αιώνια ευγνώμων.', 2, 'money', 8000, 1500, 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=800&q=80', 'approved', NOW()),

-- Campaign 8: Γιατροί της Ελπίδας - Εθελοντικό ιατρείο
(5, NULL, 'Εθελοντικό ιατρείο για νησιωτικές κοινότητες', 'Χρειαζόμαστε εξοπλισμό και φάρμακα για δωρεάν ιατρικές εξετάσεις σε απομακρυσμένα νησιά.', 'Πολλά μικρά νησιά του Αιγαίου δεν διαθέτουν μόνιμο γιατρό. Οι κάτοικοι πρέπει να ταξιδέψουν ώρες με πλοίο για μια απλή εξέταση, κάτι που για πολλούς ηλικιωμένους είναι αδύνατο.

Η ομάδα μας, αποτελούμενη από εθελοντές γιατρούς και νοσηλευτές, οργανώνει ιατρικές αποστολές σε αυτά τα νησιά. Κάθε αποστολή διαρκεί μια εβδομάδα και εξυπηρετεί δεκάδες ασθενείς.

Χρειαζόμαστε φορητό ιατρικό εξοπλισμό, βασικά φάρμακα και κάλυψη μεταφορικών εξόδων. Με 500 ευρώ καλύπτουμε τα φάρμακα μιας αποστολής. Με 1.000 ευρώ μπορούμε να εξετάσουμε ένα ολόκληρο χωριό.

Κάθε συνεισφορά σημαίνει ένας ακόμη ηλικιωμένος δεν θα μείνει χωρίς φροντίδα.', 1, 'money', 6000, 1850, 'https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?w=800&q=80', 'approved', NOW()),

-- Campaign 9: Πολιτισμός & Τέχνη
(NULL, 1, 'Θεατρική παράσταση για κοινωνικό σκοπό', 'Ανεβάζουμε θεατρική παράσταση με έσοδα υπέρ παιδιών με αναπηρία. Χρειαζόμαστε βοήθεια για τα έξοδα παραγωγής.', 'Είμαστε μια ομάδα ερασιτεχνών ηθοποιών που αγαπάμε το θέατρο και πιστεύουμε στη δύναμή του να αλλάζει ζωές. Φέτος αποφασίσαμε να ανεβάσουμε μια παράσταση με σκοπό να συγκεντρώσουμε χρήματα για παιδιά με κινητικές αναπηρίες.

Η παράσταση «Το Φως στο Τούνελ» είναι ένα πρωτότυπο έργο που μιλάει για την ελπίδα, τη δύναμη της κοινότητας και την αποδοχή. Θα παρουσιαστεί σε τρία θέατρα της Αθήνας και τα εισιτήρια θα διατεθούν σε χαμηλή τιμή για να μπορέσουν όλοι να παρακολουθήσουν.

Χρειαζόμαστε βοήθεια για τα σκηνικά, τα κοστούμια, τον φωτισμό και την ενοικίαση χώρων. Κάθε ευρώ που συγκεντρώνουμε πέραν των εξόδων θα δοθεί απευθείας σε οικογένειες παιδιών με αναπηρία.

Ελάτε να δημιουργήσουμε μαζί κάτι όμορφο που θα κάνει τη διαφορά!', 7, 'money', 4000, 980, 'https://images.unsplash.com/photo-1507924538820-ede94a04019d?w=800&q=80', 'approved', NOW()),

-- Campaign 10: Έκτακτη ανάγκη - Πλημμύρα
(NULL, 2, 'Αποκατάσταση σπιτιού μετά από πλημμύρα', 'Η πλημμύρα κατέστρεψε το σπίτι μας. Χρειαζόμαστε βοήθεια για να ξαναχτίσουμε τη ζωή μας.', 'Στις 5 Σεπτεμβρίου, μια ξαφνική πλημμύρα έπληξε την περιοχή μας στη Θεσσαλία. Μέσα σε λίγες ώρες, το νερό μπήκε στο σπίτι μας και κατέστρεψε τα πάντα — έπιπλα, ηλεκτρικές συσκευές, ρούχα, αναμνήσεις μιας ολόκληρης ζωής.

Η οικογένειά μου — εγώ, η γυναίκα μου και τα δύο μας παιδιά — μείναμε κυριολεκτικά χωρίς τίποτα. Φιλοξενούμαστε προσωρινά σε συγγενείς, αλλά η κατάσταση δεν μπορεί να συνεχιστεί για πολύ.

Τα χρήματα θα χρησιμοποιηθούν για: επισκευή τοίχων και δαπέδων, αντικατάσταση ηλεκτρολογικών εγκαταστάσεων, αγορά βασικών επίπλων και συσκευών, και αγορά σχολικών ειδών για τα παιδιά.

Ξέρω ότι πολλοί άνθρωποι αντιμετωπίζουν παρόμοια προβλήματα, αλλά κάθε βοήθεια — μικρή ή μεγάλη — μας δίνει ελπίδα ότι θα ξαναστηθούμε στα πόδια μας. Σας ευχαριστώ.', 3, 'money', 12000, 4200, 'https://images.unsplash.com/photo-1547683905-f686c993aae5?w=800&q=80', 'approved', NOW());

-- Demo donations (amounts match current_amount in campaigns)
INSERT INTO donations (campaign_id, donor_name, donor_email, amount, message) VALUES
-- Campaign 1: Ιατρική Υποστήριξη (total: 650€)
(1, 'Νίκος Κ.', 'nikos@email.com', 200, 'Καλή δύναμη!'),
(1, 'Σοφία Π.', 'sofia@email.com', 150, 'Στηρίζουμε!'),
(1, 'Anonymous', NULL, 300, NULL),
-- Campaign 2: Συλλογή τροφίμων (total: 780€)
(2, 'Αθηνά Β.', 'athina@email.com', 280, 'Για τις οικογένειες!'),
(2, 'Anonymous', NULL, 500, 'Καλή δύναμη στο έργο σας'),
-- Campaign 3: Κτηνιατρικά (total: 420€)
(3, 'Μαρία Λ.', 'maria.l@email.com', 120, 'Για τα ζωάκια!'),
(3, 'Γιάννης Α.', 'giannis@email.com', 200, NULL),
(3, 'Anonymous', NULL, 100, 'Μπράβο για τη δουλειά σας'),
-- Campaign 4: Επέμβαση καρδιάς (total: 3200€)
(4, 'Ελένη Μ.', 'eleni@email.com', 500, 'Περαστικά! Σύντομα θα είσαι καλά.'),
(4, 'Κώστας Δ.', 'kostas.d@email.com', 1000, 'Δύναμη και υπομονή'),
(4, 'Anonymous', NULL, 700, 'Από καρδιάς'),
(4, 'Αντώνης Ρ.', 'antonis@email.com', 500, NULL),
(4, 'Δήμητρα Κ.', 'dimitra@email.com', 500, 'Θα τα καταφέρεις!'),
-- Campaign 5: Χριστουγεννιάτικα δώρα (total: 2350€)
(5, 'Θεοδώρα Μ.', 'theodora@email.com', 500, 'Για τα παιδάκια! 🎄'),
(5, 'Σταύρος Κ.', 'stavros@email.com', 350, 'Καλές γιορτές!'),
(5, 'Anonymous', NULL, 1000, NULL),
(5, 'Εταιρεία ABC', 'info@abc.gr', 500, 'Εταιρική δωρεά'),
-- Campaign 6: Αναδάσωση (total: 3750€)
(6, 'Νίκη Π.', 'niki@email.com', 750, 'Για το περιβάλλον! 🌱'),
(6, 'Δημήτρης Α.', 'dimitris@email.com', 500, '150 δέντρα!'),
(6, 'Anonymous', NULL, 1500, 'Από μια ομάδα φίλων'),
(6, 'Άννα Λ.', 'anna.l@email.com', 1000, 'Για τα παιδιά μας'),
-- Campaign 7: Σπουδές (total: 1500€)
(7, 'Κώστας Π.', 'kostas@email.com', 500, 'Καλή επιτυχία στις σπουδές!'),
(7, 'Ελένη Δ.', 'eleni.d@email.com', 300, 'Μπράβο για την υποτροφία!'),
(7, 'Anonymous', NULL, 400, NULL),
(7, 'Μαρία Κ.', 'maria.k@email.com', 300, 'Πίστευε στα όνειρά σου!'),
-- Campaign 8: Εθελοντικό ιατρείο (total: 1850€)
(8, 'Χρήστος Μ.', 'christos.m@email.com', 500, 'Μπράβο για την πρωτοβουλία!'),
(8, 'Φωτεινή Α.', 'fotini@email.com', 350, 'Για τους ηλικιωμένους στα νησιά'),
(8, 'Anonymous', NULL, 1000, 'Από ιατρικό σύλλογο'),
-- Campaign 9: Θεατρική παράσταση (total: 980€)
(9, 'Παναγιώτης Λ.', 'panagiotis@email.com', 200, 'Καλή επιτυχία!'),
(9, 'Κατερίνα Σ.', 'katerina@email.com', 280, 'Θαυμάσια πρωτοβουλία 👏'),
(9, 'Anonymous', NULL, 500, NULL),
-- Campaign 10: Πλημμύρα (total: 4200€)
(10, 'Βασίλης Γ.', 'vasilis.g@email.com', 1000, 'Κουράγιο! Θα τα καταφέρετε.'),
(10, 'Anonymous', NULL, 1500, 'Από συλλόγους γονέων'),
(10, 'Ειρήνη Κ.', 'eirini@email.com', 700, 'Δύναμη στην οικογένειά σας'),
(10, 'Σπύρος Ν.', 'spyros@email.com', 1000, NULL);
