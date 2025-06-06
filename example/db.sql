CREATE TABLE `authors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `authors` (`id`, `name`)
VALUES
	(1,'Test Testerson'),
	(2,'Ayla Mao'),
	(3,'Lew Swires');


CREATE TABLE `posts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text,
  PRIMARY KEY (`id`)
);

INSERT INTO `posts` (`id`, `author_id`, `title`, `body`)
VALUES
	(1,1,'Lorem ipsum','This is the first post oh my god haha'),
	(2,2,'Dolor sit amet','Wow i can\'t believe i\'m back for a second post');


CREATE TABLE `comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int unsigned NOT NULL,
  `author_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `comments` (`id`, `post_id`, `author_id`, `comment`)
VALUES
	(1,1,2,'wow'),
	(2,1,3,'haha'),
	(3,2,3,'this is amazing');