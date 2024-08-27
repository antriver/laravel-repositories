DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` INTEGER NOT NULL primary key autoincrement,
  `text` varchar(255) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
);

DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `id` INTEGER NOT NULL primary key autoincrement,
  `text` varchar(255) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
);
