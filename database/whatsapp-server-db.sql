-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 29, 2024 at 05:41 AM
-- Server version: 8.0.40-0ubuntu0.24.10.1
-- PHP Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `whatsml`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `pkId` int NOT NULL,
  `sessionId` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `archived` tinyint(1) DEFAULT NULL,
  `contactPrimaryIdentityKey` longblob,
  `conversationTimestamp` bigint DEFAULT NULL,
  `createdAt` bigint DEFAULT NULL,
  `createdBy` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disappearingMode` json DEFAULT NULL,
  `displayName` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endOfHistoryTransfer` tinyint(1) DEFAULT NULL,
  `endOfHistoryTransferType` int DEFAULT NULL,
  `ephemeralExpiration` int DEFAULT NULL,
  `ephemeralSettingTimestamp` bigint DEFAULT NULL,
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isDefaultSubgroup` tinyint(1) DEFAULT NULL,
  `isParentGroup` tinyint(1) DEFAULT NULL,
  `lastMsgTimestamp` bigint DEFAULT NULL,
  `lidJid` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `markedAsUnread` tinyint(1) DEFAULT NULL,
  `mediaVisibility` int DEFAULT NULL,
  `messages` json DEFAULT NULL,
  `muteEndTime` bigint DEFAULT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `newJid` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notSpam` tinyint(1) DEFAULT NULL,
  `oldJid` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pHash` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parentGroupId` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participant` json DEFAULT NULL,
  `pinned` bigint DEFAULT NULL,
  `pnJid` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pnhDuplicateLidThread` tinyint(1) DEFAULT NULL,
  `readOnly` tinyint(1) DEFAULT NULL,
  `shareOwnPn` tinyint(1) DEFAULT NULL,
  `support` tinyint(1) DEFAULT NULL,
  `suspended` tinyint(1) DEFAULT NULL,
  `tcToken` longblob,
  `tcTokenSenderTimestamp` bigint DEFAULT NULL,
  `tcTokenTimestamp` bigint DEFAULT NULL,
  `terminated` tinyint(1) DEFAULT NULL,
  `unreadCount` int DEFAULT NULL,
  `unreadMentionCount` int DEFAULT NULL,
  `wallpaper` json DEFAULT NULL,
  `lastMessageRecvTimestamp` int DEFAULT NULL,
  `commentsCount` int DEFAULT NULL,
  `picture` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_reply_enabled` tinyint(1) DEFAULT '1',
  `wlc_mgs_send_at` timestamp NULL DEFAULT NULL,
  `badge_id` bigint DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `pkId` int NOT NULL,
  `sessionId` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notify` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verifiedName` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imgUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_meta_data`
--

CREATE TABLE `group_meta_data` (
  `pkId` int NOT NULL,
  `sessionId` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subjectOwner` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subjectTime` int DEFAULT NULL,
  `creation` int DEFAULT NULL,
  `desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descOwner` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descId` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `restrict` tinyint(1) DEFAULT NULL,
  `announce` tinyint(1) DEFAULT NULL,
  `isCommunity` tinyint(1) DEFAULT NULL,
  `isCommunityAnnounce` tinyint(1) DEFAULT NULL,
  `joinApprovalMode` tinyint(1) DEFAULT NULL,
  `memberAddMode` tinyint(1) DEFAULT NULL,
  `author` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `participants` json NOT NULL,
  `ephemeralDuration` int DEFAULT NULL,
  `inviteCode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `pkId` int NOT NULL,
  `sessionId` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remoteJid` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agentId` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bizPrivacyStatus` int DEFAULT NULL,
  `broadcast` tinyint(1) DEFAULT NULL,
  `clearMedia` tinyint(1) DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `ephemeralDuration` int DEFAULT NULL,
  `ephemeralOffToOn` tinyint(1) DEFAULT NULL,
  `ephemeralOutOfSync` tinyint(1) DEFAULT NULL,
  `ephemeralStartTimestamp` bigint DEFAULT NULL,
  `finalLiveLocation` json DEFAULT NULL,
  `futureproofData` longblob,
  `ignore` tinyint(1) DEFAULT NULL,
  `keepInChat` json DEFAULT NULL,
  `key` json NOT NULL,
  `labels` json DEFAULT NULL,
  `mediaCiphertextSha256` longblob,
  `mediaData` json DEFAULT NULL,
  `message` json DEFAULT NULL,
  `messageC2STimestamp` bigint DEFAULT NULL,
  `messageSecret` longblob,
  `messageStubParameters` json DEFAULT NULL,
  `messageStubType` int DEFAULT NULL,
  `messageTimestamp` bigint DEFAULT NULL,
  `multicast` tinyint(1) DEFAULT NULL,
  `originalSelfAuthorUserJidString` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participant` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paymentInfo` json DEFAULT NULL,
  `photoChange` json DEFAULT NULL,
  `pollAdditionalMetadata` json DEFAULT NULL,
  `pollUpdates` json DEFAULT NULL,
  `pushName` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotedPaymentInfo` json DEFAULT NULL,
  `quotedStickerData` json DEFAULT NULL,
  `reactions` json DEFAULT NULL,
  `revokeMessageTimestamp` bigint DEFAULT NULL,
  `starred` tinyint(1) DEFAULT NULL,
  `status` int DEFAULT NULL,
  `statusAlreadyViewed` tinyint(1) DEFAULT NULL,
  `statusPsa` json DEFAULT NULL,
  `urlNumber` tinyint(1) DEFAULT NULL,
  `urlText` tinyint(1) DEFAULT NULL,
  `userReceipt` json DEFAULT NULL,
  `verifiedBizName` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eventResponses` json DEFAULT NULL,
  `pinInChat` json DEFAULT NULL,
  `reportingTokenInfo` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `pkId` int NOT NULL,
  `sessionId` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `_prisma_migrations`
--

CREATE TABLE `_prisma_migrations` (
  `id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checksum` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `finished_at` datetime(3) DEFAULT NULL,
  `migration_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logs` text COLLATE utf8mb4_unicode_ci,
  `rolled_back_at` datetime(3) DEFAULT NULL,
  `started_at` datetime(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `applied_steps_count` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`pkId`),
  ADD UNIQUE KEY `unique_id_per_session_id_1` (`sessionId`,`id`),
  ADD KEY `chat_sessionId_idx` (`sessionId`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`pkId`),
  ADD UNIQUE KEY `unique_id_per_session_id_2` (`sessionId`,`id`),
  ADD KEY `contact_sessionId_idx` (`sessionId`);

--
-- Indexes for table `group_meta_data`
--
ALTER TABLE `group_meta_data`
  ADD PRIMARY KEY (`pkId`),
  ADD UNIQUE KEY `unique_id_per_session_id_3` (`sessionId`,`id`),
  ADD KEY `group_meta_data_sessionId_idx` (`sessionId`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`pkId`),
  ADD UNIQUE KEY `unique_message_key_per_session_id` (`sessionId`,`remoteJid`,`id`),
  ADD KEY `message_sessionId_idx` (`sessionId`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`pkId`),
  ADD UNIQUE KEY `unique_id_per_session_id_4` (`sessionId`,`id`),
  ADD KEY `session_sessionId_idx` (`sessionId`);

--
-- Indexes for table `_prisma_migrations`
--
ALTER TABLE `_prisma_migrations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `pkId` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `pkId` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `group_meta_data`
--
ALTER TABLE `group_meta_data`
  MODIFY `pkId` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `pkId` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `session`
--
ALTER TABLE `session`
  MODIFY `pkId` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
