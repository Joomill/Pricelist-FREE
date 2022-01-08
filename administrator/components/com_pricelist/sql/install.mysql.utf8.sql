SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Table structure for table `#__pricelist_products`
--

CREATE TABLE IF NOT EXISTS `#__pricelist_products`
(
    `id`               int(10) UNSIGNED                                       NOT NULL AUTO_INCREMENT,
    `name`             varchar(255) COLLATE utf8mb4_unicode_ci                NOT NULL DEFAULT '',
    `alias`            varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `description`      mediumtext COLLATE utf8mb4_unicode_ci                           DEFAULT NULL,
    `price`            varchar(255) COLLATE utf8mb4_unicode_ci                         DEFAULT '',
    `checked_out_time` datetime,
    `checked_out`      int(10) UNSIGNED                                       NOT NULL DEFAULT 0,
    `params`           text COLLATE utf8mb4_unicode_ci                                 DEFAULT NULL,
    `ordering`         int(11)                                                NOT NULL DEFAULT 0,
    `language`         char(7) COLLATE utf8mb4_unicode_ci                     NOT NULL DEFAULT '*',
    `publish_down`     datetime,
    `publish_up`       datetime,
    `published`        tinyint(1)                                             NOT NULL DEFAULT 0,
    `created`          datetime                                               NOT NULL,
    `created_by`       int UNSIGNED                                           NOT NULL DEFAULT 0,
    `created_by_alias` varchar(255) COLLATE utf8mb4_unicode_ci                NOT NULL DEFAULT '',
    `modified`         datetime                                               NOT NULL,
    `modified_by`      int UNSIGNED                                           NOT NULL DEFAULT 0,
    `state`            tinyint(3)                                             NOT NULL DEFAULT 0,
    `catid`            int(11)                                                NOT NULL DEFAULT 0,
    `access`           int(10) UNSIGNED                                       NOT NULL DEFAULT 0,
    `featured`         tinyint(3) UNSIGNED                                    NOT NULL DEFAULT 0,
    `version`          int UNSIGNED                                           NOT NULL DEFAULT 1,

    PRIMARY KEY
        (
         `id`
            ),
    KEY `idx_access`
        (
         `access`
            ),
    KEY `idx_catid`
        (
         `catid`
            ),
    KEY `idx_createdby`
        (
         `created_by`
        ),
        KEY `idx_state`
        (
         `published`
            ),
    KEY `idx_language`
        (
         `language`
            ),
    KEY `idx_checkout`
        (
         `checked_out`
            ),
    KEY `idx_featured_catid`
        (
         `featured`,
         `catid`
            )
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;