-- ============================================================
-- Run this ONCE in your dbterelearn database.
-- Adds the 3 columns needed for OTP to tbluser.
-- ============================================================

USE dbterelearn;

ALTER TABLE tbluser
  ADD COLUMN otp_hash     VARCHAR(255) NULL DEFAULT NULL  COMMENT 'Bcrypt-hashed OTP',
  ADD COLUMN otp_expiry   DATETIME     NULL DEFAULT NULL  COMMENT 'OTP expiry timestamp',
  ADD COLUMN otp_sent_at  DATETIME     NULL DEFAULT NULL  COMMENT 'Last OTP send time (for cooldown)';

-- Also add failed_attempts column to track per-account brute force
ALTER TABLE tbluser
  ADD COLUMN failed_attempts TINYINT(3) NOT NULL DEFAULT 0 COMMENT 'Consecutive failed login count';

-- Verify
DESCRIBE tbluser;