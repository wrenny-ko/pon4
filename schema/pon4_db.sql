-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Aug 23, 2024 at 12:37 AM
-- Server version: 11.5.2-MariaDB-ubu2404
-- PHP Version: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pon4_db`
--

-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avatar` int(11) NOT NULL DEFAULT 1,
  `username` varchar(20) NOT NULL UNIQUE,
  `email` varchar(40) NOT NULL UNIQUE,
  `password` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE `perms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `admin` boolean NOT NULL,
  `mod` boolean NOT NULL,
  `tech` boolean NOT NULL,
  `beta` boolean NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `user_index` (`user`),
  FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE `leaderboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `max_rows` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE `leaderboard_hidden_columns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `column_name` varchar(20) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `scribblestar` boolean NOT NULL,
  `buttonpresser` boolean NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `user_index` (`user`),
  FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE `scribbles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL DEFAULT 1,
  `likes` int(11) NOT NULL DEFAULT 0,
  `dislikes` int(11) NOT NULL DEFAULT 0,
  `title` varchar(30) NOT NULL,
  `data_url` varchar(20000) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `user_index` (`user`),
  FOREIGN KEY (`user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `scribble` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  FOREIGN KEY (`scribble`) REFERENCES `scribbles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE `dislikes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `scribble` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  FOREIGN KEY (`scribble`) REFERENCES `scribbles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `scribble` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text` varchar(300) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user`) REFERENCES `users` (`id`),
  FOREIGN KEY (`scribble`) REFERENCES `scribbles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- set default user and avatar
INSERT INTO `users` (`username`, `email`, `password`) VALUES ('anonymous','me@me', 'deadbeef');
INSERT INTO `scribbles` (`user`, `likes`, `dislikes`, `title`,  `data_url`) VALUES (1, 0, 0, 'frist', 'data:image/png;base64,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');

-- set a mod and an admin user
--INSERT INTO `users` (`username`, `email`, `password`) VALUES ('anonymous','me@me', 'deadbeef');

INSERT INTO `leaderboard` (`max_rows`) VALUES ('10');
