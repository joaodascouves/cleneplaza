DROP DATABASE IF EXISTS `clene2`;
CREATE DATABASE `clene2`;

USE `clene2`;

DROP TABLE IF EXISTS `cl_users`;
CREATE TABLE `cl_users`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,
  `email`  VARCHAR(60) NOT NULL,
  `name` VARCHAR(32) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `level`  ENUM('user', 'admin') DEFAULT 'user',

  `file_path`  VARCHAR(60) DEFAULT '/clenexyz/imagem.php',
  `file_name`  VARCHAR(128) DEFAULT NULL,
  `file_sum` VARCHAR(32) DEFAULT NULL,

  `about` VARCHAR(512) DEFAULT 'No much to say...',

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `cl_posts`;
CREATE TABLE `cl_posts`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,
  `aproved_by_user_id`  INTEGER DEFAULT 0,

  `file_path`  VARCHAR(60) NOT NULL,
  `file_name`  VARCHAR(128) NOT NULL,
  `file_sum` VARCHAR(32) NOT NULL,

  `body`  TEXT DEFAULT NULL,

  `visible`   BOOLEAN DEFAULT true,
  `status`  ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `cl_mirrors`;
CREATE TABLE `cl_mirrors`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,

  `ip`  INT(4) UNSIGNED NOT NULL,
  `url` VARCHAR(128) NOT NULL,
  `domain` VARCHAR(128) NOT NULL,
  `flags` SET('homepage', 'reincident', 'mass'),

  `fullpage_path` VARCHAR(60) NOT NULL,
  `preview_path`  VARCHAR(60) NOT NULL,

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `cl_comments`;
CREATE TABLE `cl_comments`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


DROP TABLE IF EXISTS `cl_coins`;
CREATE TABLE `cl_coins`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,
  `amount`  INTEGER DEFAULT 0,

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `cl_news`;
CREATE TABLE `cl_news`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,

  `title` VARCHAR(60) NOT NULL,
  `subtitle`  VARCHAR(60) NOT NULL,
  `body`  TEXT NOT NULL,

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO `cl_users` (
  `name`,
  `email`,
  `password`,
  `about`
) VALUES (
  'teste',
  'teste',
  MD5(CONCAT('teste', 'NOLETO')),
  'Test user.'
);
