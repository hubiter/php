SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for hp_article
-- ----------------------------
DROP TABLE IF EXISTS `hp_article`;
CREATE TABLE `hp_article`  (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `cate_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `icon` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `source` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `author` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `source_url` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `source_url_md5` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `is_show` tinyint(3) NULL DEFAULT 1,
  `is_top` tinyint(3) NULL DEFAULT 2,
  `visits` int(11) NULL DEFAULT 0,
  `user_id` int(10) NULL DEFAULT 0,
  `ctime` datetime(0) NULL DEFAULT NULL,
  `utime` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`article_id`) USING BTREE,
  INDEX `cate_id`(`cate_id`) USING BTREE,
  INDEX `hp_a_source_url_md5`(`source_url_md5`) USING BTREE,
  INDEX `hubphp_article_cate_id_idx`(`cate_id`) USING BTREE,
  INDEX `hubphp_article_name_idx`(`name`) USING BTREE,
  FULLTEXT INDEX `hubphp_article_description_idx`(`description`)
) ENGINE = MyISAM AUTO_INCREMENT = 508774 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hp_article_cate
-- ----------------------------
DROP TABLE IF EXISTS `hp_article_cate`;
CREATE TABLE `hp_article_cate`  (
  `cate_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_id` int(11) NULL DEFAULT 0,
  `cate_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `order_num` tinyint(3) NULL DEFAULT 0,
  `ctime` datetime(0) NULL DEFAULT NULL,
  `utime` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`cate_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hp_article_content
-- ----------------------------
DROP TABLE IF EXISTS `hp_article_content`;
CREATE TABLE `hp_article_content`  (
  `article_id` int(10) NOT NULL DEFAULT 0,
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  UNIQUE INDEX `hubphp_article_content_article_id_idx`(`article_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hp_article_tag_rel
-- ----------------------------
DROP TABLE IF EXISTS `hp_article_tag_rel`;
CREATE TABLE `hp_article_tag_rel`  (
  `rel_id` int(10) NOT NULL AUTO_INCREMENT,
  `article_id` int(10) NOT NULL DEFAULT 0,
  `tag_id` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`rel_id`) USING BTREE,
  INDEX `hubphp_tag_rel_article_id_tag_id_idx`(`article_id`, `tag_id`) USING BTREE,
  INDEX `hubphp_tag_rel_tag_id_idx`(`tag_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 387315 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hp_tags
-- ----------------------------
DROP TABLE IF EXISTS `hp_tags`;
CREATE TABLE `hp_tags`  (
  `tag_id` int(10) NOT NULL AUTO_INCREMENT,
  `tag_val` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `is_main` tinyint(3) NOT NULL DEFAULT 2 COMMENT '是否主要标签 1：是；2：否',
  PRIMARY KEY (`tag_id`) USING BTREE,
  UNIQUE INDEX `hubphp_tags_tag_val_idx`(`tag_val`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 55 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hp_user
-- ----------------------------
DROP TABLE IF EXISTS `hp_user`;
CREATE TABLE `hp_user`  (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NULL DEFAULT NULL,
  `user_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `passwd` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `salt` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `nick_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `icon` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `mobile` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `status` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '1：正常；2：失效',
  `login_count` int(11) NULL DEFAULT NULL,
  `ctime` datetime(0) NULL,
  PRIMARY KEY (`user_id`) USING BTREE,
  UNIQUE INDEX `ts_user_username_idx`(`user_name`) USING BTREE,
  UNIQUE INDEX `ts_user_mobile_idx`(`mobile`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for hp_user_token
-- ----------------------------
DROP TABLE IF EXISTS `hp_user_token`;
CREATE TABLE `hp_user_token`  (
  `token_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户ID',
  `access_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '访问的token',
  `ip` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '当前访问者的IP',
  `login_time` datetime(0) NOT NULL COMMENT '当前访问时间',
  `expire_time` datetime(0) NOT NULL COMMENT '过期时间点',
  PRIMARY KEY (`token_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 148 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
