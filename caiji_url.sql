/*
Navicat MySQL Data Transfer

Source Server         : 阿里云
Source Server Version : 50548
Source Host           : 121.43.60.36:3306
Source Database       : caiji

Target Server Type    : MYSQL
Target Server Version : 50548
File Encoding         : 65001

Date: 2016-10-19 17:14:03
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for caiji_url
-- ----------------------------
DROP TABLE IF EXISTS `caiji_url`;
CREATE TABLE `caiji_url` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '电影名称',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '电影图片',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '电影url',
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '计数器',
  `status` int(10) NOT NULL DEFAULT '0' COMMENT '0未采集1已采集',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `caiji_url_num` (`num`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for dianyin
-- ----------------------------
DROP TABLE IF EXISTS `dianyin`;
CREATE TABLE `dianyin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ids` int(10) NOT NULL DEFAULT '0' COMMENT '目标站电影id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '电影标题',
  `img` varchar(255) NOT NULL DEFAULT '' COMMENT '电影缩略图',
  `list_img` text NOT NULL COMMENT '电影内容图',
  `sku` text NOT NULL COMMENT '电影sku',
  `jieshao` text NOT NULL COMMENT '电影介绍',
  `bt` text NOT NULL COMMENT '电影bt_url',
  `status` int(10) NOT NULL DEFAULT '0' COMMENT '0未采集1已采集',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `dianyin_ids` (`ids`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
