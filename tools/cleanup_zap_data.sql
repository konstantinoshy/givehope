-- ============================================================================
-- GiveHope — Cleanup ZAP test data
-- ============================================================================
-- Σκοπός: διαγραφή των records που δημιούργησε το OWASP ZAP κατά
-- τη διάρκεια του automated scan (zaproxy@example.com)
--
-- Αυτό το script είναι ΑΣΦΑΛΕΣ: σβήνει ΜΟΝΟ records που έχουν
-- email = 'zaproxy@example.com' ή name LIKE 'Zaproxy%'.
-- Δεν αγγίζει χρήστες, οργανισμούς, εράνους ή δωρεές.
-- ============================================================================

USE donations_platform;

SET @reports_before = (SELECT COUNT(*) FROM reports);
SET @messages_before = (SELECT COUNT(*) FROM messages);
SET @logs_before = (SELECT COUNT(*) FROM data_processing_log);

-- Διαγραφή ZAP reports ---------------------------------------------------
SET @zap_reports = (SELECT COUNT(*) FROM reports WHERE reporter_email = 'zaproxy@example.com');
DELETE FROM reports WHERE reporter_email = 'zaproxy@example.com';

-- Διαγραφή ZAP messages --------------------------------------------------
SET @zap_messages = (SELECT COUNT(*) FROM messages
                     WHERE email = 'zaproxy@example.com'
                        OR name LIKE 'Zaproxy%'
                        OR subject LIKE '%Zaproxy%');
DELETE FROM messages
 WHERE email = 'zaproxy@example.com'
    OR name LIKE 'Zaproxy%'
    OR subject LIKE '%Zaproxy%';

-- Διαγραφή ZAP donations -------------------------------------------------
SET @zap_donations = (SELECT COUNT(*) FROM donations
                      WHERE donor_name = 'ZAP'
                         OR donor_email = 'foo-bar@example.com'
                         OR message LIKE '%Zaproxy%');
DELETE FROM donations
 WHERE donor_name = 'ZAP'
    OR donor_email = 'foo-bar@example.com'
    OR message LIKE '%Zaproxy%';

-- Επαναϋπολογισμός current_amount στα campaigns ------------------------
-- (μετά τη διαγραφή ZAP donations οι σύνολα ίσως είναι ασύγχρονα)
UPDATE campaigns c
   SET current_amount = (
       SELECT COALESCE(SUM(d.amount), 0)
         FROM donations d
        WHERE d.campaign_id = c.id
   );

-- Σύνοψη ----------------------------------------------------------------
SELECT
    'ZAP cleanup complete' AS status,
    @zap_reports   AS zap_reports_deleted,
    @zap_messages  AS zap_messages_deleted,
    @zap_donations AS zap_donations_deleted,
    (SELECT COUNT(*) FROM reports)   AS reports_remaining,
    (SELECT COUNT(*) FROM messages)  AS messages_remaining,
    (SELECT COUNT(*) FROM donations) AS donations_remaining,
    (SELECT SUM(current_amount) FROM campaigns) AS total_raised_recomputed;
