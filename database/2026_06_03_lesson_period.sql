ALTER TABLE `tblpost`
  ADD COLUMN IF NOT EXISTS `lesson_period` enum('prelim','midterm','finals') DEFAULT NULL AFTER `topic`,
  ADD KEY IF NOT EXISTS `idx_lesson_period` (`lesson_period`);
