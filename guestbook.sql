-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 21, 2010 at 12:08 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `s37`
--

-- --------------------------------------------------------

--
-- Table structure for table `guestbook`
--

CREATE TABLE IF NOT EXISTS `guestbook` (
  `guestbook_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guestbook_posted` datetime NOT NULL,
  `guestbook_nama` varchar(250) NOT NULL,
  `guestbook_kota` varchar(150) NOT NULL,
  `guestbook_email` varchar(150) NOT NULL,
  `guestbook_website` varchar(150) NOT NULL,
  `guestbook_pekerjaan` varchar(50) NOT NULL,
  `guestbook_komentar` text NOT NULL,
  PRIMARY KEY (`guestbook_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `guestbook`
--

INSERT INTO `guestbook` (`guestbook_id`, `guestbook_posted`, `guestbook_nama`, `guestbook_kota`, `guestbook_email`, `guestbook_website`, `guestbook_pekerjaan`, `guestbook_komentar`) VALUES
(1, '2010-11-21 11:08:03', 'Indra Sutriadi Pipii', 'Kotamobagu', 'indra.sutriadi@gmail.com', 'sutriadi.web.id', 'guru', 'Halo pengunjung website!\r\nSelamat datang!');
