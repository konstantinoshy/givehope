-- Extend audit log for GDPR web-form submissions (run once on existing DBs)
ALTER TABLE data_processing_log
  MODIFY entity_type ENUM('user', 'organization', 'donation', 'message', 'campaign', 'gdpr_request') NOT NULL;
