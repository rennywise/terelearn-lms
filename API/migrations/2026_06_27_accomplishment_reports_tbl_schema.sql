SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS accomplishment_reports;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE accomplishment_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id CHAR(36) NOT NULL,
  subject_id CHAR(36) NULL,
  section_label VARCHAR(50) NULL,
  faculty_id CHAR(36) NOT NULL,
  session_id CHAR(36) NOT NULL,
  academic_week VARCHAR(10) NULL,
  units INT NULL,
  date_covered VARCHAR(80) NULL,
  time_conducted VARCHAR(50) NULL,
  duration VARCHAR(30) NULL,
  topics_covered TEXT NULL,
  sync_activities TEXT NULL,
  async_activities TEXT NULL,
  lab_activities TEXT NULL,
  faculty_signature VARCHAR(120) NULL,
  dean_name VARCHAR(100) NULL,
  date_submitted DATE NULL,
  hrd_received_date DATE NULL,
  status ENUM('draft','submitted') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_accomplishment_session (session_id),
  KEY idx_accomplishment_class (class_id),
  KEY idx_accomplishment_subject (subject_id),
  KEY idx_accomplishment_faculty (faculty_id),
  CONSTRAINT fk_accomplishment_class FOREIGN KEY (class_id) REFERENCES tblclass(id) ON DELETE CASCADE,
  CONSTRAINT fk_accomplishment_subject FOREIGN KEY (subject_id) REFERENCES tblsubject(id) ON DELETE SET NULL,
  CONSTRAINT fk_accomplishment_faculty FOREIGN KEY (faculty_id) REFERENCES tblfaculty(id) ON DELETE CASCADE,
  CONSTRAINT fk_accomplishment_session FOREIGN KEY (session_id) REFERENCES tblattendance(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
