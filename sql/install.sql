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
  `level`  ENUM('admin', 'mod', 'user') DEFAULT 'user',

  `status` ENUM('inactive', 'active', 'banned') DEFAULT 'inactive',
  `penalty` INT(4) UNSIGNED DEFAULT 0,
  `reason`  VARCHAR(60) DEFAULT NULL,

  `file_path`  VARCHAR(60) DEFAULT '/clenexyz/imagem.php',
  `file_name`  VARCHAR(128) DEFAULT NULL,
  `file_sum` VARCHAR(32) DEFAULT NULL,

  `about` VARCHAR(512) DEFAULT 'No much to say...',

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `cl_saloons`;
CREATE TABLE `cl_saloons`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,

  `visible` BOOLEAN DEFAULT 1,
  `locked`  BOOLEAN DEFAULT 0,
  `password`  VARCHAR(32) DEFAULT NULL,

  `name`  VARCHAR(20) NOT NULL,
  `alias` VARCHAR(10) NOT NULL,
  `rules` TEXT DEFAULT NULL,

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY `unique_saloon` (`alias`)
);

DROP TABLE IF EXISTS `cl_posts`;
CREATE TABLE `cl_posts`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,

  `file_path`  VARCHAR(60) NOT NULL,
  `file_name`  VARCHAR(128) NOT NULL,
  `file_sum` VARCHAR(32) NOT NULL,

  `title` VARCHAR(60) DEFAULT NULL,
  `body`  TEXT DEFAULT NULL,

  `saloon` VARCHAR(60) NOT NULL,

  `visible`   BOOLEAN DEFAULT true,
  `status`  ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `mod_id`  INTEGER DEFAULT NULL,

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
  `flags` SET('homepage', 'reincident', 'mass') DEFAULT NULL,
  `views` INT(4) UNSIGNED DEFAULT 0,

  `fullpage_path` VARCHAR(60) NOT NULL,
  `preview_path`  VARCHAR(60) NOT NULL,

  `status`  ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `mod_id`  INTEGER DEFAULT NULL,

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS `cl_comments`;
CREATE TABLE `cl_comments`
(
  `ID`  INTEGER PRIMARY KEY AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,
  `entry_id`  INTEGER NOT NULL,
  `context`  ENUM('post', 'mirror') DEFAULT NULL,

  `file_path`  VARCHAR(60) DEFAULT NULL,
  `file_name`  VARCHAR(128) DEFAULT NULL,
  `file_sum` VARCHAR(32) DEFAULT NULL,

  `body`  TEXT NOT NULL,

  `status`  ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `mod_id`  INTEGER DEFAULT NULL,

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

  `file_path`  VARCHAR(60) DEFAULT NULL,
  `file_name`  VARCHAR(128) DEFAULT NULL,
  `file_sum` VARCHAR(32) DEFAULT NULL,

  `title` VARCHAR(60) NOT NULL,
  `subtitle`  VARCHAR(60) DEFAULT NULL,
  `body`  TEXT NOT NULL,

  `status`  ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `mod_id`  INTEGER DEFAULT NULL,

  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO `cl_saloons` (
  `name`,
  `alias`
) VALUES (
  'Testing saloon',
  'testing'
);

INSERT INTO `cl_saloons` (
  `name`,
  `alias`
) VALUES (
  'Clening saloon',
  'clening'
);

INSERT INTO `cl_users` (
  `name`,
  `email`,
  `password`,
  `about`,
  `level`,
  `status`
) VALUES (
  'teste',
  'teste',
  MD5(CONCAT('teste', 'NOLETO')),
  'Test user.',
  'admin',
  'active'
);

INSERT INTO `cl_users` (
  `name`,
  `email`,
  `password`,
  `about`,
  `level`,
  `status`
) VALUES (
  'teste2',
  'teste2',
  MD5(CONCAT('teste2', 'NOLETO')),
  'Test user.',
  'mod',
  'active'
);

INSERT INTO `cl_users` (
  `name`,
  `email`,
  `password`,
  `about`,
  `level`,
  `status`
) VALUES (
  'teste3',
  'teste3',
  MD5(CONCAT('teste3', 'NOLETO')),
  'Test user.',
  'user',
  'active'
);
