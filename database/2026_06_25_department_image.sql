ALTER TABLE `tbldepartment`
  ADD COLUMN IF NOT EXISTS `dept_image` varchar(255) DEFAULT NULL AFTER `description`;
