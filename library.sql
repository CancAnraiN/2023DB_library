-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2023-05-12 10:38:34
-- 伺服器版本： 10.4.28-MariaDB
-- PHP 版本： 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `library`
--

DELIMITER $$
--
-- 程序
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_due_list` ()  NO SQL SELECT I.issue_id, M.email, B.isbn, B.title
FROM book_issue_log I INNER JOIN member M on I.member = M.username INNER JOIN book B ON I.book_isbn = B.isbn
WHERE DATEDIFF(CURRENT_DATE, I.due_date) >= 0 AND DATEDIFF(CURRENT_DATE, I.due_date) % 5 = 0 AND (I.last_reminded IS NULL OR DATEDIFF(I.last_reminded, CURRENT_DATE) <> 0)$$

DELIMITER ;

-- --------------------------------------------------------

--
-- 資料表結構 `book`
--

CREATE TABLE `book` (
  `isbn` char(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int(4) UNSIGNED NOT NULL,
  `copies` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `book`
--

INSERT INTO `book` (`isbn`, `title`, `author`, `category`, `price`, `copies`) VALUES
('0000747532699', '哈利波特—神秘的魔法石', 'J·K·羅琳', '科幻', 300, 12),
('0000747538492', '哈利波特—消失的密室', 'J·K·羅琳', '科幻', 300, 9),
('0000747542155', '哈利波特—阿茲卡班的逃犯', 'J·K·羅琳', '科幻', 350, 16),
('0000747546240', '哈利波特—火盃的考驗', 'J·K·羅琳', '科幻', 400, 15),
('0000747551006', '哈利波特—鳳凰會的密令', 'J·K·羅琳', '科幻', 400, 20),
('0000747581088', '哈利波特—混血王子的背叛', 'J·K·羅琳', '科幻', 500, 25),
('1231231231231', '教學test', '111', '教育', 12313, 33),
('6666666666666', '惡魔之書', '我', '科幻', 66666, 3),
('9786263243798', 'PHP8/MySQL網頁程式設計自學聖經', '文淵閣工作室', '電腦資訊', 400, 2),
('9786263344938', '資料結構', '王致強', '考試用書', 100, 2),
('9786267203460', '全新NEW TOEIC 900分 必考單字+文法：這本最好，輕鬆突破900分 ', '張小怡, Johnson Mo', '語言學習', 275, 3),
('9789572695807', '輝夜姬想讓人告白~天才們的戀愛頭腦戰~ 25', '赤坂アカ', '漫畫', 133, 49),
('9789577873415', '篠宮神社系列 1 我再也進不了山的故事', '夜行列車', '日本文學', 350, 0),
('9789864792122', '鋼鐵人馬斯克：從特斯拉到太空探索，大夢想家如何創造驚奇的未來（最新增訂版）', '艾胥黎．范思', '人物傳記', 650, 10);

-- --------------------------------------------------------

--
-- 資料表結構 `book_issue_log`
--

CREATE TABLE `book_issue_log` (
  `issue_id` int(11) NOT NULL,
  `member` varchar(20) NOT NULL,
  `book_isbn` varchar(13) NOT NULL,
  `due_date` date NOT NULL,
  `last_reminded` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `book_issue_log`
--

INSERT INTO `book_issue_log` (`issue_id`, `member`, `book_isbn`, `due_date`, `last_reminded`) VALUES
(5, 'gary', '6666666666666', '2023-05-18', NULL),
(7, 'gary', '9786267203460', '2023-05-18', NULL),
(8, 'hank', '9786267203460', '2023-05-18', NULL),
(9, 'test1', '0000747538492', '2023-05-18', NULL),
(10, 'test2', '9789577873415', '2023-05-18', NULL),
(11, 'test2', '9789572695807', '2023-05-18', NULL),
(12, 'gay', '9786263344938', '2023-05-18', NULL),
(14, 'gary', '9789577873415', '2023-05-19', NULL);

--
-- 觸發器 `book_issue_log`
--
DELIMITER $$
CREATE TRIGGER `issue_book` BEFORE INSERT ON `book_issue_log` FOR EACH ROW BEGIN
	SET NEW.due_date = DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY);
    UPDATE member SET balance = balance - (SELECT price FROM book WHERE isbn = NEW.book_isbn) WHERE username = NEW.member;
    UPDATE book SET copies = copies - 1 WHERE isbn = NEW.book_isbn;
    DELETE FROM pending_book_requests WHERE member = NEW.member AND book_isbn = NEW.book_isbn;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `return_book` BEFORE DELETE ON `book_issue_log` FOR EACH ROW BEGIN
    UPDATE member SET balance = balance + (SELECT price FROM book WHERE isbn = OLD.book_isbn) WHERE username = OLD.member;
    UPDATE book SET copies = copies + 1 WHERE isbn = OLD.book_isbn;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 資料表結構 `librarian`
--

CREATE TABLE `librarian` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` char(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `librarian`
--

INSERT INTO `librarian` (`id`, `username`, `password`) VALUES
(1, 'can', '39dfa55283318d31afe5a3ff4a0e3253e2045e43');

-- --------------------------------------------------------

--
-- 資料表結構 `member`
--

CREATE TABLE `member` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` char(40) NOT NULL,
  `name` varchar(80) NOT NULL,
  `email` varchar(80) NOT NULL,
  `balance` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `member`
--

INSERT INTO `member` (`id`, `username`, `password`, `name`, `email`, `balance`) VALUES
(6, 'test2', '109f4b3c50d7b0df729d299bc6f8e9ef9066971f', 'test22', 'gary@mail', 49522),
(12, 'gary', 'f9023000f29773649f3850298becb9544b5fd6a9', 'gary', 'garyko0406@gmail.com', 499999375),
(13, 'hank', '43956efcf5eeea0437d78fe9942c54f5587b3285', 'hank lee', 'gary@mail123123', 39725),
(14, 'gay', '2871591e64f027a23fadafaa00305a8bd3484292', 'gaygayder', 'gary@mail32323111', 49900),
(17, 'test1', '6e45a996ca8c1c3bb0a7807c039dfffb02c0cad2', 'set', 'gary200046@yahoo.com.tw', 50000);

--
-- 觸發器 `member`
--
DELIMITER $$
CREATE TRIGGER `add_member` AFTER INSERT ON `member` FOR EACH ROW DELETE FROM pending_registrations WHERE username = NEW.username
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `remove_member` AFTER DELETE ON `member` FOR EACH ROW DELETE FROM pending_book_requests WHERE member = OLD.username
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- 資料表結構 `pending_book_requests`
--

CREATE TABLE `pending_book_requests` (
  `request_id` int(11) NOT NULL,
  `member` varchar(20) NOT NULL,
  `book_isbn` varchar(13) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `pending_book_requests`
--

INSERT INTO `pending_book_requests` (`request_id`, `member`, `book_isbn`, `time`) VALUES
(21, 'gay', '0000747546240', '2023-05-12 05:52:46');

-- --------------------------------------------------------

--
-- 資料表結構 `pending_registrations`
--

CREATE TABLE `pending_registrations` (
  `username` varchar(20) NOT NULL,
  `password` char(40) NOT NULL,
  `name` varchar(80) NOT NULL,
  `email` varchar(80) NOT NULL,
  `balance` int(4) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `pending_registrations`
--

INSERT INTO `pending_registrations` (`username`, `password`, `name`, `email`, `balance`, `time`) VALUES
('demo', '89e495e7941cf9e40e6980d14a16bf023ccd4c91', 'demoo', 'demo@gmail', 40000, '2023-05-12 05:53:31');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`isbn`);

--
-- 資料表索引 `book_issue_log`
--
ALTER TABLE `book_issue_log`
  ADD PRIMARY KEY (`issue_id`);

--
-- 資料表索引 `librarian`
--
ALTER TABLE `librarian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 資料表索引 `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 資料表索引 `pending_book_requests`
--
ALTER TABLE `pending_book_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- 資料表索引 `pending_registrations`
--
ALTER TABLE `pending_registrations`
  ADD PRIMARY KEY (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `book_issue_log`
--
ALTER TABLE `book_issue_log`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `librarian`
--
ALTER TABLE `librarian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `member`
--
ALTER TABLE `member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `pending_book_requests`
--
ALTER TABLE `pending_book_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
