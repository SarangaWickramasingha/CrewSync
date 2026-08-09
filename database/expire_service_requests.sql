-- ============================================================
--  Auto-expire pending service requests after 72 hours
--  Requires the MySQL Event Scheduler to be ON:
--    SET GLOBAL event_scheduler = ON;
-- ============================================================

CREATE EVENT IF NOT EXISTS expire_service_requests
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
ON COMPLETION PRESERVE
DO
  UPDATE service_requests
  SET request_status = 'expired'
  WHERE request_status = 'pending'
    AND expires_at IS NOT NULL
    AND expires_at <= NOW();
