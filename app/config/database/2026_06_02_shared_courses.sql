ALTER TABLE `tblsubjectpreset`
  ADD COLUMN IF NOT EXISTS `owner_course_id` char(36) DEFAULT NULL AFTER `course_id`,
  ADD COLUMN IF NOT EXISTS `share_scope` enum('department_only','selected_programs','all_programs') NOT NULL DEFAULT 'department_only' AFTER `year_level`,
  ADD COLUMN IF NOT EXISTS `allow_cross_program_adoption` tinyint(1) NOT NULL DEFAULT 1 AFTER `share_scope`,
  ADD KEY IF NOT EXISTS `idx_owner_course_id` (`owner_course_id`),
  ADD KEY IF NOT EXISTS `idx_share_scope` (`share_scope`);

UPDATE `tblsubjectpreset`
SET `owner_course_id` = `course_id`
WHERE `owner_course_id` IS NULL OR `owner_course_id` = '';

CREATE TABLE IF NOT EXISTS `tblsubjectpreset_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `preset_id` int(11) NOT NULL,
  `program_id` char(36) NOT NULL,
  `added_by` char(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_preset_program` (`preset_id`,`program_id`),
  KEY `idx_program_id` (`program_id`),
  CONSTRAINT `fk_spp_preset` FOREIGN KEY (`preset_id`) REFERENCES `tblsubjectpreset` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_spp_program` FOREIGN KEY (`program_id`) REFERENCES `tblcourse` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `tblsubjectpreset_programs` (`preset_id`, `program_id`, `added_by`)
SELECT `id`, `course_id`, `set_by`
FROM `tblsubjectpreset`
WHERE `course_id` IS NOT NULL AND `course_id` <> '';
