-- Update movie table to support decimal views
ALTER TABLE `movie` MODIFY COLUMN `views` DECIMAL(15,1) DEFAULT 0;
