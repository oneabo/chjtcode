/*
 Navicat Premium Data Transfer

 Source Server         : 9h_v2_locall_linux
 Source Server Type    : MySQL
 Source Server Version : 50730
 Source Host           : 192.168.1.10:3306
 Source Schema         : 9h_v2

 Target Server Type    : MySQL
 Target Server Version : 50730
 File Encoding         : 65001

 Date: 27/09/2020 10:41:14
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for 9h_admin
-- ----------------------------
DROP TABLE IF EXISTS `9h_admin`;
CREATE TABLE `9h_admin`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `last_login_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '最后登录时间',
  `last_login_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '最后登录ip',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否可以访问 0禁止1可以',
  `account` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '账号',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '密码',
  `mobile` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '手机号',
  `salt` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '随机盐值',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '管理员邮箱',
  `errlogin_info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '错误登陆时登陆的信息',
  `role_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '权限角色',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `account`(`account`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '后台人员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_admin
-- ----------------------------
INSERT INTO `9h_admin` VALUES (1, 1601171716, '127.0.0.1', 1588932087, 1, 'admin', '###b5c5f9eceddb5c83a499a352b69d7fc8', '', 'bQfR6h', '', '', '-1');

-- ----------------------------
-- Table structure for 9h_admin_mymenu
-- ----------------------------
DROP TABLE IF EXISTS `9h_admin_mymenu`;
CREATE TABLE `9h_admin_mymenu`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父菜单id',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '菜单类型;1:有界面可访问菜单,2:无界面可访问菜单(接口),0:只作为菜单文字显示',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态;1:显示,0:不显示',
  `sort` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序 越大越前',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'module/controller/action?param组成的url',
  `extra_urls` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '该菜单下的附加url集合，以 ,隔开',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '菜单名称',
  `page` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '菜单页面路径(给VUE使用)',
  `active_page` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '激活菜单路径（隐藏时可填写）',
  `icon` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '菜单图标',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `btn_show` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否上级按钮触发显示0不设置，1设置',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '后台菜单表' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of 9h_admin_mymenu
-- ----------------------------
INSERT INTO `9h_admin_mymenu` VALUES (1, 0, 0, 1, 0, '', '', '系统管理', '', '', 'tools', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (2, 1, 1, 1, 0, 'admin/role/index', 'admin/role/edit,admin/role/del,admin/role/enable,admin/role/editRoleMenus,admin/role/getRoleMenusId,admin/menu/index', '角色管理', 'admin/role', '', '', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (3, 1, 1, 1, 0, 'admin/menu/index', 'admin/menu/del', '后台菜单', 'admin/menulist', '', '', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (4, 1, 1, 1, 0, 'admin/account/index', 'admin/role/index,admin/account/edit,admin/account/del,admin/account/enable', '管理员列表', 'admin/list', '', '', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (5, 1, 1, 0, 0, 'admin/menu/getMenuInfo', 'admin/menu/index,admin/menu/edit', '菜单编辑', 'admin/menuedit', 'admin/menulist', '', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (6, 1, 1, 1, 0, 'admin/sys/sysInfo', 'admin/sys/sysEdit', '基础设置', 'admin/baseset', '', '', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (7, 1, 1, 1, 0, 'admin/banner/getBannerList', 'admin/banner/bannerChangeSort,admin/banner/bannerEnable,admin/banner/bannerDel,admin/banner/bannerEdit', '广告图管理', 'admin/banner', '', '', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (8, 0, 1, 0, 0, 'admin/index/editPassword', '', '修改密码', 'changePassword/index', '', 'tools', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (9, 1, 1, 1, 0, 'admin/index/cityList', '', '区域管理', 'admin/cityList', '', '', '', 0);
INSERT INTO `9h_admin_mymenu` VALUES (10, 1, 1, 1, 0, 'admin/column/index', '', '栏目管理', 'admin/column', '', '', '', 0);

-- ----------------------------
-- Table structure for 9h_admin_myrole
-- ----------------------------
DROP TABLE IF EXISTS `9h_admin_myrole`;
CREATE TABLE `9h_admin_myrole`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态;0:禁用;1:正常',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `list_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'vue的角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_admin_myrole
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_admin_myrole_auth_access
-- ----------------------------
DROP TABLE IF EXISTS `9h_admin_myrole_auth_access`;
CREATE TABLE `9h_admin_myrole_auth_access`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色',
  `mymenu_ids` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'mymenu的ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `role_id`(`role_id`) USING BTREE,
  INDEX `mymenu_id`(`mymenu_ids`(255)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '权限授权表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_admin_myrole_auth_access
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_admin_token
-- ----------------------------
DROP TABLE IF EXISTS `9h_admin_token`;
CREATE TABLE `9h_admin_token`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员id',
  `expire_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '过期时间',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'token',
  `device_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '设备类型;mobile,android,iphone,web,pc,wxapp',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `admin_id`(`admin_id`) USING BTREE,
  UNIQUE INDEX `token`(`token`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '后台人员token' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_admin_token
-- ----------------------------
INSERT INTO `9h_admin_token` VALUES (1, 1, 1601178916, 1601171716, '3677d53fa156ea58ccd5b6b195fe875d', 'pc');

-- ----------------------------
-- Table structure for 9h_banner_img
-- ----------------------------
DROP TABLE IF EXISTS `9h_banner_img`;
CREATE TABLE `9h_banner_img`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cover` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片文件路径',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '应用客户端类型 0小程序',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否开启状态',
  `sort` int(11) NOT NULL COMMENT '排序',
  `place` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片应用位置，首页或者其他页面',
  `href` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '点击图片后要跳转的页面地址',
  `update_time` int(11) UNSIGNED NULL DEFAULT 0,
  UNIQUE INDEX `id`(`id`) USING BTREE,
  INDEX `type`(`type`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `place`(`place`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_banner_img
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_city_building
-- ----------------------------
DROP TABLE IF EXISTS `9h_city_building`;
CREATE TABLE `9h_city_building`  (
  `id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '编号',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '楼栋名称',
  `num` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '楼栋编号',
  `floor_total` int(11) NULL DEFAULT NULL COMMENT '总楼层',
  `room_total` int(11) NULL DEFAULT NULL COMMENT '每层总户数',
  `build_type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '建筑类型',
  `build_date` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '建筑日期',
  `build_usage` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '建筑用途',
  `estate_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '板块编号',
  `is_elevator` tinyint(1) NOT NULL COMMENT '是否有电梯',
  `is_locked` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否锁定',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  `time_created` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `estate_id`(`estate_id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_city_building
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_city_district
-- ----------------------------
DROP TABLE IF EXISTS `9h_city_district`;
CREATE TABLE `9h_city_district`  (
  `id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '编号',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '板块名称',
  `area_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '区域编号【关联】',
  `map` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地图',
  `is_deleted` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `time_created` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `area`(`area_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_city_district
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_city_estate
-- ----------------------------
DROP TABLE IF EXISTS `9h_city_estate`;
CREATE TABLE `9h_city_estate`  (
  `id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '编号\r\n',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '板块名称\r\n',
  `name_py` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '板块拼音',
  `map` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地图',
  `alias` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '别名',
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地址',
  `build_date` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '建筑时间\r\n',
  `area_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '区域绑定id\r\n',
  `area_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '区域名称',
  `district_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '板块绑定id',
  `district_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '板块名称',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `is_locked` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否锁定',
  `time_updated` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  `time_created` int(11) NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `area_id`(`area_id`) USING BTREE,
  INDEX `district_id`(`district_id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_city_estate
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_city_house
-- ----------------------------
DROP TABLE IF EXISTS `9h_city_house`;
CREATE TABLE `9h_city_house`  (
  `id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房号',
  `map` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地图',
  `house_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房屋类型',
  `room_num` int(11) NULL DEFAULT NULL COMMENT '房间数量',
  `hall_num` int(11) NULL DEFAULT NULL COMMENT '大厅数量',
  `toilet_num` int(11) NULL DEFAULT NULL COMMENT '卫生间数量',
  `balcony_num` int(11) NULL DEFAULT NULL COMMENT '阳台数量',
  `kitchen_num` int(11) NULL DEFAULT NULL COMMENT '厨房数量',
  `floor_num` int(11) NULL DEFAULT NULL COMMENT '楼层数量',
  `gross_area` decimal(10, 2) NULL DEFAULT NULL COMMENT '总面积',
  `net_area` decimal(10, 2) NULL DEFAULT NULL COMMENT '净面积',
  `towards` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '朝向',
  `area_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '区域编号',
  `district_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '板块编号',
  `estate_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '楼盘编号',
  `building_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '栋座编号',
  `is_deleted` tinyint(1) NULL DEFAULT NULL COMMENT '是否删除',
  `is_locked` tinyint(1) NULL DEFAULT NULL COMMENT '是否删除',
  `time_updated` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  `time_created` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `area_id`(`area_id`) USING BTREE,
  INDEX `district_id`(`district_id`) USING BTREE,
  INDEX `estate_id`(`estate_id`) USING BTREE,
  INDEX `building_id`(`building_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_city_house
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_column
-- ----------------------------
DROP TABLE IF EXISTS `9h_column`;
CREATE TABLE `9h_column`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cover` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片文件路径',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '应用客户端类型 0 H5端',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否开启状态',
  `sort` int(11) NOT NULL COMMENT '排序',
  `place` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '图片应用位置，首页或者其他页面',
  `href` varchar(125) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '点击图片后要跳转的页面地址',
  `update_time` int(11) UNSIGNED NULL DEFAULT 0,
  UNIQUE INDEX `id`(`id`) USING BTREE,
  INDEX `type`(`type`) USING BTREE,
  INDEX `place`(`place`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_column
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_com_area
-- ----------------------------
DROP TABLE IF EXISTS `9h_com_area`;
CREATE TABLE `9h_com_area`  (
  `id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '编号',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '区域名称',
  `parent_id` int(11) NULL DEFAULT 0 COMMENT '隶属',
  `area_id` int(11) NULL DEFAULT 0 COMMENT '关联【旧版】区域',
  `is_deleted` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NULL DEFAULT 0 COMMENT '创建时间',
  `time_created` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `area`(`area_id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `parentTo`(`parent_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_com_area
-- ----------------------------
INSERT INTO `9h_com_area` VALUES ('592', '厦门市', 0, 10, 0, 1589426950, 1589426950);
INSERT INTO `9h_com_area` VALUES ('59201', '思明区', 592, 1, 0, 1589426950, 1589426950);
INSERT INTO `9h_com_area` VALUES ('59202', '湖里区', 592, 2, 0, 1589426950, 1589426950);
INSERT INTO `9h_com_area` VALUES ('59203', '海沧区', 592, 3, 0, 1589426950, 1589426950);
INSERT INTO `9h_com_area` VALUES ('59204', '集美区', 592, 4, 0, 1589426950, 1589426950);
INSERT INTO `9h_com_area` VALUES ('59205', '同安区', 592, 5, 0, 1589426950, 1589426950);
INSERT INTO `9h_com_area` VALUES ('59206', '厦门周边', 592, 6, 0, 1589426950, 1589426950);

-- ----------------------------
-- Table structure for 9h_com_dicts
-- ----------------------------
DROP TABLE IF EXISTS `9h_com_dicts`;
CREATE TABLE `9h_com_dicts`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '类型',
  `value` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '相关值',
  `is_deleted` tinyint(1) NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `time_created` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `type`(`type`) USING BTREE,
  INDEX `value`(`value`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 110 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_com_dicts
-- ----------------------------
INSERT INTO `9h_com_dicts` VALUES (1, '商住楼', 'type', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (2, '住宅', 'type', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (3, '别墅', 'type', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (4, '写字楼', 'type', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (5, '真', 'tag', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (6, '聚', 'tag', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (7, '优', 'tag', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (8, '特', 'tag', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (9, '私', 'tag', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (10, '有视频', 'tag', '5', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (11, '有照片', 'tag', '6', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (12, '学区房', 'tag', '7', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (13, '电梯', 'tag', '8', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (14, '地铁', 'tag', '9', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (15, '验真中', 'tag', '10', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (16, '未验真', 'tag', '11', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (17, 'VIP', 'tag', '12', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (18, '有钥匙', 'tag', '13', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (19, '满五', 'tag', '14', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (20, '满二', 'tag', '15', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (21, '唯一', 'tag', '16', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (22, '东', 'towards', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (23, '南', 'towards', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (24, '西', 'towards', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (25, '北', 'towards', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (26, '南北', 'towards', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (27, '东西', 'towards', '5', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (28, '东南', 'towards', '6', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (29, '东北', 'towards', '7', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (30, '西南', 'towards', '8', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (31, '西北', 'towards', '9', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (32, '业主', 'relation', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (33, '配偶', 'relation', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (34, '朋友', 'relation', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (35, '租客', 'relation', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (36, '其他中介', 'relation', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (37, '栋', 'building_unit', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (38, '号', 'building_unit', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (39, '幢', 'building_unit', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (40, '座', 'building_unit', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (41, '巷', 'building_unit', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (42, '自定义', 'building_unit', '5', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (43, '单元', 'unit_unit', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (44, '无单元', 'unit_unit', 'null', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (45, '在租', 'status', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (46, '在售', 'status', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (47, '核销', 'status', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (48, '简装', 'decoration_state', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (49, '豪装', 'decoration_state', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (50, '精装', 'decoration_state', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (51, '中装', 'decoration_state', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (52, '毛坯', 'decoration_state', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (53, '有', 'has_carport', 'Y', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (54, '无', 'has_carport', 'N', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (55, '有电梯', 'is_elevator', 'Y', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (56, '无电梯', 'is_elevator', 'N', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (57, '有地铁', 'has_subway', 'Y', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (58, '无地铁', 'has_subway', 'N', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (59, '塔楼', 'build_type', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (60, '板楼', 'build_type', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (61, '板塔结合', 'build_type', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (62, '平房', 'build_type', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (63, '住宅', 'build_usage', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (64, '别墅', 'build_usage', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (65, '别墅洋房', 'build_usage', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (66, '花园住宅', 'build_usage', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (67, '车位', 'build_usage', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (68, '公寓', 'build_usage', '5', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (69, '商住', 'build_usage', '6', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (70, '商铺', 'build_usage', '7', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (71, '写字楼', 'build_usage', '8', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (72, '地皮', 'build_usage', '9', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (73, '安置房', 'property_right_nature', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (74, '公产房', 'property_right_nature', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (75, '经适房', 'property_right_nature', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (76, '商品房', 'property_right_nature', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (77, '私产房', 'property_right_nature', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (78, '小产房', 'property_right_nature', '5', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (79, '企业产房', 'property_right_nature', '6', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (80, '有', 'has_school_quota', 'Y', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (81, '无', 'has_school_quota', 'N', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (82, '电视', 'appliances', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (83, '空调', 'appliances', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (84, '宽带', 'appliances', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (85, '洗衣机', 'appliances', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (86, '热水器', 'appliances', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (87, '油烟机', 'appliances', '5', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (88, '冰箱', 'appliances', '6', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (89, '床', 'appliances', '7', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (90, '衣柜', 'appliances', '8', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (91, '沙发', 'appliances', '9', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (92, '桌子', 'appliances', '10', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (93, '椅子', 'appliances', '11', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (94, '合租', 'rental_mode', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (95, '整租', 'rental_mode', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (96, '整合均可', 'rental_mode', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (97, '长期', 'rental_term', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (98, '短期', 'rental_term', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (99, '月付', 'payment_method', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (100, '半年付', 'payment_method', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (101, '双月付', 'payment_method', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (102, '年付', 'payment_method', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (103, '季付', 'payment_method', '4', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (104, '室内', 'attachment_type', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (105, '户型', 'attachment_type', '1', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (106, '其他', 'attachment_type', '2', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (107, '视频', 'attachment_type', '3', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (108, '离职', 'agent_status', '0', 0, 1589426950, 1589426950);
INSERT INTO `9h_com_dicts` VALUES (109, '在职', 'agent_status', '1', 0, 1589426950, 1589426950);

-- ----------------------------
-- Table structure for 9h_property
-- ----------------------------
DROP TABLE IF EXISTS `9h_property`;
CREATE TABLE `9h_property`  (
  `id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '编号',
  `number` int(11) NULL DEFAULT NULL COMMENT '【原萃】房源编号',
  `verification_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '政府认证房源编号',
  `estate_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '【关联】楼盘ID',
  `price` decimal(10, 2) NULL DEFAULT NULL COMMENT '售价',
  `is_sale` tinyint(1) NULL DEFAULT 0 COMMENT '【关联】在售楼盘编号',
  `is_rental` tinyint(1) NULL DEFAULT 0 COMMENT '【关联】出租楼盘编号',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地址',
  `building_num` int(11) NULL DEFAULT NULL COMMENT '楼号',
  `building_unit` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '栋座单位（如栋、幢等）',
  `unit_num` int(11) NULL DEFAULT NULL COMMENT '单元',
  `unit_unit` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '单元单位',
  `house_num` varchar(0) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房号',
  `build_type` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房屋类型',
  `build_usage` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '建筑用途',
  `room_num` int(11) NULL DEFAULT NULL COMMENT '房间数量',
  `hall_num` int(11) NULL DEFAULT NULL COMMENT '客厅数量',
  `toilet_num` int(11) NULL DEFAULT NULL COMMENT '卫生间数量',
  `balcony_num` int(11) NULL DEFAULT NULL COMMENT '阳台数量',
  `gross_area` double(10, 2) NULL DEFAULT NULL COMMENT '建筑面积',
  `inside_area` int(11) NULL DEFAULT NULL COMMENT '套内面积',
  `free_area` int(11) NULL DEFAULT NULL COMMENT '赠送面积',
  `towards` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '朝向',
  `floor` int(11) NULL DEFAULT NULL COMMENT '楼层',
  `floor_total` int(11) NULL DEFAULT NULL COMMENT '总楼层数',
  `decoration_state` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '装修情况',
  `decoration_date` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '装修日期',
  `property_right_nature` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '产权性质',
  `property_right_year` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '产权年限',
  `ladder_num` int(11) NULL DEFAULT NULL COMMENT '梯户情况（梯数）',
  `household_num` int(11) NULL DEFAULT NULL COMMENT '梯户情况（户数）',
  `has_subway` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '地铁配套',
  `has_carport` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '车位情况',
  `has_school` tinyint(1) NOT NULL DEFAULT 0 COMMENT '学区名额',
  `is_elevator` tinyint(1) NOT NULL DEFAULT 0 COMMENT '电梯情况',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NOT NULL COMMENT '更新的时间',
  `time_created` int(11) NOT NULL COMMENT '创建的时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `estate_id`(`estate_id`) USING BTREE,
  INDEX `number`(`number`) USING BTREE,
  INDEX `verification_code`(`verification_code`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_property
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_property_attachment
-- ----------------------------
DROP TABLE IF EXISTS `9h_property_attachment`;
CREATE TABLE `9h_property_attachment`  (
  `id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `type` int(11) NULL DEFAULT NULL COMMENT '类型',
  `property_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房源编号',
  `path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地址',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注说明',
  `sale_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '在售',
  `rental_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '出租',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NOT NULL COMMENT '更新的时间',
  `time_created` int(11) NOT NULL COMMENT '创建的时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `type`(`type`) USING BTREE,
  INDEX `property_id`(`property_id`) USING BTREE,
  INDEX `sale_id`(`sale_id`) USING BTREE,
  INDEX `rental_id`(`rental_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_property_attachment
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_property_landlord
-- ----------------------------
DROP TABLE IF EXISTS `9h_property_landlord`;
CREATE TABLE `9h_property_landlord`  (
  `id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '编号',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `relation` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '委托关系',
  `phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '电话',
  `wechat_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '微信编号',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注说明',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NOT NULL COMMENT '更新的时间',
  `time_created` int(11) NOT NULL COMMENT '创建的时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_property_landlord
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_property_log
-- ----------------------------
DROP TABLE IF EXISTS `9h_property_log`;
CREATE TABLE `9h_property_log`  (
  `id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `property_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '楼盘id',
  `sale_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '出售编号',
  `rental_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '在租编号',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '操作说明',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NOT NULL COMMENT '更新的时间',
  `time_created` int(11) NOT NULL COMMENT '创建的时间',
  PRIMARY KEY (`id`(4)) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `property_id`(`property_id`) USING BTREE,
  INDEX `sale_id`(`sale_id`) USING BTREE,
  INDEX `rental_id`(`rental_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_property_log
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_property_news
-- ----------------------------
DROP TABLE IF EXISTS `9h_property_news`;
CREATE TABLE `9h_property_news`  (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_property_news
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_property_price_his
-- ----------------------------
DROP TABLE IF EXISTS `9h_property_price_his`;
CREATE TABLE `9h_property_price_his`  (
  `id` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '编号',
  `property_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房源编号',
  `price` decimal(10, 2) NULL DEFAULT NULL COMMENT '售价',
  `is_sale` tinyint(1) NULL DEFAULT 0 COMMENT '【是否】在售',
  `is_rental` tinyint(1) NULL DEFAULT 0 COMMENT '【是否】出租',
  `is_news` tinyint(1) NULL DEFAULT NULL COMMENT '【是否】新房',
  `times` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '日期含年月',
  `remarks` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注说明',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NOT NULL COMMENT '更新的时间',
  `time_created` int(11) NOT NULL COMMENT '创建的时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `property_id`(`property_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_property_price_his
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_property_rental
-- ----------------------------
DROP TABLE IF EXISTS `9h_property_rental`;
CREATE TABLE `9h_property_rental`  (
  `id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `property_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房源编号',
  `number` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '【原萃】房源编号',
  `owner_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开盘人',
  `maintainer_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '维护人',
  `check_real_status` tinyint(1) NULL DEFAULT NULL COMMENT '是否已验真',
  `exploration_status` tinyint(1) NULL DEFAULT NULL COMMENT '是否已实勘',
  `comment_title` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房源标题-经纪人',
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房源状态',
  `appliances` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '配套设施',
  `rental_mode` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '出租方式',
  `rental_term` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '出租期限',
  `checkin_date` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '入住时间',
  `payment_method` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '付款方式',
  `landlord_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '【关联】业主信息表',
  `tags` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标签',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NOT NULL COMMENT '更新的时间',
  `time_created` int(11) NOT NULL COMMENT '创建的时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `property_id`(`property_id`) USING BTREE,
  INDEX `owner_id`(`owner_id`) USING BTREE,
  INDEX `maintainer_id`(`maintainer_id`) USING BTREE,
  INDEX `landlord_id`(`landlord_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_property_rental
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_property_sale
-- ----------------------------
DROP TABLE IF EXISTS `9h_property_sale`;
CREATE TABLE `9h_property_sale`  (
  `id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `property_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房源编号',
  `number` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '【原萃】房源编号',
  `owner_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '开盘人',
  `maintainer_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '维护人',
  `check_real_status` tinyint(1) NULL DEFAULT 0 COMMENT '是否已验真',
  `exploration_status` tinyint(1) NULL DEFAULT 0 COMMENT '是否已实勘',
  `comment_title` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房源标题-经纪人',
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房源状态',
  `comments` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '经纪人点评内容',
  `landlord_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '【关联】业主信息表',
  `tags` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标签',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `time_updated` int(11) NOT NULL COMMENT '更新的时间',
  `time_created` int(11) NOT NULL COMMENT '创建的时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `property_id`(`property_id`) USING BTREE,
  INDEX `owner_id`(`owner_id`) USING BTREE,
  INDEX `maintainer_id`(`maintainer_id`) USING BTREE,
  INDEX `landlord_id`(`landlord_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_property_sale
-- ----------------------------

-- ----------------------------
-- Table structure for 9h_site_city
-- ----------------------------
DROP TABLE IF EXISTS `9h_site_city`;
CREATE TABLE `9h_site_city`  (
  `id` int(11) NOT NULL COMMENT '市级编码',
  `cname` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL COMMENT '名称',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否启用',
  `is_hot` tinyint(1) NULL DEFAULT 0,
  `update_time` int(11) UNSIGNED NULL DEFAULT 0,
  `sort` int(11) UNSIGNED NULL DEFAULT 0,
  `pid` int(11) NULL DEFAULT 0 COMMENT '省份编码',
  `pcname` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL COMMENT '省份名称',
  UNIQUE INDEX `id`(`id`) USING BTREE,
  INDEX `status`(`status`) USING BTREE,
  INDEX `is_hot`(`is_hot`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_bin ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of 9h_site_city
-- ----------------------------
INSERT INTO `9h_site_city` VALUES (140400, '长治市', 1, 0, 1601023990, 2, 140000, '山西省');
INSERT INTO `9h_site_city` VALUES (350200, '厦门市', 0, 0, 0, 0, 350000, '福建省');

-- ----------------------------
-- Table structure for 9h_sys_city
-- ----------------------------
DROP TABLE IF EXISTS `9h_sys_city`;
CREATE TABLE `9h_sys_city`  (
  `id` int(11) NOT NULL COMMENT '编码',
  `cname` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL COMMENT '名称',
  `pid` int(11) NOT NULL COMMENT '上级',
  `lv` tinyint(4) NULL DEFAULT 1,
  UNIQUE INDEX `id`(`id`) USING BTREE,
  INDEX `cname`(`cname`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_bin ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of 9h_sys_city
-- ----------------------------
INSERT INTO `9h_sys_city` VALUES (110000, '北京市', 0, 1);
INSERT INTO `9h_sys_city` VALUES (110100, '北京市(市区)', 110000, 2);
INSERT INTO `9h_sys_city` VALUES (110101, '东城区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110102, '西城区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110105, '朝阳区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110106, '丰台区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110107, '石景山区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110108, '海淀区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110109, '门头沟区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110111, '房山区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110112, '通州区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110113, '顺义区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110114, '昌平区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110115, '大兴区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110116, '怀柔区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110117, '平谷区', 110100, 3);
INSERT INTO `9h_sys_city` VALUES (110200, '北京市 (县)', 110000, 2);
INSERT INTO `9h_sys_city` VALUES (110228, '密云县', 110200, 3);
INSERT INTO `9h_sys_city` VALUES (110229, '延庆县', 110200, 3);
INSERT INTO `9h_sys_city` VALUES (120000, '天津市', 0, 1);
INSERT INTO `9h_sys_city` VALUES (120100, '天津市(市区)', 120000, 2);
INSERT INTO `9h_sys_city` VALUES (120101, '和平区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120102, '河东区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120103, '河西区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120104, '南开区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120105, '河北区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120106, '红桥区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120110, '东丽区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120111, '西青区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120112, '津南区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120113, '北辰区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120114, '武清区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120115, '宝坻区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120116, '滨海新区', 120100, 3);
INSERT INTO `9h_sys_city` VALUES (120200, '天津市 (县)', 120000, 2);
INSERT INTO `9h_sys_city` VALUES (120221, '宁河县', 120200, 3);
INSERT INTO `9h_sys_city` VALUES (120223, '静海县', 120200, 3);
INSERT INTO `9h_sys_city` VALUES (120225, '蓟县', 120200, 3);
INSERT INTO `9h_sys_city` VALUES (130000, '河北省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (130100, '石家庄市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130102, '长安区', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130104, '桥西区', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130105, '新华区', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130107, '井陉矿区', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130108, '裕华区', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130109, '藁城区', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130110, '鹿泉区', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130111, '栾城区', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130121, '井陉县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130123, '正定县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130125, '行唐县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130126, '灵寿县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130127, '高邑县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130128, '深泽县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130129, '赞皇县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130130, '无极县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130131, '平山县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130132, '元氏县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130133, '赵县', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130181, '辛集市', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130183, '晋州市', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130184, '新乐市', 130100, 3);
INSERT INTO `9h_sys_city` VALUES (130200, '唐山市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130202, '路南区', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130203, '路北区', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130204, '古冶区', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130205, '开平区', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130207, '丰南区', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130208, '丰润区', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130209, '曹妃甸区', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130223, '滦县', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130224, '滦南县', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130225, '乐亭县', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130227, '迁西县', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130229, '玉田县', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130281, '遵化市', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130283, '迁安市', 130200, 3);
INSERT INTO `9h_sys_city` VALUES (130300, '秦皇岛市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130302, '海港区', 130300, 3);
INSERT INTO `9h_sys_city` VALUES (130303, '山海关区', 130300, 3);
INSERT INTO `9h_sys_city` VALUES (130304, '北戴河区', 130300, 3);
INSERT INTO `9h_sys_city` VALUES (130321, '青龙满族自治县', 130300, 3);
INSERT INTO `9h_sys_city` VALUES (130322, '昌黎县', 130300, 3);
INSERT INTO `9h_sys_city` VALUES (130323, '抚宁县', 130300, 3);
INSERT INTO `9h_sys_city` VALUES (130324, '卢龙县', 130300, 3);
INSERT INTO `9h_sys_city` VALUES (130400, '邯郸市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130402, '邯山区', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130403, '丛台区', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130404, '复兴区', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130406, '峰峰矿区', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130421, '邯郸县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130423, '临漳县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130424, '成安县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130425, '大名县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130426, '涉县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130427, '磁县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130428, '肥乡县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130429, '永年县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130430, '邱县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130431, '鸡泽县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130432, '广平县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130433, '馆陶县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130434, '魏县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130435, '曲周县', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130481, '武安市', 130400, 3);
INSERT INTO `9h_sys_city` VALUES (130500, '邢台市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130502, '桥东区', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130503, '桥西区', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130521, '邢台县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130522, '临城县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130523, '内丘县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130524, '柏乡县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130525, '隆尧县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130526, '任县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130527, '南和县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130528, '宁晋县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130529, '巨鹿县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130530, '新河县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130531, '广宗县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130532, '平乡县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130533, '威县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130534, '清河县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130535, '临西县', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130581, '南宫市', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130582, '沙河市', 130500, 3);
INSERT INTO `9h_sys_city` VALUES (130600, '保定市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130602, '新市区', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130603, '北市区', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130604, '南市区', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130621, '满城县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130622, '清苑县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130623, '涞水县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130624, '阜平县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130625, '徐水县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130626, '定兴县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130627, '唐县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130628, '高阳县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130629, '容城县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130630, '涞源县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130631, '望都县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130632, '安新县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130633, '易县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130634, '曲阳县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130635, '蠡县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130636, '顺平县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130637, '博野县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130638, '雄县', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130681, '涿州市', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130682, '定州市', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130683, '安国市', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130684, '高碑店市', 130600, 3);
INSERT INTO `9h_sys_city` VALUES (130700, '张家口市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130702, '桥东区', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130703, '桥西区', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130705, '宣化区', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130706, '下花园区', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130721, '宣化县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130722, '张北县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130723, '康保县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130724, '沽源县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130725, '尚义县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130726, '蔚县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130727, '阳原县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130728, '怀安县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130729, '万全县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130730, '怀来县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130731, '涿鹿县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130732, '赤城县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130733, '崇礼县', 130700, 3);
INSERT INTO `9h_sys_city` VALUES (130800, '承德市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130802, '双桥区', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130803, '双滦区', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130804, '鹰手营子矿区', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130821, '承德县', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130822, '兴隆县', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130823, '平泉县', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130824, '滦平县', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130825, '隆化县', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130826, '丰宁满族自治县', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130827, '宽城满族自治县', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130828, '围场满族蒙古族自治县', 130800, 3);
INSERT INTO `9h_sys_city` VALUES (130900, '沧州市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (130902, '新华区', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130903, '运河区', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130921, '沧县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130922, '青县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130923, '东光县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130924, '海兴县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130925, '盐山县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130926, '肃宁县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130927, '南皮县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130928, '吴桥县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130929, '献县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130930, '孟村回族自治县', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130981, '泊头市', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130982, '任丘市', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130983, '黄骅市', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (130984, '河间市', 130900, 3);
INSERT INTO `9h_sys_city` VALUES (131000, '廊坊市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (131002, '安次区', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131003, '广阳区', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131022, '固安县', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131023, '永清县', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131024, '香河县', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131025, '大城县', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131026, '文安县', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131028, '大厂回族自治县', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131081, '霸州市', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131082, '三河市', 131000, 3);
INSERT INTO `9h_sys_city` VALUES (131100, '衡水市', 130000, 2);
INSERT INTO `9h_sys_city` VALUES (131102, '桃城区', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131121, '枣强县', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131122, '武邑县', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131123, '武强县', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131124, '饶阳县', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131125, '安平县', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131126, '故城县', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131127, '景县', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131128, '阜城县', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131181, '冀州市', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (131182, '深州市', 131100, 3);
INSERT INTO `9h_sys_city` VALUES (140000, '山西省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (140100, '太原市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140105, '小店区', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140106, '迎泽区', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140107, '杏花岭区', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140108, '尖草坪区', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140109, '万柏林区', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140110, '晋源区', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140121, '清徐县', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140122, '阳曲县', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140123, '娄烦县', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140181, '古交市', 140100, 3);
INSERT INTO `9h_sys_city` VALUES (140200, '大同市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140202, '城区', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140203, '矿区', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140211, '南郊区', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140212, '新荣区', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140221, '阳高县', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140222, '天镇县', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140223, '广灵县', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140224, '灵丘县', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140225, '浑源县', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140226, '左云县', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140227, '大同县', 140200, 3);
INSERT INTO `9h_sys_city` VALUES (140300, '阳泉市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140302, '城区', 140300, 3);
INSERT INTO `9h_sys_city` VALUES (140303, '矿区', 140300, 3);
INSERT INTO `9h_sys_city` VALUES (140311, '郊区', 140300, 3);
INSERT INTO `9h_sys_city` VALUES (140321, '平定县', 140300, 3);
INSERT INTO `9h_sys_city` VALUES (140322, '盂县', 140300, 3);
INSERT INTO `9h_sys_city` VALUES (140400, '长治市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140402, '城区', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140411, '郊区', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140421, '长治县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140423, '襄垣县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140424, '屯留县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140425, '平顺县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140426, '黎城县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140427, '壶关县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140428, '长子县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140429, '武乡县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140430, '沁县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140431, '沁源县', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140481, '潞城市', 140400, 3);
INSERT INTO `9h_sys_city` VALUES (140500, '晋城市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140502, '城区', 140500, 3);
INSERT INTO `9h_sys_city` VALUES (140521, '沁水县', 140500, 3);
INSERT INTO `9h_sys_city` VALUES (140522, '阳城县', 140500, 3);
INSERT INTO `9h_sys_city` VALUES (140524, '陵川县', 140500, 3);
INSERT INTO `9h_sys_city` VALUES (140525, '泽州县', 140500, 3);
INSERT INTO `9h_sys_city` VALUES (140581, '高平市', 140500, 3);
INSERT INTO `9h_sys_city` VALUES (140600, '朔州市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140602, '朔城区', 140600, 3);
INSERT INTO `9h_sys_city` VALUES (140603, '平鲁区', 140600, 3);
INSERT INTO `9h_sys_city` VALUES (140621, '山阴县', 140600, 3);
INSERT INTO `9h_sys_city` VALUES (140622, '应县', 140600, 3);
INSERT INTO `9h_sys_city` VALUES (140623, '右玉县', 140600, 3);
INSERT INTO `9h_sys_city` VALUES (140624, '怀仁县', 140600, 3);
INSERT INTO `9h_sys_city` VALUES (140700, '晋中市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140702, '榆次区', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140721, '榆社县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140722, '左权县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140723, '和顺县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140724, '昔阳县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140725, '寿阳县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140726, '太谷县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140727, '祁县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140728, '平遥县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140729, '灵石县', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140781, '介休市', 140700, 3);
INSERT INTO `9h_sys_city` VALUES (140800, '运城市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140802, '盐湖区', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140821, '临猗县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140822, '万荣县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140823, '闻喜县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140824, '稷山县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140825, '新绛县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140826, '绛县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140827, '垣曲县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140828, '夏县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140829, '平陆县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140830, '芮城县', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140881, '永济市', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140882, '河津市', 140800, 3);
INSERT INTO `9h_sys_city` VALUES (140900, '忻州市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (140902, '忻府区', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140921, '定襄县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140922, '五台县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140923, '代县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140924, '繁峙县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140925, '宁武县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140926, '静乐县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140927, '神池县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140928, '五寨县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140929, '岢岚县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140930, '河曲县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140931, '保德县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140932, '偏关县', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (140981, '原平市', 140900, 3);
INSERT INTO `9h_sys_city` VALUES (141000, '临汾市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (141002, '尧都区', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141021, '曲沃县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141022, '翼城县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141023, '襄汾县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141024, '洪洞县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141025, '古县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141026, '安泽县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141027, '浮山县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141028, '吉县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141029, '乡宁县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141030, '大宁县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141031, '隰县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141032, '永和县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141033, '蒲县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141034, '汾西县', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141081, '侯马市', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141082, '霍州市', 141000, 3);
INSERT INTO `9h_sys_city` VALUES (141100, '吕梁市', 140000, 2);
INSERT INTO `9h_sys_city` VALUES (141102, '离石区', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141121, '文水县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141122, '交城县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141123, '兴县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141124, '临县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141125, '柳林县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141126, '石楼县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141127, '岚县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141128, '方山县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141129, '中阳县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141130, '交口县', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141181, '孝义市', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (141182, '汾阳市', 141100, 3);
INSERT INTO `9h_sys_city` VALUES (150000, '内蒙古自治区', 0, 1);
INSERT INTO `9h_sys_city` VALUES (150100, '呼和浩特市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150102, '新城区', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150103, '回民区', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150104, '玉泉区', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150105, '赛罕区', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150121, '土默特左旗', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150122, '托克托县', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150123, '和林格尔县', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150124, '清水河县', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150125, '武川县', 150100, 3);
INSERT INTO `9h_sys_city` VALUES (150200, '包头市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150202, '东河区', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150203, '昆都仑区', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150204, '青山区', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150205, '石拐区', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150206, '白云鄂博矿区', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150207, '九原区', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150221, '土默特右旗', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150222, '固阳县', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150223, '达尔罕茂明安联合旗', 150200, 3);
INSERT INTO `9h_sys_city` VALUES (150300, '乌海市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150302, '海勃湾区', 150300, 3);
INSERT INTO `9h_sys_city` VALUES (150303, '海南区', 150300, 3);
INSERT INTO `9h_sys_city` VALUES (150304, '乌达区', 150300, 3);
INSERT INTO `9h_sys_city` VALUES (150400, '赤峰市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150402, '红山区', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150403, '元宝山区', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150404, '松山区', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150421, '阿鲁科尔沁旗', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150422, '巴林左旗', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150423, '巴林右旗', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150424, '林西县', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150425, '克什克腾旗', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150426, '翁牛特旗', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150428, '喀喇沁旗', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150429, '宁城县', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150430, '敖汉旗', 150400, 3);
INSERT INTO `9h_sys_city` VALUES (150500, '通辽市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150502, '科尔沁区', 150500, 3);
INSERT INTO `9h_sys_city` VALUES (150521, '科尔沁左翼中旗', 150500, 3);
INSERT INTO `9h_sys_city` VALUES (150522, '科尔沁左翼后旗', 150500, 3);
INSERT INTO `9h_sys_city` VALUES (150523, '开鲁县', 150500, 3);
INSERT INTO `9h_sys_city` VALUES (150524, '库伦旗', 150500, 3);
INSERT INTO `9h_sys_city` VALUES (150525, '奈曼旗', 150500, 3);
INSERT INTO `9h_sys_city` VALUES (150526, '扎鲁特旗', 150500, 3);
INSERT INTO `9h_sys_city` VALUES (150581, '霍林郭勒市', 150500, 3);
INSERT INTO `9h_sys_city` VALUES (150600, '鄂尔多斯市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150602, '东胜区', 150600, 3);
INSERT INTO `9h_sys_city` VALUES (150621, '达拉特旗', 150600, 3);
INSERT INTO `9h_sys_city` VALUES (150622, '准格尔旗', 150600, 3);
INSERT INTO `9h_sys_city` VALUES (150623, '鄂托克前旗', 150600, 3);
INSERT INTO `9h_sys_city` VALUES (150624, '鄂托克旗', 150600, 3);
INSERT INTO `9h_sys_city` VALUES (150625, '杭锦旗', 150600, 3);
INSERT INTO `9h_sys_city` VALUES (150626, '乌审旗', 150600, 3);
INSERT INTO `9h_sys_city` VALUES (150627, '伊金霍洛旗', 150600, 3);
INSERT INTO `9h_sys_city` VALUES (150700, '呼伦贝尔市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150702, '海拉尔区', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150703, '扎赉诺尔区', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150721, '阿荣旗', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150722, '莫力达瓦达斡尔族自治旗', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150723, '鄂伦春自治旗', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150724, '鄂温克族自治旗', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150725, '陈巴尔虎旗', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150726, '新巴尔虎左旗', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150727, '新巴尔虎右旗', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150781, '满洲里市', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150782, '牙克石市', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150783, '扎兰屯市', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150784, '额尔古纳市', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150785, '根河市', 150700, 3);
INSERT INTO `9h_sys_city` VALUES (150800, '巴彦淖尔市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150802, '临河区', 150800, 3);
INSERT INTO `9h_sys_city` VALUES (150821, '五原县', 150800, 3);
INSERT INTO `9h_sys_city` VALUES (150822, '磴口县', 150800, 3);
INSERT INTO `9h_sys_city` VALUES (150823, '乌拉特前旗', 150800, 3);
INSERT INTO `9h_sys_city` VALUES (150824, '乌拉特中旗', 150800, 3);
INSERT INTO `9h_sys_city` VALUES (150825, '乌拉特后旗', 150800, 3);
INSERT INTO `9h_sys_city` VALUES (150826, '杭锦后旗', 150800, 3);
INSERT INTO `9h_sys_city` VALUES (150900, '乌兰察布市', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (150902, '集宁区', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150921, '卓资县', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150922, '化德县', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150923, '商都县', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150924, '兴和县', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150925, '凉城县', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150926, '察哈尔右翼前旗', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150927, '察哈尔右翼中旗', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150928, '察哈尔右翼后旗', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150929, '四子王旗', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (150981, '丰镇市', 150900, 3);
INSERT INTO `9h_sys_city` VALUES (152200, '兴安盟', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (152201, '乌兰浩特市', 152200, 3);
INSERT INTO `9h_sys_city` VALUES (152202, '阿尔山市', 152200, 3);
INSERT INTO `9h_sys_city` VALUES (152221, '科尔沁右翼前旗', 152200, 3);
INSERT INTO `9h_sys_city` VALUES (152222, '科尔沁右翼中旗', 152200, 3);
INSERT INTO `9h_sys_city` VALUES (152223, '扎赉特旗', 152200, 3);
INSERT INTO `9h_sys_city` VALUES (152224, '突泉县', 152200, 3);
INSERT INTO `9h_sys_city` VALUES (152500, '锡林郭勒盟', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (152501, '二连浩特市', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152502, '锡林浩特市', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152522, '阿巴嘎旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152523, '苏尼特左旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152524, '苏尼特右旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152525, '东乌珠穆沁旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152526, '西乌珠穆沁旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152527, '太仆寺旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152528, '镶黄旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152529, '正镶白旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152530, '正蓝旗', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152531, '多伦县', 152500, 3);
INSERT INTO `9h_sys_city` VALUES (152900, '阿拉善盟', 150000, 2);
INSERT INTO `9h_sys_city` VALUES (152921, '阿拉善左旗', 152900, 3);
INSERT INTO `9h_sys_city` VALUES (152922, '阿拉善右旗', 152900, 3);
INSERT INTO `9h_sys_city` VALUES (152923, '额济纳旗', 152900, 3);
INSERT INTO `9h_sys_city` VALUES (210000, '辽宁省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (210100, '沈阳市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210102, '和平区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210103, '沈河区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210104, '大东区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210105, '皇姑区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210106, '铁西区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210111, '苏家屯区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210112, '浑南区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210113, '沈北新区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210114, '于洪区', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210122, '辽中县', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210123, '康平县', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210124, '法库县', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210181, '新民市', 210100, 3);
INSERT INTO `9h_sys_city` VALUES (210200, '大连市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210202, '中山区', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210203, '西岗区', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210204, '沙河口区', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210211, '甘井子区', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210212, '旅顺口区', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210213, '金州区', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210224, '长海县', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210281, '瓦房店市', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210282, '普兰店市', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210283, '庄河市', 210200, 3);
INSERT INTO `9h_sys_city` VALUES (210300, '鞍山市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210302, '铁东区', 210300, 3);
INSERT INTO `9h_sys_city` VALUES (210303, '铁西区', 210300, 3);
INSERT INTO `9h_sys_city` VALUES (210304, '立山区', 210300, 3);
INSERT INTO `9h_sys_city` VALUES (210311, '千山区', 210300, 3);
INSERT INTO `9h_sys_city` VALUES (210321, '台安县', 210300, 3);
INSERT INTO `9h_sys_city` VALUES (210323, '岫岩满族自治县', 210300, 3);
INSERT INTO `9h_sys_city` VALUES (210381, '海城市', 210300, 3);
INSERT INTO `9h_sys_city` VALUES (210400, '抚顺市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210402, '新抚区', 210400, 3);
INSERT INTO `9h_sys_city` VALUES (210403, '东洲区', 210400, 3);
INSERT INTO `9h_sys_city` VALUES (210404, '望花区', 210400, 3);
INSERT INTO `9h_sys_city` VALUES (210411, '顺城区', 210400, 3);
INSERT INTO `9h_sys_city` VALUES (210421, '抚顺县', 210400, 3);
INSERT INTO `9h_sys_city` VALUES (210422, '新宾满族自治县', 210400, 3);
INSERT INTO `9h_sys_city` VALUES (210423, '清原满族自治县', 210400, 3);
INSERT INTO `9h_sys_city` VALUES (210500, '本溪市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210502, '平山区', 210500, 3);
INSERT INTO `9h_sys_city` VALUES (210503, '溪湖区', 210500, 3);
INSERT INTO `9h_sys_city` VALUES (210504, '明山区', 210500, 3);
INSERT INTO `9h_sys_city` VALUES (210505, '南芬区', 210500, 3);
INSERT INTO `9h_sys_city` VALUES (210521, '本溪满族自治县', 210500, 3);
INSERT INTO `9h_sys_city` VALUES (210522, '桓仁满族自治县', 210500, 3);
INSERT INTO `9h_sys_city` VALUES (210600, '丹东市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210602, '元宝区', 210600, 3);
INSERT INTO `9h_sys_city` VALUES (210603, '振兴区', 210600, 3);
INSERT INTO `9h_sys_city` VALUES (210604, '振安区', 210600, 3);
INSERT INTO `9h_sys_city` VALUES (210624, '宽甸满族自治县', 210600, 3);
INSERT INTO `9h_sys_city` VALUES (210681, '东港市', 210600, 3);
INSERT INTO `9h_sys_city` VALUES (210682, '凤城市', 210600, 3);
INSERT INTO `9h_sys_city` VALUES (210700, '锦州市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210702, '古塔区', 210700, 3);
INSERT INTO `9h_sys_city` VALUES (210703, '凌河区', 210700, 3);
INSERT INTO `9h_sys_city` VALUES (210711, '太和区', 210700, 3);
INSERT INTO `9h_sys_city` VALUES (210726, '黑山县', 210700, 3);
INSERT INTO `9h_sys_city` VALUES (210727, '义县', 210700, 3);
INSERT INTO `9h_sys_city` VALUES (210781, '凌海市', 210700, 3);
INSERT INTO `9h_sys_city` VALUES (210782, '北镇市', 210700, 3);
INSERT INTO `9h_sys_city` VALUES (210800, '营口市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210802, '站前区', 210800, 3);
INSERT INTO `9h_sys_city` VALUES (210803, '西市区', 210800, 3);
INSERT INTO `9h_sys_city` VALUES (210804, '鲅鱼圈区', 210800, 3);
INSERT INTO `9h_sys_city` VALUES (210811, '老边区', 210800, 3);
INSERT INTO `9h_sys_city` VALUES (210881, '盖州市', 210800, 3);
INSERT INTO `9h_sys_city` VALUES (210882, '大石桥市', 210800, 3);
INSERT INTO `9h_sys_city` VALUES (210900, '阜新市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (210902, '海州区', 210900, 3);
INSERT INTO `9h_sys_city` VALUES (210903, '新邱区', 210900, 3);
INSERT INTO `9h_sys_city` VALUES (210904, '太平区', 210900, 3);
INSERT INTO `9h_sys_city` VALUES (210905, '清河门区', 210900, 3);
INSERT INTO `9h_sys_city` VALUES (210911, '细河区', 210900, 3);
INSERT INTO `9h_sys_city` VALUES (210921, '阜新蒙古族自治县', 210900, 3);
INSERT INTO `9h_sys_city` VALUES (210922, '彰武县', 210900, 3);
INSERT INTO `9h_sys_city` VALUES (211000, '辽阳市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (211002, '白塔区', 211000, 3);
INSERT INTO `9h_sys_city` VALUES (211003, '文圣区', 211000, 3);
INSERT INTO `9h_sys_city` VALUES (211004, '宏伟区', 211000, 3);
INSERT INTO `9h_sys_city` VALUES (211005, '弓长岭区', 211000, 3);
INSERT INTO `9h_sys_city` VALUES (211011, '太子河区', 211000, 3);
INSERT INTO `9h_sys_city` VALUES (211021, '辽阳县', 211000, 3);
INSERT INTO `9h_sys_city` VALUES (211081, '灯塔市', 211000, 3);
INSERT INTO `9h_sys_city` VALUES (211100, '盘锦市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (211102, '双台子区', 211100, 3);
INSERT INTO `9h_sys_city` VALUES (211103, '兴隆台区', 211100, 3);
INSERT INTO `9h_sys_city` VALUES (211121, '大洼县', 211100, 3);
INSERT INTO `9h_sys_city` VALUES (211122, '盘山县', 211100, 3);
INSERT INTO `9h_sys_city` VALUES (211200, '铁岭市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (211202, '银州区', 211200, 3);
INSERT INTO `9h_sys_city` VALUES (211204, '清河区', 211200, 3);
INSERT INTO `9h_sys_city` VALUES (211221, '铁岭县', 211200, 3);
INSERT INTO `9h_sys_city` VALUES (211223, '西丰县', 211200, 3);
INSERT INTO `9h_sys_city` VALUES (211224, '昌图县', 211200, 3);
INSERT INTO `9h_sys_city` VALUES (211281, '调兵山市', 211200, 3);
INSERT INTO `9h_sys_city` VALUES (211282, '开原市', 211200, 3);
INSERT INTO `9h_sys_city` VALUES (211300, '朝阳市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (211302, '双塔区', 211300, 3);
INSERT INTO `9h_sys_city` VALUES (211303, '龙城区', 211300, 3);
INSERT INTO `9h_sys_city` VALUES (211321, '朝阳县', 211300, 3);
INSERT INTO `9h_sys_city` VALUES (211322, '建平县', 211300, 3);
INSERT INTO `9h_sys_city` VALUES (211324, '喀喇沁左翼蒙古族自治县', 211300, 3);
INSERT INTO `9h_sys_city` VALUES (211381, '北票市', 211300, 3);
INSERT INTO `9h_sys_city` VALUES (211382, '凌源市', 211300, 3);
INSERT INTO `9h_sys_city` VALUES (211400, '葫芦岛市', 210000, 2);
INSERT INTO `9h_sys_city` VALUES (211402, '连山区', 211400, 3);
INSERT INTO `9h_sys_city` VALUES (211403, '龙港区', 211400, 3);
INSERT INTO `9h_sys_city` VALUES (211404, '南票区', 211400, 3);
INSERT INTO `9h_sys_city` VALUES (211421, '绥中县', 211400, 3);
INSERT INTO `9h_sys_city` VALUES (211422, '建昌县', 211400, 3);
INSERT INTO `9h_sys_city` VALUES (211481, '兴城市', 211400, 3);
INSERT INTO `9h_sys_city` VALUES (220000, '吉林省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (220100, '长春市', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (220102, '南关区', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220103, '宽城区', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220104, '朝阳区', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220105, '二道区', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220106, '绿园区', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220112, '双阳区', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220113, '九台区', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220122, '农安县', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220182, '榆树市', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220183, '德惠市', 220100, 3);
INSERT INTO `9h_sys_city` VALUES (220200, '吉林市', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (220202, '昌邑区', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220203, '龙潭区', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220204, '船营区', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220211, '丰满区', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220221, '永吉县', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220281, '蛟河市', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220282, '桦甸市', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220283, '舒兰市', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220284, '磐石市', 220200, 3);
INSERT INTO `9h_sys_city` VALUES (220300, '四平市', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (220302, '铁西区', 220300, 3);
INSERT INTO `9h_sys_city` VALUES (220303, '铁东区', 220300, 3);
INSERT INTO `9h_sys_city` VALUES (220322, '梨树县', 220300, 3);
INSERT INTO `9h_sys_city` VALUES (220323, '伊通满族自治县', 220300, 3);
INSERT INTO `9h_sys_city` VALUES (220381, '公主岭市', 220300, 3);
INSERT INTO `9h_sys_city` VALUES (220382, '双辽市', 220300, 3);
INSERT INTO `9h_sys_city` VALUES (220400, '辽源市', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (220402, '龙山区', 220400, 3);
INSERT INTO `9h_sys_city` VALUES (220403, '西安区', 220400, 3);
INSERT INTO `9h_sys_city` VALUES (220421, '东丰县', 220400, 3);
INSERT INTO `9h_sys_city` VALUES (220422, '东辽县', 220400, 3);
INSERT INTO `9h_sys_city` VALUES (220500, '通化市', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (220502, '东昌区', 220500, 3);
INSERT INTO `9h_sys_city` VALUES (220503, '二道江区', 220500, 3);
INSERT INTO `9h_sys_city` VALUES (220521, '通化县', 220500, 3);
INSERT INTO `9h_sys_city` VALUES (220523, '辉南县', 220500, 3);
INSERT INTO `9h_sys_city` VALUES (220524, '柳河县', 220500, 3);
INSERT INTO `9h_sys_city` VALUES (220581, '梅河口市', 220500, 3);
INSERT INTO `9h_sys_city` VALUES (220582, '集安市', 220500, 3);
INSERT INTO `9h_sys_city` VALUES (220600, '白山市', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (220602, '浑江区', 220600, 3);
INSERT INTO `9h_sys_city` VALUES (220605, '江源区', 220600, 3);
INSERT INTO `9h_sys_city` VALUES (220621, '抚松县', 220600, 3);
INSERT INTO `9h_sys_city` VALUES (220622, '靖宇县', 220600, 3);
INSERT INTO `9h_sys_city` VALUES (220623, '长白朝鲜族自治县', 220600, 3);
INSERT INTO `9h_sys_city` VALUES (220681, '临江市', 220600, 3);
INSERT INTO `9h_sys_city` VALUES (220700, '松原市', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (220702, '宁江区', 220700, 3);
INSERT INTO `9h_sys_city` VALUES (220721, '前郭尔罗斯蒙古族自治县', 220700, 3);
INSERT INTO `9h_sys_city` VALUES (220722, '长岭县', 220700, 3);
INSERT INTO `9h_sys_city` VALUES (220723, '乾安县', 220700, 3);
INSERT INTO `9h_sys_city` VALUES (220781, '扶余市', 220700, 3);
INSERT INTO `9h_sys_city` VALUES (220800, '白城市', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (220802, '洮北区', 220800, 3);
INSERT INTO `9h_sys_city` VALUES (220821, '镇赉县', 220800, 3);
INSERT INTO `9h_sys_city` VALUES (220822, '通榆县', 220800, 3);
INSERT INTO `9h_sys_city` VALUES (220881, '洮南市', 220800, 3);
INSERT INTO `9h_sys_city` VALUES (220882, '大安市', 220800, 3);
INSERT INTO `9h_sys_city` VALUES (222400, '延边朝鲜族自治州', 220000, 2);
INSERT INTO `9h_sys_city` VALUES (222401, '延吉市', 222400, 3);
INSERT INTO `9h_sys_city` VALUES (222402, '图们市', 222400, 3);
INSERT INTO `9h_sys_city` VALUES (222403, '敦化市', 222400, 3);
INSERT INTO `9h_sys_city` VALUES (222404, '珲春市', 222400, 3);
INSERT INTO `9h_sys_city` VALUES (222405, '龙井市', 222400, 3);
INSERT INTO `9h_sys_city` VALUES (222406, '和龙市', 222400, 3);
INSERT INTO `9h_sys_city` VALUES (222424, '汪清县', 222400, 3);
INSERT INTO `9h_sys_city` VALUES (222426, '安图县', 222400, 3);
INSERT INTO `9h_sys_city` VALUES (230000, '黑龙江省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (230100, '哈尔滨市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230102, '道里区', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230103, '南岗区', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230104, '道外区', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230108, '平房区', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230109, '松北区', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230110, '香坊区', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230111, '呼兰区', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230112, '阿城区', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230123, '依兰县', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230124, '方正县', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230125, '宾县', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230126, '巴彦县', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230127, '木兰县', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230128, '通河县', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230129, '延寿县', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230182, '双城市', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230183, '尚志市', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230184, '五常市', 230100, 3);
INSERT INTO `9h_sys_city` VALUES (230200, '齐齐哈尔市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230202, '龙沙区', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230203, '建华区', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230204, '铁锋区', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230205, '昂昂溪区', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230206, '富拉尔基区', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230207, '碾子山区', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230208, '梅里斯达斡尔族区', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230221, '龙江县', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230223, '依安县', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230224, '泰来县', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230225, '甘南县', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230227, '富裕县', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230229, '克山县', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230230, '克东县', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230231, '拜泉县', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230281, '讷河市', 230200, 3);
INSERT INTO `9h_sys_city` VALUES (230300, '鸡西市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230302, '鸡冠区', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230303, '恒山区', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230304, '滴道区', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230305, '梨树区', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230306, '城子河区', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230307, '麻山区', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230321, '鸡东县', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230381, '虎林市', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230382, '密山市', 230300, 3);
INSERT INTO `9h_sys_city` VALUES (230400, '鹤岗市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230402, '向阳区', 230400, 3);
INSERT INTO `9h_sys_city` VALUES (230403, '工农区', 230400, 3);
INSERT INTO `9h_sys_city` VALUES (230404, '南山区', 230400, 3);
INSERT INTO `9h_sys_city` VALUES (230405, '兴安区', 230400, 3);
INSERT INTO `9h_sys_city` VALUES (230406, '东山区', 230400, 3);
INSERT INTO `9h_sys_city` VALUES (230407, '兴山区', 230400, 3);
INSERT INTO `9h_sys_city` VALUES (230421, '萝北县', 230400, 3);
INSERT INTO `9h_sys_city` VALUES (230422, '绥滨县', 230400, 3);
INSERT INTO `9h_sys_city` VALUES (230500, '双鸭山市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230502, '尖山区', 230500, 3);
INSERT INTO `9h_sys_city` VALUES (230503, '岭东区', 230500, 3);
INSERT INTO `9h_sys_city` VALUES (230505, '四方台区', 230500, 3);
INSERT INTO `9h_sys_city` VALUES (230506, '宝山区', 230500, 3);
INSERT INTO `9h_sys_city` VALUES (230521, '集贤县', 230500, 3);
INSERT INTO `9h_sys_city` VALUES (230522, '友谊县', 230500, 3);
INSERT INTO `9h_sys_city` VALUES (230523, '宝清县', 230500, 3);
INSERT INTO `9h_sys_city` VALUES (230524, '饶河县', 230500, 3);
INSERT INTO `9h_sys_city` VALUES (230600, '大庆市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230602, '萨尔图区', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230603, '龙凤区', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230604, '让胡路区', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230605, '红岗区', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230606, '大同区', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230621, '肇州县', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230622, '肇源县', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230623, '林甸县', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230624, '杜尔伯特蒙古族自治县', 230600, 3);
INSERT INTO `9h_sys_city` VALUES (230700, '伊春市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230702, '伊春区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230703, '南岔区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230704, '友好区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230705, '西林区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230706, '翠峦区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230707, '新青区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230708, '美溪区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230709, '金山屯区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230710, '五营区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230711, '乌马河区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230712, '汤旺河区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230713, '带岭区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230714, '乌伊岭区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230715, '红星区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230716, '上甘岭区', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230722, '嘉荫县', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230781, '铁力市', 230700, 3);
INSERT INTO `9h_sys_city` VALUES (230800, '佳木斯市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230803, '向阳区', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230804, '前进区', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230805, '东风区', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230811, '郊区', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230822, '桦南县', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230826, '桦川县', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230828, '汤原县', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230833, '抚远县', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230881, '同江市', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230882, '富锦市', 230800, 3);
INSERT INTO `9h_sys_city` VALUES (230900, '七台河市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (230902, '新兴区', 230900, 3);
INSERT INTO `9h_sys_city` VALUES (230903, '桃山区', 230900, 3);
INSERT INTO `9h_sys_city` VALUES (230904, '茄子河区', 230900, 3);
INSERT INTO `9h_sys_city` VALUES (230921, '勃利县', 230900, 3);
INSERT INTO `9h_sys_city` VALUES (231000, '牡丹江市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (231002, '东安区', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231003, '阳明区', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231004, '爱民区', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231005, '西安区', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231024, '东宁县', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231025, '林口县', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231081, '绥芬河市', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231083, '海林市', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231084, '宁安市', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231085, '穆棱市', 231000, 3);
INSERT INTO `9h_sys_city` VALUES (231100, '黑河市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (231102, '爱辉区', 231100, 3);
INSERT INTO `9h_sys_city` VALUES (231121, '嫩江县', 231100, 3);
INSERT INTO `9h_sys_city` VALUES (231123, '逊克县', 231100, 3);
INSERT INTO `9h_sys_city` VALUES (231124, '孙吴县', 231100, 3);
INSERT INTO `9h_sys_city` VALUES (231181, '北安市', 231100, 3);
INSERT INTO `9h_sys_city` VALUES (231182, '五大连池市', 231100, 3);
INSERT INTO `9h_sys_city` VALUES (231200, '绥化市', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (231202, '北林区', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231221, '望奎县', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231222, '兰西县', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231223, '青冈县', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231224, '庆安县', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231225, '明水县', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231226, '绥棱县', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231281, '安达市', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231282, '肇东市', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (231283, '海伦市', 231200, 3);
INSERT INTO `9h_sys_city` VALUES (232700, '大兴安岭地区', 230000, 2);
INSERT INTO `9h_sys_city` VALUES (232721, '呼玛县', 232700, 3);
INSERT INTO `9h_sys_city` VALUES (232722, '塔河县', 232700, 3);
INSERT INTO `9h_sys_city` VALUES (232723, '漠河县', 232700, 3);
INSERT INTO `9h_sys_city` VALUES (310000, '上海市', 0, 1);
INSERT INTO `9h_sys_city` VALUES (310100, '上海市(市区)', 310000, 2);
INSERT INTO `9h_sys_city` VALUES (310101, '黄浦区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310104, '徐汇区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310105, '长宁区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310106, '静安区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310107, '普陀区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310108, '闸北区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310109, '虹口区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310110, '杨浦区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310112, '闵行区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310113, '宝山区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310114, '嘉定区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310115, '浦东新区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310116, '金山区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310117, '松江区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310118, '青浦区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310120, '奉贤区', 310100, 3);
INSERT INTO `9h_sys_city` VALUES (310200, '上海市 (县)', 310000, 2);
INSERT INTO `9h_sys_city` VALUES (310230, '崇明县', 310200, 3);
INSERT INTO `9h_sys_city` VALUES (320000, '江苏省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (320100, '南京市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320102, '玄武区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320104, '秦淮区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320105, '建邺区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320106, '鼓楼区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320111, '浦口区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320113, '栖霞区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320114, '雨花台区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320115, '江宁区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320116, '六合区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320117, '溧水区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320118, '高淳区', 320100, 3);
INSERT INTO `9h_sys_city` VALUES (320200, '无锡市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320202, '崇安区', 320200, 3);
INSERT INTO `9h_sys_city` VALUES (320203, '南长区', 320200, 3);
INSERT INTO `9h_sys_city` VALUES (320204, '北塘区', 320200, 3);
INSERT INTO `9h_sys_city` VALUES (320205, '锡山区', 320200, 3);
INSERT INTO `9h_sys_city` VALUES (320206, '惠山区', 320200, 3);
INSERT INTO `9h_sys_city` VALUES (320211, '滨湖区', 320200, 3);
INSERT INTO `9h_sys_city` VALUES (320281, '江阴市', 320200, 3);
INSERT INTO `9h_sys_city` VALUES (320282, '宜兴市', 320200, 3);
INSERT INTO `9h_sys_city` VALUES (320300, '徐州市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320302, '鼓楼区', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320303, '云龙区', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320305, '贾汪区', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320311, '泉山区', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320312, '铜山区', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320321, '丰县', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320322, '沛县', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320324, '睢宁县', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320381, '新沂市', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320382, '邳州市', 320300, 3);
INSERT INTO `9h_sys_city` VALUES (320400, '常州市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320402, '天宁区', 320400, 3);
INSERT INTO `9h_sys_city` VALUES (320404, '钟楼区', 320400, 3);
INSERT INTO `9h_sys_city` VALUES (320405, '戚墅堰区', 320400, 3);
INSERT INTO `9h_sys_city` VALUES (320411, '新北区', 320400, 3);
INSERT INTO `9h_sys_city` VALUES (320412, '武进区', 320400, 3);
INSERT INTO `9h_sys_city` VALUES (320481, '溧阳市', 320400, 3);
INSERT INTO `9h_sys_city` VALUES (320482, '金坛市', 320400, 3);
INSERT INTO `9h_sys_city` VALUES (320500, '苏州市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320505, '虎丘区', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320506, '吴中区', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320507, '相城区', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320508, '姑苏区', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320509, '吴江区', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320581, '常熟市', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320582, '张家港市', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320583, '昆山市', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320585, '太仓市', 320500, 3);
INSERT INTO `9h_sys_city` VALUES (320600, '南通市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320602, '崇川区', 320600, 3);
INSERT INTO `9h_sys_city` VALUES (320611, '港闸区', 320600, 3);
INSERT INTO `9h_sys_city` VALUES (320612, '通州区', 320600, 3);
INSERT INTO `9h_sys_city` VALUES (320621, '海安县', 320600, 3);
INSERT INTO `9h_sys_city` VALUES (320623, '如东县', 320600, 3);
INSERT INTO `9h_sys_city` VALUES (320681, '启东市', 320600, 3);
INSERT INTO `9h_sys_city` VALUES (320682, '如皋市', 320600, 3);
INSERT INTO `9h_sys_city` VALUES (320684, '海门市', 320600, 3);
INSERT INTO `9h_sys_city` VALUES (320700, '连云港市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320703, '连云区', 320700, 3);
INSERT INTO `9h_sys_city` VALUES (320706, '海州区', 320700, 3);
INSERT INTO `9h_sys_city` VALUES (320707, '赣榆区', 320700, 3);
INSERT INTO `9h_sys_city` VALUES (320722, '东海县', 320700, 3);
INSERT INTO `9h_sys_city` VALUES (320723, '灌云县', 320700, 3);
INSERT INTO `9h_sys_city` VALUES (320724, '灌南县', 320700, 3);
INSERT INTO `9h_sys_city` VALUES (320800, '淮安市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320802, '清河区', 320800, 3);
INSERT INTO `9h_sys_city` VALUES (320803, '淮安区', 320800, 3);
INSERT INTO `9h_sys_city` VALUES (320804, '淮阴区', 320800, 3);
INSERT INTO `9h_sys_city` VALUES (320811, '清浦区', 320800, 3);
INSERT INTO `9h_sys_city` VALUES (320826, '涟水县', 320800, 3);
INSERT INTO `9h_sys_city` VALUES (320829, '洪泽县', 320800, 3);
INSERT INTO `9h_sys_city` VALUES (320830, '盱眙县', 320800, 3);
INSERT INTO `9h_sys_city` VALUES (320831, '金湖县', 320800, 3);
INSERT INTO `9h_sys_city` VALUES (320900, '盐城市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (320902, '亭湖区', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (320903, '盐都区', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (320921, '响水县', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (320922, '滨海县', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (320923, '阜宁县', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (320924, '射阳县', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (320925, '建湖县', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (320981, '东台市', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (320982, '大丰市', 320900, 3);
INSERT INTO `9h_sys_city` VALUES (321000, '扬州市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (321002, '广陵区', 321000, 3);
INSERT INTO `9h_sys_city` VALUES (321003, '邗江区', 321000, 3);
INSERT INTO `9h_sys_city` VALUES (321012, '江都区', 321000, 3);
INSERT INTO `9h_sys_city` VALUES (321023, '宝应县', 321000, 3);
INSERT INTO `9h_sys_city` VALUES (321081, '仪征市', 321000, 3);
INSERT INTO `9h_sys_city` VALUES (321084, '高邮市', 321000, 3);
INSERT INTO `9h_sys_city` VALUES (321100, '镇江市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (321102, '京口区', 321100, 3);
INSERT INTO `9h_sys_city` VALUES (321111, '润州区', 321100, 3);
INSERT INTO `9h_sys_city` VALUES (321112, '丹徒区', 321100, 3);
INSERT INTO `9h_sys_city` VALUES (321181, '丹阳市', 321100, 3);
INSERT INTO `9h_sys_city` VALUES (321182, '扬中市', 321100, 3);
INSERT INTO `9h_sys_city` VALUES (321183, '句容市', 321100, 3);
INSERT INTO `9h_sys_city` VALUES (321200, '泰州市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (321202, '海陵区', 321200, 3);
INSERT INTO `9h_sys_city` VALUES (321203, '高港区', 321200, 3);
INSERT INTO `9h_sys_city` VALUES (321204, '姜堰区', 321200, 3);
INSERT INTO `9h_sys_city` VALUES (321281, '兴化市', 321200, 3);
INSERT INTO `9h_sys_city` VALUES (321282, '靖江市', 321200, 3);
INSERT INTO `9h_sys_city` VALUES (321283, '泰兴市', 321200, 3);
INSERT INTO `9h_sys_city` VALUES (321300, '宿迁市', 320000, 2);
INSERT INTO `9h_sys_city` VALUES (321302, '宿城区', 321300, 3);
INSERT INTO `9h_sys_city` VALUES (321311, '宿豫区', 321300, 3);
INSERT INTO `9h_sys_city` VALUES (321322, '沭阳县', 321300, 3);
INSERT INTO `9h_sys_city` VALUES (321323, '泗阳县', 321300, 3);
INSERT INTO `9h_sys_city` VALUES (321324, '泗洪县', 321300, 3);
INSERT INTO `9h_sys_city` VALUES (330000, '浙江省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (330100, '杭州市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330102, '上城区', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330103, '下城区', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330104, '江干区', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330105, '拱墅区', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330106, '西湖区', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330108, '滨江区', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330109, '萧山区', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330110, '余杭区', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330122, '桐庐县', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330127, '淳安县', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330182, '建德市', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330183, '富阳市', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330185, '临安市', 330100, 3);
INSERT INTO `9h_sys_city` VALUES (330200, '宁波市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330203, '海曙区', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330204, '江东区', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330205, '江北区', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330206, '北仑区', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330211, '镇海区', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330212, '鄞州区', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330225, '象山县', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330226, '宁海县', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330281, '余姚市', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330282, '慈溪市', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330283, '奉化市', 330200, 3);
INSERT INTO `9h_sys_city` VALUES (330300, '温州市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330302, '鹿城区', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330303, '龙湾区', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330304, '瓯海区', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330322, '洞头县', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330324, '永嘉县', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330326, '平阳县', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330327, '苍南县', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330328, '文成县', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330329, '泰顺县', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330381, '瑞安市', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330382, '乐清市', 330300, 3);
INSERT INTO `9h_sys_city` VALUES (330400, '嘉兴市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330402, '南湖区', 330400, 3);
INSERT INTO `9h_sys_city` VALUES (330411, '秀洲区', 330400, 3);
INSERT INTO `9h_sys_city` VALUES (330421, '嘉善县', 330400, 3);
INSERT INTO `9h_sys_city` VALUES (330424, '海盐县', 330400, 3);
INSERT INTO `9h_sys_city` VALUES (330481, '海宁市', 330400, 3);
INSERT INTO `9h_sys_city` VALUES (330482, '平湖市', 330400, 3);
INSERT INTO `9h_sys_city` VALUES (330483, '桐乡市', 330400, 3);
INSERT INTO `9h_sys_city` VALUES (330500, '湖州市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330502, '吴兴区', 330500, 3);
INSERT INTO `9h_sys_city` VALUES (330503, '南浔区', 330500, 3);
INSERT INTO `9h_sys_city` VALUES (330521, '德清县', 330500, 3);
INSERT INTO `9h_sys_city` VALUES (330522, '长兴县', 330500, 3);
INSERT INTO `9h_sys_city` VALUES (330523, '安吉县', 330500, 3);
INSERT INTO `9h_sys_city` VALUES (330600, '绍兴市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330602, '越城区', 330600, 3);
INSERT INTO `9h_sys_city` VALUES (330603, '柯桥区', 330600, 3);
INSERT INTO `9h_sys_city` VALUES (330604, '上虞区', 330600, 3);
INSERT INTO `9h_sys_city` VALUES (330624, '新昌县', 330600, 3);
INSERT INTO `9h_sys_city` VALUES (330681, '诸暨市', 330600, 3);
INSERT INTO `9h_sys_city` VALUES (330683, '嵊州市', 330600, 3);
INSERT INTO `9h_sys_city` VALUES (330700, '金华市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330702, '婺城区', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330703, '金东区', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330723, '武义县', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330726, '浦江县', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330727, '磐安县', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330781, '兰溪市', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330782, '义乌市', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330783, '东阳市', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330784, '永康市', 330700, 3);
INSERT INTO `9h_sys_city` VALUES (330800, '衢州市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330802, '柯城区', 330800, 3);
INSERT INTO `9h_sys_city` VALUES (330803, '衢江区', 330800, 3);
INSERT INTO `9h_sys_city` VALUES (330822, '常山县', 330800, 3);
INSERT INTO `9h_sys_city` VALUES (330824, '开化县', 330800, 3);
INSERT INTO `9h_sys_city` VALUES (330825, '龙游县', 330800, 3);
INSERT INTO `9h_sys_city` VALUES (330881, '江山市', 330800, 3);
INSERT INTO `9h_sys_city` VALUES (330900, '舟山市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (330902, '定海区', 330900, 3);
INSERT INTO `9h_sys_city` VALUES (330903, '普陀区', 330900, 3);
INSERT INTO `9h_sys_city` VALUES (330921, '岱山县', 330900, 3);
INSERT INTO `9h_sys_city` VALUES (330922, '嵊泗县', 330900, 3);
INSERT INTO `9h_sys_city` VALUES (331000, '台州市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (331002, '椒江区', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331003, '黄岩区', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331004, '路桥区', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331021, '玉环县', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331022, '三门县', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331023, '天台县', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331024, '仙居县', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331081, '温岭市', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331082, '临海市', 331000, 3);
INSERT INTO `9h_sys_city` VALUES (331100, '丽水市', 330000, 2);
INSERT INTO `9h_sys_city` VALUES (331102, '莲都区', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (331121, '青田县', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (331122, '缙云县', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (331123, '遂昌县', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (331124, '松阳县', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (331125, '云和县', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (331126, '庆元县', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (331127, '景宁畲族自治县', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (331181, '龙泉市', 331100, 3);
INSERT INTO `9h_sys_city` VALUES (340000, '安徽省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (340100, '合肥市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (340102, '瑶海区', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340103, '庐阳区', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340104, '蜀山区', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340111, '包河区', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340121, '长丰县', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340122, '肥东县', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340123, '肥西县', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340124, '庐江县', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340181, '巢湖市', 340100, 3);
INSERT INTO `9h_sys_city` VALUES (340200, '芜湖市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (340202, '镜湖区', 340200, 3);
INSERT INTO `9h_sys_city` VALUES (340203, '弋江区', 340200, 3);
INSERT INTO `9h_sys_city` VALUES (340207, '鸠江区', 340200, 3);
INSERT INTO `9h_sys_city` VALUES (340208, '三山区', 340200, 3);
INSERT INTO `9h_sys_city` VALUES (340221, '芜湖县', 340200, 3);
INSERT INTO `9h_sys_city` VALUES (340222, '繁昌县', 340200, 3);
INSERT INTO `9h_sys_city` VALUES (340223, '南陵县', 340200, 3);
INSERT INTO `9h_sys_city` VALUES (340225, '无为县', 340200, 3);
INSERT INTO `9h_sys_city` VALUES (340300, '蚌埠市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (340302, '龙子湖区', 340300, 3);
INSERT INTO `9h_sys_city` VALUES (340303, '蚌山区', 340300, 3);
INSERT INTO `9h_sys_city` VALUES (340304, '禹会区', 340300, 3);
INSERT INTO `9h_sys_city` VALUES (340311, '淮上区', 340300, 3);
INSERT INTO `9h_sys_city` VALUES (340321, '怀远县', 340300, 3);
INSERT INTO `9h_sys_city` VALUES (340322, '五河县', 340300, 3);
INSERT INTO `9h_sys_city` VALUES (340323, '固镇县', 340300, 3);
INSERT INTO `9h_sys_city` VALUES (340400, '淮南市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (340402, '大通区', 340400, 3);
INSERT INTO `9h_sys_city` VALUES (340403, '田家庵区', 340400, 3);
INSERT INTO `9h_sys_city` VALUES (340404, '谢家集区', 340400, 3);
INSERT INTO `9h_sys_city` VALUES (340405, '八公山区', 340400, 3);
INSERT INTO `9h_sys_city` VALUES (340406, '潘集区', 340400, 3);
INSERT INTO `9h_sys_city` VALUES (340421, '凤台县', 340400, 3);
INSERT INTO `9h_sys_city` VALUES (340500, '马鞍山市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (340503, '花山区', 340500, 3);
INSERT INTO `9h_sys_city` VALUES (340504, '雨山区', 340500, 3);
INSERT INTO `9h_sys_city` VALUES (340506, '博望区', 340500, 3);
INSERT INTO `9h_sys_city` VALUES (340521, '当涂县', 340500, 3);
INSERT INTO `9h_sys_city` VALUES (340522, '含山县', 340500, 3);
INSERT INTO `9h_sys_city` VALUES (340523, '和县', 340500, 3);
INSERT INTO `9h_sys_city` VALUES (340600, '淮北市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (340602, '杜集区', 340600, 3);
INSERT INTO `9h_sys_city` VALUES (340603, '相山区', 340600, 3);
INSERT INTO `9h_sys_city` VALUES (340604, '烈山区', 340600, 3);
INSERT INTO `9h_sys_city` VALUES (340621, '濉溪县', 340600, 3);
INSERT INTO `9h_sys_city` VALUES (340700, '铜陵市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (340702, '铜官山区', 340700, 3);
INSERT INTO `9h_sys_city` VALUES (340703, '狮子山区', 340700, 3);
INSERT INTO `9h_sys_city` VALUES (340711, '郊区', 340700, 3);
INSERT INTO `9h_sys_city` VALUES (340721, '铜陵县', 340700, 3);
INSERT INTO `9h_sys_city` VALUES (340800, '安庆市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (340802, '迎江区', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340803, '大观区', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340811, '宜秀区', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340822, '怀宁县', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340823, '枞阳县', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340824, '潜山县', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340825, '太湖县', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340826, '宿松县', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340827, '望江县', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340828, '岳西县', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (340881, '桐城市', 340800, 3);
INSERT INTO `9h_sys_city` VALUES (341000, '黄山市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (341002, '屯溪区', 341000, 3);
INSERT INTO `9h_sys_city` VALUES (341003, '黄山区', 341000, 3);
INSERT INTO `9h_sys_city` VALUES (341004, '徽州区', 341000, 3);
INSERT INTO `9h_sys_city` VALUES (341021, '歙县', 341000, 3);
INSERT INTO `9h_sys_city` VALUES (341022, '休宁县', 341000, 3);
INSERT INTO `9h_sys_city` VALUES (341023, '黟县', 341000, 3);
INSERT INTO `9h_sys_city` VALUES (341024, '祁门县', 341000, 3);
INSERT INTO `9h_sys_city` VALUES (341100, '滁州市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (341102, '琅琊区', 341100, 3);
INSERT INTO `9h_sys_city` VALUES (341103, '南谯区', 341100, 3);
INSERT INTO `9h_sys_city` VALUES (341122, '来安县', 341100, 3);
INSERT INTO `9h_sys_city` VALUES (341124, '全椒县', 341100, 3);
INSERT INTO `9h_sys_city` VALUES (341125, '定远县', 341100, 3);
INSERT INTO `9h_sys_city` VALUES (341126, '凤阳县', 341100, 3);
INSERT INTO `9h_sys_city` VALUES (341181, '天长市', 341100, 3);
INSERT INTO `9h_sys_city` VALUES (341182, '明光市', 341100, 3);
INSERT INTO `9h_sys_city` VALUES (341200, '阜阳市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (341202, '颍州区', 341200, 3);
INSERT INTO `9h_sys_city` VALUES (341203, '颍东区', 341200, 3);
INSERT INTO `9h_sys_city` VALUES (341204, '颍泉区', 341200, 3);
INSERT INTO `9h_sys_city` VALUES (341221, '临泉县', 341200, 3);
INSERT INTO `9h_sys_city` VALUES (341222, '太和县', 341200, 3);
INSERT INTO `9h_sys_city` VALUES (341225, '阜南县', 341200, 3);
INSERT INTO `9h_sys_city` VALUES (341226, '颍上县', 341200, 3);
INSERT INTO `9h_sys_city` VALUES (341282, '界首市', 341200, 3);
INSERT INTO `9h_sys_city` VALUES (341300, '宿州市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (341302, '埇桥区', 341300, 3);
INSERT INTO `9h_sys_city` VALUES (341321, '砀山县', 341300, 3);
INSERT INTO `9h_sys_city` VALUES (341322, '萧县', 341300, 3);
INSERT INTO `9h_sys_city` VALUES (341323, '灵璧县', 341300, 3);
INSERT INTO `9h_sys_city` VALUES (341324, '泗县', 341300, 3);
INSERT INTO `9h_sys_city` VALUES (341500, '六安市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (341502, '金安区', 341500, 3);
INSERT INTO `9h_sys_city` VALUES (341503, '裕安区', 341500, 3);
INSERT INTO `9h_sys_city` VALUES (341521, '寿县', 341500, 3);
INSERT INTO `9h_sys_city` VALUES (341522, '霍邱县', 341500, 3);
INSERT INTO `9h_sys_city` VALUES (341523, '舒城县', 341500, 3);
INSERT INTO `9h_sys_city` VALUES (341524, '金寨县', 341500, 3);
INSERT INTO `9h_sys_city` VALUES (341525, '霍山县', 341500, 3);
INSERT INTO `9h_sys_city` VALUES (341600, '亳州市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (341602, '谯城区', 341600, 3);
INSERT INTO `9h_sys_city` VALUES (341621, '涡阳县', 341600, 3);
INSERT INTO `9h_sys_city` VALUES (341622, '蒙城县', 341600, 3);
INSERT INTO `9h_sys_city` VALUES (341623, '利辛县', 341600, 3);
INSERT INTO `9h_sys_city` VALUES (341700, '池州市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (341702, '贵池区', 341700, 3);
INSERT INTO `9h_sys_city` VALUES (341721, '东至县', 341700, 3);
INSERT INTO `9h_sys_city` VALUES (341722, '石台县', 341700, 3);
INSERT INTO `9h_sys_city` VALUES (341723, '青阳县', 341700, 3);
INSERT INTO `9h_sys_city` VALUES (341800, '宣城市', 340000, 2);
INSERT INTO `9h_sys_city` VALUES (341802, '宣州区', 341800, 3);
INSERT INTO `9h_sys_city` VALUES (341821, '郎溪县', 341800, 3);
INSERT INTO `9h_sys_city` VALUES (341822, '广德县', 341800, 3);
INSERT INTO `9h_sys_city` VALUES (341823, '泾县', 341800, 3);
INSERT INTO `9h_sys_city` VALUES (341824, '绩溪县', 341800, 3);
INSERT INTO `9h_sys_city` VALUES (341825, '旌德县', 341800, 3);
INSERT INTO `9h_sys_city` VALUES (341881, '宁国市', 341800, 3);
INSERT INTO `9h_sys_city` VALUES (350000, '福建省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (350100, '福州市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350102, '鼓楼区', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350103, '台江区', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350104, '仓山区', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350105, '马尾区', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350111, '晋安区', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350121, '闽侯县', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350122, '连江县', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350123, '罗源县', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350124, '闽清县', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350125, '永泰县', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350128, '平潭县', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350181, '福清市', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350182, '长乐市', 350100, 3);
INSERT INTO `9h_sys_city` VALUES (350200, '厦门市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350203, '思明区', 350200, 3);
INSERT INTO `9h_sys_city` VALUES (350205, '海沧区', 350200, 3);
INSERT INTO `9h_sys_city` VALUES (350206, '湖里区', 350200, 3);
INSERT INTO `9h_sys_city` VALUES (350211, '集美区', 350200, 3);
INSERT INTO `9h_sys_city` VALUES (350212, '同安区', 350200, 3);
INSERT INTO `9h_sys_city` VALUES (350213, '翔安区', 350200, 3);
INSERT INTO `9h_sys_city` VALUES (350300, '莆田市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350302, '城厢区', 350300, 3);
INSERT INTO `9h_sys_city` VALUES (350303, '涵江区', 350300, 3);
INSERT INTO `9h_sys_city` VALUES (350304, '荔城区', 350300, 3);
INSERT INTO `9h_sys_city` VALUES (350305, '秀屿区', 350300, 3);
INSERT INTO `9h_sys_city` VALUES (350322, '仙游县', 350300, 3);
INSERT INTO `9h_sys_city` VALUES (350400, '三明市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350402, '梅列区', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350403, '三元区', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350421, '明溪县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350423, '清流县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350424, '宁化县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350425, '大田县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350426, '尤溪县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350427, '沙县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350428, '将乐县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350429, '泰宁县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350430, '建宁县', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350481, '永安市', 350400, 3);
INSERT INTO `9h_sys_city` VALUES (350500, '泉州市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350502, '鲤城区', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350503, '丰泽区', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350504, '洛江区', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350505, '泉港区', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350521, '惠安县', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350524, '安溪县', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350525, '永春县', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350526, '德化县', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350527, '金门县', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350581, '石狮市', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350582, '晋江市', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350583, '南安市', 350500, 3);
INSERT INTO `9h_sys_city` VALUES (350600, '漳州市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350602, '芗城区', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350603, '龙文区', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350622, '云霄县', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350623, '漳浦县', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350624, '诏安县', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350625, '长泰县', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350626, '东山县', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350627, '南靖县', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350628, '平和县', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350629, '华安县', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350681, '龙海市', 350600, 3);
INSERT INTO `9h_sys_city` VALUES (350700, '南平市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350702, '延平区', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350721, '顺昌县', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350722, '浦城县', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350723, '光泽县', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350724, '松溪县', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350725, '政和县', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350781, '邵武市', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350782, '武夷山市', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350783, '建瓯市', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350784, '建阳市', 350700, 3);
INSERT INTO `9h_sys_city` VALUES (350800, '龙岩市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350802, '新罗区', 350800, 3);
INSERT INTO `9h_sys_city` VALUES (350821, '长汀县', 350800, 3);
INSERT INTO `9h_sys_city` VALUES (350822, '永定县', 350800, 3);
INSERT INTO `9h_sys_city` VALUES (350823, '上杭县', 350800, 3);
INSERT INTO `9h_sys_city` VALUES (350824, '武平县', 350800, 3);
INSERT INTO `9h_sys_city` VALUES (350825, '连城县', 350800, 3);
INSERT INTO `9h_sys_city` VALUES (350881, '漳平市', 350800, 3);
INSERT INTO `9h_sys_city` VALUES (350900, '宁德市', 350000, 2);
INSERT INTO `9h_sys_city` VALUES (350902, '蕉城区', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (350921, '霞浦县', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (350922, '古田县', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (350923, '屏南县', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (350924, '寿宁县', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (350925, '周宁县', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (350926, '柘荣县', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (350981, '福安市', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (350982, '福鼎市', 350900, 3);
INSERT INTO `9h_sys_city` VALUES (360000, '江西省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (360100, '南昌市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360102, '东湖区', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360103, '西湖区', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360104, '青云谱区', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360105, '湾里区', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360111, '青山湖区', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360121, '南昌县', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360122, '新建县', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360123, '安义县', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360124, '进贤县', 360100, 3);
INSERT INTO `9h_sys_city` VALUES (360200, '景德镇市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360202, '昌江区', 360200, 3);
INSERT INTO `9h_sys_city` VALUES (360203, '珠山区', 360200, 3);
INSERT INTO `9h_sys_city` VALUES (360222, '浮梁县', 360200, 3);
INSERT INTO `9h_sys_city` VALUES (360281, '乐平市', 360200, 3);
INSERT INTO `9h_sys_city` VALUES (360300, '萍乡市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360302, '安源区', 360300, 3);
INSERT INTO `9h_sys_city` VALUES (360313, '湘东区', 360300, 3);
INSERT INTO `9h_sys_city` VALUES (360321, '莲花县', 360300, 3);
INSERT INTO `9h_sys_city` VALUES (360322, '上栗县', 360300, 3);
INSERT INTO `9h_sys_city` VALUES (360323, '芦溪县', 360300, 3);
INSERT INTO `9h_sys_city` VALUES (360400, '九江市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360402, '庐山区', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360403, '浔阳区', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360421, '九江县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360423, '武宁县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360424, '修水县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360425, '永修县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360426, '德安县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360427, '星子县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360428, '都昌县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360429, '湖口县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360430, '彭泽县', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360481, '瑞昌市', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360482, '共青城市', 360400, 3);
INSERT INTO `9h_sys_city` VALUES (360500, '新余市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360502, '渝水区', 360500, 3);
INSERT INTO `9h_sys_city` VALUES (360521, '分宜县', 360500, 3);
INSERT INTO `9h_sys_city` VALUES (360600, '鹰潭市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360602, '月湖区', 360600, 3);
INSERT INTO `9h_sys_city` VALUES (360622, '余江县', 360600, 3);
INSERT INTO `9h_sys_city` VALUES (360681, '贵溪市', 360600, 3);
INSERT INTO `9h_sys_city` VALUES (360700, '赣州市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360702, '章贡区', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360703, '南康区', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360721, '赣县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360722, '信丰县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360723, '大余县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360724, '上犹县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360725, '崇义县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360726, '安远县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360727, '龙南县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360728, '定南县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360729, '全南县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360730, '宁都县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360731, '于都县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360732, '兴国县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360733, '会昌县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360734, '寻乌县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360735, '石城县', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360781, '瑞金市', 360700, 3);
INSERT INTO `9h_sys_city` VALUES (360800, '吉安市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360802, '吉州区', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360803, '青原区', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360821, '吉安县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360822, '吉水县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360823, '峡江县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360824, '新干县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360825, '永丰县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360826, '泰和县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360827, '遂川县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360828, '万安县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360829, '安福县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360830, '永新县', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360881, '井冈山市', 360800, 3);
INSERT INTO `9h_sys_city` VALUES (360900, '宜春市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (360902, '袁州区', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360921, '奉新县', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360922, '万载县', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360923, '上高县', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360924, '宜丰县', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360925, '靖安县', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360926, '铜鼓县', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360981, '丰城市', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360982, '樟树市', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (360983, '高安市', 360900, 3);
INSERT INTO `9h_sys_city` VALUES (361000, '抚州市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (361002, '临川区', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361021, '南城县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361022, '黎川县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361023, '南丰县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361024, '崇仁县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361025, '乐安县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361026, '宜黄县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361027, '金溪县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361028, '资溪县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361029, '东乡县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361030, '广昌县', 361000, 3);
INSERT INTO `9h_sys_city` VALUES (361100, '上饶市', 360000, 2);
INSERT INTO `9h_sys_city` VALUES (361102, '信州区', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361121, '上饶县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361122, '广丰县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361123, '玉山县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361124, '铅山县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361125, '横峰县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361126, '弋阳县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361127, '余干县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361128, '鄱阳县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361129, '万年县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361130, '婺源县', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (361181, '德兴市', 361100, 3);
INSERT INTO `9h_sys_city` VALUES (370000, '山东省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (370100, '济南市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370102, '历下区', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370103, '市中区', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370104, '槐荫区', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370105, '天桥区', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370112, '历城区', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370113, '长清区', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370124, '平阴县', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370125, '济阳县', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370126, '商河县', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370181, '章丘市', 370100, 3);
INSERT INTO `9h_sys_city` VALUES (370200, '青岛市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370202, '市南区', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370203, '市北区', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370211, '黄岛区', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370212, '崂山区', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370213, '李沧区', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370214, '城阳区', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370281, '胶州市', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370282, '即墨市', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370283, '平度市', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370285, '莱西市', 370200, 3);
INSERT INTO `9h_sys_city` VALUES (370300, '淄博市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370302, '淄川区', 370300, 3);
INSERT INTO `9h_sys_city` VALUES (370303, '张店区', 370300, 3);
INSERT INTO `9h_sys_city` VALUES (370304, '博山区', 370300, 3);
INSERT INTO `9h_sys_city` VALUES (370305, '临淄区', 370300, 3);
INSERT INTO `9h_sys_city` VALUES (370306, '周村区', 370300, 3);
INSERT INTO `9h_sys_city` VALUES (370321, '桓台县', 370300, 3);
INSERT INTO `9h_sys_city` VALUES (370322, '高青县', 370300, 3);
INSERT INTO `9h_sys_city` VALUES (370323, '沂源县', 370300, 3);
INSERT INTO `9h_sys_city` VALUES (370400, '枣庄市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370402, '市中区', 370400, 3);
INSERT INTO `9h_sys_city` VALUES (370403, '薛城区', 370400, 3);
INSERT INTO `9h_sys_city` VALUES (370404, '峄城区', 370400, 3);
INSERT INTO `9h_sys_city` VALUES (370405, '台儿庄区', 370400, 3);
INSERT INTO `9h_sys_city` VALUES (370406, '山亭区', 370400, 3);
INSERT INTO `9h_sys_city` VALUES (370481, '滕州市', 370400, 3);
INSERT INTO `9h_sys_city` VALUES (370500, '东营市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370502, '东营区', 370500, 3);
INSERT INTO `9h_sys_city` VALUES (370503, '河口区', 370500, 3);
INSERT INTO `9h_sys_city` VALUES (370521, '垦利县', 370500, 3);
INSERT INTO `9h_sys_city` VALUES (370522, '利津县', 370500, 3);
INSERT INTO `9h_sys_city` VALUES (370523, '广饶县', 370500, 3);
INSERT INTO `9h_sys_city` VALUES (370600, '烟台市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370602, '芝罘区', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370611, '福山区', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370612, '牟平区', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370613, '莱山区', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370634, '长岛县', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370681, '龙口市', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370682, '莱阳市', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370683, '莱州市', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370684, '蓬莱市', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370685, '招远市', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370686, '栖霞市', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370687, '海阳市', 370600, 3);
INSERT INTO `9h_sys_city` VALUES (370700, '潍坊市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370702, '潍城区', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370703, '寒亭区', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370704, '坊子区', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370705, '奎文区', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370724, '临朐县', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370725, '昌乐县', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370781, '青州市', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370782, '诸城市', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370783, '寿光市', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370784, '安丘市', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370785, '高密市', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370786, '昌邑市', 370700, 3);
INSERT INTO `9h_sys_city` VALUES (370800, '济宁市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370811, '任城区', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370812, '兖州区', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370826, '微山县', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370827, '鱼台县', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370828, '金乡县', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370829, '嘉祥县', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370830, '汶上县', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370831, '泗水县', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370832, '梁山县', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370881, '曲阜市', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370883, '邹城市', 370800, 3);
INSERT INTO `9h_sys_city` VALUES (370900, '泰安市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (370902, '泰山区', 370900, 3);
INSERT INTO `9h_sys_city` VALUES (370911, '岱岳区', 370900, 3);
INSERT INTO `9h_sys_city` VALUES (370921, '宁阳县', 370900, 3);
INSERT INTO `9h_sys_city` VALUES (370923, '东平县', 370900, 3);
INSERT INTO `9h_sys_city` VALUES (370982, '新泰市', 370900, 3);
INSERT INTO `9h_sys_city` VALUES (370983, '肥城市', 370900, 3);
INSERT INTO `9h_sys_city` VALUES (371000, '威海市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (371002, '环翠区', 371000, 3);
INSERT INTO `9h_sys_city` VALUES (371003, '文登区', 371000, 3);
INSERT INTO `9h_sys_city` VALUES (371082, '荣成市', 371000, 3);
INSERT INTO `9h_sys_city` VALUES (371083, '乳山市', 371000, 3);
INSERT INTO `9h_sys_city` VALUES (371100, '日照市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (371102, '东港区', 371100, 3);
INSERT INTO `9h_sys_city` VALUES (371103, '岚山区', 371100, 3);
INSERT INTO `9h_sys_city` VALUES (371121, '五莲县', 371100, 3);
INSERT INTO `9h_sys_city` VALUES (371122, '莒县', 371100, 3);
INSERT INTO `9h_sys_city` VALUES (371200, '莱芜市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (371202, '莱城区', 371200, 3);
INSERT INTO `9h_sys_city` VALUES (371203, '钢城区', 371200, 3);
INSERT INTO `9h_sys_city` VALUES (371300, '临沂市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (371302, '兰山区', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371311, '罗庄区', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371312, '河东区', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371321, '沂南县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371322, '郯城县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371323, '沂水县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371324, '兰陵县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371325, '费县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371326, '平邑县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371327, '莒南县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371328, '蒙阴县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371329, '临沭县', 371300, 3);
INSERT INTO `9h_sys_city` VALUES (371400, '德州市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (371402, '德城区', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371403, '陵城区', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371422, '宁津县', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371423, '庆云县', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371424, '临邑县', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371425, '齐河县', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371426, '平原县', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371427, '夏津县', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371428, '武城县', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371481, '乐陵市', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371482, '禹城市', 371400, 3);
INSERT INTO `9h_sys_city` VALUES (371500, '聊城市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (371502, '东昌府区', 371500, 3);
INSERT INTO `9h_sys_city` VALUES (371521, '阳谷县', 371500, 3);
INSERT INTO `9h_sys_city` VALUES (371522, '莘县', 371500, 3);
INSERT INTO `9h_sys_city` VALUES (371523, '茌平县', 371500, 3);
INSERT INTO `9h_sys_city` VALUES (371524, '东阿县', 371500, 3);
INSERT INTO `9h_sys_city` VALUES (371525, '冠县', 371500, 3);
INSERT INTO `9h_sys_city` VALUES (371526, '高唐县', 371500, 3);
INSERT INTO `9h_sys_city` VALUES (371581, '临清市', 371500, 3);
INSERT INTO `9h_sys_city` VALUES (371600, '滨州市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (371602, '滨城区', 371600, 3);
INSERT INTO `9h_sys_city` VALUES (371603, '沾化区', 371600, 3);
INSERT INTO `9h_sys_city` VALUES (371621, '惠民县', 371600, 3);
INSERT INTO `9h_sys_city` VALUES (371622, '阳信县', 371600, 3);
INSERT INTO `9h_sys_city` VALUES (371623, '无棣县', 371600, 3);
INSERT INTO `9h_sys_city` VALUES (371625, '博兴县', 371600, 3);
INSERT INTO `9h_sys_city` VALUES (371626, '邹平县', 371600, 3);
INSERT INTO `9h_sys_city` VALUES (371700, '菏泽市', 370000, 2);
INSERT INTO `9h_sys_city` VALUES (371702, '牡丹区', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (371721, '曹县', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (371722, '单县', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (371723, '成武县', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (371724, '巨野县', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (371725, '郓城县', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (371726, '鄄城县', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (371727, '定陶县', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (371728, '东明县', 371700, 3);
INSERT INTO `9h_sys_city` VALUES (410000, '河南省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (410100, '郑州市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410102, '中原区', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410103, '二七区', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410104, '管城回族区', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410105, '金水区', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410106, '上街区', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410108, '惠济区', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410122, '中牟县', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410181, '巩义市', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410182, '荥阳市', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410183, '新密市', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410184, '新郑市', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410185, '登封市', 410100, 3);
INSERT INTO `9h_sys_city` VALUES (410200, '开封市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410202, '龙亭区', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410203, '顺河回族区', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410204, '鼓楼区', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410205, '禹王台区', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410211, '金明区', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410221, '杞县', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410222, '通许县', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410223, '尉氏县', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410224, '开封县', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410225, '兰考县', 410200, 3);
INSERT INTO `9h_sys_city` VALUES (410300, '洛阳市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410302, '老城区', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410303, '西工区', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410304, '瀍河回族区', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410305, '涧西区', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410306, '吉利区', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410311, '洛龙区', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410322, '孟津县', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410323, '新安县', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410324, '栾川县', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410325, '嵩县', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410326, '汝阳县', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410327, '宜阳县', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410328, '洛宁县', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410329, '伊川县', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410381, '偃师市', 410300, 3);
INSERT INTO `9h_sys_city` VALUES (410400, '平顶山市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410402, '新华区', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410403, '卫东区', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410404, '石龙区', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410411, '湛河区', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410421, '宝丰县', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410422, '叶县', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410423, '鲁山县', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410425, '郏县', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410481, '舞钢市', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410482, '汝州市', 410400, 3);
INSERT INTO `9h_sys_city` VALUES (410500, '安阳市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410502, '文峰区', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410503, '北关区', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410505, '殷都区', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410506, '龙安区', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410522, '安阳县', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410523, '汤阴县', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410526, '滑县', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410527, '内黄县', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410581, '林州市', 410500, 3);
INSERT INTO `9h_sys_city` VALUES (410600, '鹤壁市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410602, '鹤山区', 410600, 3);
INSERT INTO `9h_sys_city` VALUES (410603, '山城区', 410600, 3);
INSERT INTO `9h_sys_city` VALUES (410611, '淇滨区', 410600, 3);
INSERT INTO `9h_sys_city` VALUES (410621, '浚县', 410600, 3);
INSERT INTO `9h_sys_city` VALUES (410622, '淇县', 410600, 3);
INSERT INTO `9h_sys_city` VALUES (410700, '新乡市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410702, '红旗区', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410703, '卫滨区', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410704, '凤泉区', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410711, '牧野区', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410721, '新乡县', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410724, '获嘉县', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410725, '原阳县', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410726, '延津县', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410727, '封丘县', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410728, '长垣县', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410781, '卫辉市', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410782, '辉县市', 410700, 3);
INSERT INTO `9h_sys_city` VALUES (410800, '焦作市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410802, '解放区', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410803, '中站区', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410804, '马村区', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410811, '山阳区', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410821, '修武县', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410822, '博爱县', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410823, '武陟县', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410825, '温县', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410882, '沁阳市', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410883, '孟州市', 410800, 3);
INSERT INTO `9h_sys_city` VALUES (410900, '濮阳市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (410902, '华龙区', 410900, 3);
INSERT INTO `9h_sys_city` VALUES (410922, '清丰县', 410900, 3);
INSERT INTO `9h_sys_city` VALUES (410923, '南乐县', 410900, 3);
INSERT INTO `9h_sys_city` VALUES (410926, '范县', 410900, 3);
INSERT INTO `9h_sys_city` VALUES (410927, '台前县', 410900, 3);
INSERT INTO `9h_sys_city` VALUES (410928, '濮阳县', 410900, 3);
INSERT INTO `9h_sys_city` VALUES (411000, '许昌市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (411002, '魏都区', 411000, 3);
INSERT INTO `9h_sys_city` VALUES (411023, '许昌县', 411000, 3);
INSERT INTO `9h_sys_city` VALUES (411024, '鄢陵县', 411000, 3);
INSERT INTO `9h_sys_city` VALUES (411025, '襄城县', 411000, 3);
INSERT INTO `9h_sys_city` VALUES (411081, '禹州市', 411000, 3);
INSERT INTO `9h_sys_city` VALUES (411082, '长葛市', 411000, 3);
INSERT INTO `9h_sys_city` VALUES (411100, '漯河市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (411102, '源汇区', 411100, 3);
INSERT INTO `9h_sys_city` VALUES (411103, '郾城区', 411100, 3);
INSERT INTO `9h_sys_city` VALUES (411104, '召陵区', 411100, 3);
INSERT INTO `9h_sys_city` VALUES (411121, '舞阳县', 411100, 3);
INSERT INTO `9h_sys_city` VALUES (411122, '临颍县', 411100, 3);
INSERT INTO `9h_sys_city` VALUES (411200, '三门峡市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (411202, '湖滨区', 411200, 3);
INSERT INTO `9h_sys_city` VALUES (411221, '渑池县', 411200, 3);
INSERT INTO `9h_sys_city` VALUES (411222, '陕县', 411200, 3);
INSERT INTO `9h_sys_city` VALUES (411224, '卢氏县', 411200, 3);
INSERT INTO `9h_sys_city` VALUES (411281, '义马市', 411200, 3);
INSERT INTO `9h_sys_city` VALUES (411282, '灵宝市', 411200, 3);
INSERT INTO `9h_sys_city` VALUES (411300, '南阳市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (411302, '宛城区', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411303, '卧龙区', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411321, '南召县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411322, '方城县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411323, '西峡县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411324, '镇平县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411325, '内乡县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411326, '淅川县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411327, '社旗县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411328, '唐河县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411329, '新野县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411330, '桐柏县', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411381, '邓州市', 411300, 3);
INSERT INTO `9h_sys_city` VALUES (411400, '商丘市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (411402, '梁园区', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411403, '睢阳区', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411421, '民权县', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411422, '睢县', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411423, '宁陵县', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411424, '柘城县', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411425, '虞城县', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411426, '夏邑县', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411481, '永城市', 411400, 3);
INSERT INTO `9h_sys_city` VALUES (411500, '信阳市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (411502, '浉河区', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411503, '平桥区', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411521, '罗山县', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411522, '光山县', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411523, '新县', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411524, '商城县', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411525, '固始县', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411526, '潢川县', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411527, '淮滨县', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411528, '息县', 411500, 3);
INSERT INTO `9h_sys_city` VALUES (411600, '周口市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (411602, '川汇区', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411621, '扶沟县', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411622, '西华县', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411623, '商水县', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411624, '沈丘县', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411625, '郸城县', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411626, '淮阳县', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411627, '太康县', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411628, '鹿邑县', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411681, '项城市', 411600, 3);
INSERT INTO `9h_sys_city` VALUES (411700, '驻马店市', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (411702, '驿城区', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411721, '西平县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411722, '上蔡县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411723, '平舆县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411724, '正阳县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411725, '确山县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411726, '泌阳县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411727, '汝南县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411728, '遂平县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (411729, '新蔡县', 411700, 3);
INSERT INTO `9h_sys_city` VALUES (419000, '省直辖县级行政区划', 410000, 2);
INSERT INTO `9h_sys_city` VALUES (419001, '济源市', 419000, 3);
INSERT INTO `9h_sys_city` VALUES (420000, '湖北省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (420100, '武汉市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (420102, '江岸区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420103, '江汉区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420104, '硚口区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420105, '汉阳区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420106, '武昌区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420107, '青山区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420111, '洪山区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420112, '东西湖区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420113, '汉南区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420114, '蔡甸区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420115, '江夏区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420116, '黄陂区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420117, '新洲区', 420100, 3);
INSERT INTO `9h_sys_city` VALUES (420200, '黄石市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (420202, '黄石港区', 420200, 3);
INSERT INTO `9h_sys_city` VALUES (420203, '西塞山区', 420200, 3);
INSERT INTO `9h_sys_city` VALUES (420204, '下陆区', 420200, 3);
INSERT INTO `9h_sys_city` VALUES (420205, '铁山区', 420200, 3);
INSERT INTO `9h_sys_city` VALUES (420222, '阳新县', 420200, 3);
INSERT INTO `9h_sys_city` VALUES (420281, '大冶市', 420200, 3);
INSERT INTO `9h_sys_city` VALUES (420300, '十堰市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (420302, '茅箭区', 420300, 3);
INSERT INTO `9h_sys_city` VALUES (420303, '张湾区', 420300, 3);
INSERT INTO `9h_sys_city` VALUES (420304, '郧阳区', 420300, 3);
INSERT INTO `9h_sys_city` VALUES (420322, '郧西县', 420300, 3);
INSERT INTO `9h_sys_city` VALUES (420323, '竹山县', 420300, 3);
INSERT INTO `9h_sys_city` VALUES (420324, '竹溪县', 420300, 3);
INSERT INTO `9h_sys_city` VALUES (420325, '房县', 420300, 3);
INSERT INTO `9h_sys_city` VALUES (420381, '丹江口市', 420300, 3);
INSERT INTO `9h_sys_city` VALUES (420500, '宜昌市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (420502, '西陵区', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420503, '伍家岗区', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420504, '点军区', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420505, '猇亭区', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420506, '夷陵区', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420525, '远安县', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420526, '兴山县', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420527, '秭归县', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420528, '长阳土家族自治县', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420529, '五峰土家族自治县', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420581, '宜都市', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420582, '当阳市', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420583, '枝江市', 420500, 3);
INSERT INTO `9h_sys_city` VALUES (420600, '襄阳市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (420602, '襄城区', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420606, '樊城区', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420607, '襄州区', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420624, '南漳县', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420625, '谷城县', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420626, '保康县', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420682, '老河口市', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420683, '枣阳市', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420684, '宜城市', 420600, 3);
INSERT INTO `9h_sys_city` VALUES (420700, '鄂州市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (420702, '梁子湖区', 420700, 3);
INSERT INTO `9h_sys_city` VALUES (420703, '华容区', 420700, 3);
INSERT INTO `9h_sys_city` VALUES (420704, '鄂城区', 420700, 3);
INSERT INTO `9h_sys_city` VALUES (420800, '荆门市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (420802, '东宝区', 420800, 3);
INSERT INTO `9h_sys_city` VALUES (420804, '掇刀区', 420800, 3);
INSERT INTO `9h_sys_city` VALUES (420821, '京山县', 420800, 3);
INSERT INTO `9h_sys_city` VALUES (420822, '沙洋县', 420800, 3);
INSERT INTO `9h_sys_city` VALUES (420881, '钟祥市', 420800, 3);
INSERT INTO `9h_sys_city` VALUES (420900, '孝感市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (420902, '孝南区', 420900, 3);
INSERT INTO `9h_sys_city` VALUES (420921, '孝昌县', 420900, 3);
INSERT INTO `9h_sys_city` VALUES (420922, '大悟县', 420900, 3);
INSERT INTO `9h_sys_city` VALUES (420923, '云梦县', 420900, 3);
INSERT INTO `9h_sys_city` VALUES (420981, '应城市', 420900, 3);
INSERT INTO `9h_sys_city` VALUES (420982, '安陆市', 420900, 3);
INSERT INTO `9h_sys_city` VALUES (420984, '汉川市', 420900, 3);
INSERT INTO `9h_sys_city` VALUES (421000, '荆州市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (421002, '沙市区', 421000, 3);
INSERT INTO `9h_sys_city` VALUES (421003, '荆州区', 421000, 3);
INSERT INTO `9h_sys_city` VALUES (421022, '公安县', 421000, 3);
INSERT INTO `9h_sys_city` VALUES (421023, '监利县', 421000, 3);
INSERT INTO `9h_sys_city` VALUES (421024, '江陵县', 421000, 3);
INSERT INTO `9h_sys_city` VALUES (421081, '石首市', 421000, 3);
INSERT INTO `9h_sys_city` VALUES (421083, '洪湖市', 421000, 3);
INSERT INTO `9h_sys_city` VALUES (421087, '松滋市', 421000, 3);
INSERT INTO `9h_sys_city` VALUES (421100, '黄冈市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (421102, '黄州区', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421121, '团风县', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421122, '红安县', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421123, '罗田县', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421124, '英山县', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421125, '浠水县', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421126, '蕲春县', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421127, '黄梅县', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421181, '麻城市', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421182, '武穴市', 421100, 3);
INSERT INTO `9h_sys_city` VALUES (421200, '咸宁市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (421202, '咸安区', 421200, 3);
INSERT INTO `9h_sys_city` VALUES (421221, '嘉鱼县', 421200, 3);
INSERT INTO `9h_sys_city` VALUES (421222, '通城县', 421200, 3);
INSERT INTO `9h_sys_city` VALUES (421223, '崇阳县', 421200, 3);
INSERT INTO `9h_sys_city` VALUES (421224, '通山县', 421200, 3);
INSERT INTO `9h_sys_city` VALUES (421281, '赤壁市', 421200, 3);
INSERT INTO `9h_sys_city` VALUES (421300, '随州市', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (421303, '曾都区', 421300, 3);
INSERT INTO `9h_sys_city` VALUES (421321, '随县', 421300, 3);
INSERT INTO `9h_sys_city` VALUES (421381, '广水市', 421300, 3);
INSERT INTO `9h_sys_city` VALUES (422800, '恩施土家族苗族自治州', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (422801, '恩施市', 422800, 3);
INSERT INTO `9h_sys_city` VALUES (422802, '利川市', 422800, 3);
INSERT INTO `9h_sys_city` VALUES (422822, '建始县', 422800, 3);
INSERT INTO `9h_sys_city` VALUES (422823, '巴东县', 422800, 3);
INSERT INTO `9h_sys_city` VALUES (422825, '宣恩县', 422800, 3);
INSERT INTO `9h_sys_city` VALUES (422826, '咸丰县', 422800, 3);
INSERT INTO `9h_sys_city` VALUES (422827, '来凤县', 422800, 3);
INSERT INTO `9h_sys_city` VALUES (422828, '鹤峰县', 422800, 3);
INSERT INTO `9h_sys_city` VALUES (429000, '省直辖县级行政区划', 420000, 2);
INSERT INTO `9h_sys_city` VALUES (429004, '仙桃市', 429000, 3);
INSERT INTO `9h_sys_city` VALUES (429005, '潜江市', 429000, 3);
INSERT INTO `9h_sys_city` VALUES (429006, '天门市', 429000, 3);
INSERT INTO `9h_sys_city` VALUES (429021, '神农架林区', 429000, 3);
INSERT INTO `9h_sys_city` VALUES (430000, '湖南省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (430100, '长沙市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430102, '芙蓉区', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430103, '天心区', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430104, '岳麓区', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430105, '开福区', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430111, '雨花区', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430112, '望城区', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430121, '长沙县', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430124, '宁乡县', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430181, '浏阳市', 430100, 3);
INSERT INTO `9h_sys_city` VALUES (430200, '株洲市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430202, '荷塘区', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430203, '芦淞区', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430204, '石峰区', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430211, '天元区', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430221, '株洲县', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430223, '攸县', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430224, '茶陵县', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430225, '炎陵县', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430281, '醴陵市', 430200, 3);
INSERT INTO `9h_sys_city` VALUES (430300, '湘潭市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430302, '雨湖区', 430300, 3);
INSERT INTO `9h_sys_city` VALUES (430304, '岳塘区', 430300, 3);
INSERT INTO `9h_sys_city` VALUES (430321, '湘潭县', 430300, 3);
INSERT INTO `9h_sys_city` VALUES (430381, '湘乡市', 430300, 3);
INSERT INTO `9h_sys_city` VALUES (430382, '韶山市', 430300, 3);
INSERT INTO `9h_sys_city` VALUES (430400, '衡阳市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430405, '珠晖区', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430406, '雁峰区', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430407, '石鼓区', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430408, '蒸湘区', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430412, '南岳区', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430421, '衡阳县', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430422, '衡南县', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430423, '衡山县', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430424, '衡东县', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430426, '祁东县', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430481, '耒阳市', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430482, '常宁市', 430400, 3);
INSERT INTO `9h_sys_city` VALUES (430500, '邵阳市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430502, '双清区', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430503, '大祥区', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430511, '北塔区', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430521, '邵东县', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430522, '新邵县', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430523, '邵阳县', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430524, '隆回县', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430525, '洞口县', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430527, '绥宁县', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430528, '新宁县', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430529, '城步苗族自治县', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430581, '武冈市', 430500, 3);
INSERT INTO `9h_sys_city` VALUES (430600, '岳阳市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430602, '岳阳楼区', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430603, '云溪区', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430611, '君山区', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430621, '岳阳县', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430623, '华容县', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430624, '湘阴县', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430626, '平江县', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430681, '汨罗市', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430682, '临湘市', 430600, 3);
INSERT INTO `9h_sys_city` VALUES (430700, '常德市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430702, '武陵区', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430703, '鼎城区', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430721, '安乡县', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430722, '汉寿县', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430723, '澧县', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430724, '临澧县', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430725, '桃源县', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430726, '石门县', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430781, '津市市', 430700, 3);
INSERT INTO `9h_sys_city` VALUES (430800, '张家界市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430802, '永定区', 430800, 3);
INSERT INTO `9h_sys_city` VALUES (430811, '武陵源区', 430800, 3);
INSERT INTO `9h_sys_city` VALUES (430821, '慈利县', 430800, 3);
INSERT INTO `9h_sys_city` VALUES (430822, '桑植县', 430800, 3);
INSERT INTO `9h_sys_city` VALUES (430900, '益阳市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (430902, '资阳区', 430900, 3);
INSERT INTO `9h_sys_city` VALUES (430903, '赫山区', 430900, 3);
INSERT INTO `9h_sys_city` VALUES (430921, '南县', 430900, 3);
INSERT INTO `9h_sys_city` VALUES (430922, '桃江县', 430900, 3);
INSERT INTO `9h_sys_city` VALUES (430923, '安化县', 430900, 3);
INSERT INTO `9h_sys_city` VALUES (430981, '沅江市', 430900, 3);
INSERT INTO `9h_sys_city` VALUES (431000, '郴州市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (431002, '北湖区', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431003, '苏仙区', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431021, '桂阳县', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431022, '宜章县', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431023, '永兴县', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431024, '嘉禾县', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431025, '临武县', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431026, '汝城县', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431027, '桂东县', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431028, '安仁县', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431081, '资兴市', 431000, 3);
INSERT INTO `9h_sys_city` VALUES (431100, '永州市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (431102, '零陵区', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431103, '冷水滩区', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431121, '祁阳县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431122, '东安县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431123, '双牌县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431124, '道县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431125, '江永县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431126, '宁远县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431127, '蓝山县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431128, '新田县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431129, '江华瑶族自治县', 431100, 3);
INSERT INTO `9h_sys_city` VALUES (431200, '怀化市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (431202, '鹤城区', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431221, '中方县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431222, '沅陵县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431223, '辰溪县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431224, '溆浦县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431225, '会同县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431226, '麻阳苗族自治县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431227, '新晃侗族自治县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431228, '芷江侗族自治县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431229, '靖州苗族侗族自治县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431230, '通道侗族自治县', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431281, '洪江市', 431200, 3);
INSERT INTO `9h_sys_city` VALUES (431300, '娄底市', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (431302, '娄星区', 431300, 3);
INSERT INTO `9h_sys_city` VALUES (431321, '双峰县', 431300, 3);
INSERT INTO `9h_sys_city` VALUES (431322, '新化县', 431300, 3);
INSERT INTO `9h_sys_city` VALUES (431381, '冷水江市', 431300, 3);
INSERT INTO `9h_sys_city` VALUES (431382, '涟源市', 431300, 3);
INSERT INTO `9h_sys_city` VALUES (433100, '湘西土家族苗族自治州', 430000, 2);
INSERT INTO `9h_sys_city` VALUES (433101, '吉首市', 433100, 3);
INSERT INTO `9h_sys_city` VALUES (433122, '泸溪县', 433100, 3);
INSERT INTO `9h_sys_city` VALUES (433123, '凤凰县', 433100, 3);
INSERT INTO `9h_sys_city` VALUES (433124, '花垣县', 433100, 3);
INSERT INTO `9h_sys_city` VALUES (433125, '保靖县', 433100, 3);
INSERT INTO `9h_sys_city` VALUES (433126, '古丈县', 433100, 3);
INSERT INTO `9h_sys_city` VALUES (433127, '永顺县', 433100, 3);
INSERT INTO `9h_sys_city` VALUES (433130, '龙山县', 433100, 3);
INSERT INTO `9h_sys_city` VALUES (440000, '广东省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (440100, '广州市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440103, '荔湾区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440104, '越秀区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440105, '海珠区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440106, '天河区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440111, '白云区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440112, '黄埔区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440113, '番禺区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440114, '花都区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440115, '南沙区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440116, '萝岗区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440117, '从化区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440118, '增城区', 440100, 3);
INSERT INTO `9h_sys_city` VALUES (440200, '韶关市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440203, '武江区', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440204, '浈江区', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440205, '曲江区', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440222, '始兴县', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440224, '仁化县', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440229, '翁源县', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440232, '乳源瑶族自治县', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440233, '新丰县', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440281, '乐昌市', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440282, '南雄市', 440200, 3);
INSERT INTO `9h_sys_city` VALUES (440300, '深圳市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440303, '罗湖区', 440300, 3);
INSERT INTO `9h_sys_city` VALUES (440304, '福田区', 440300, 3);
INSERT INTO `9h_sys_city` VALUES (440305, '南山区', 440300, 3);
INSERT INTO `9h_sys_city` VALUES (440306, '宝安区', 440300, 3);
INSERT INTO `9h_sys_city` VALUES (440307, '龙岗区', 440300, 3);
INSERT INTO `9h_sys_city` VALUES (440308, '盐田区', 440300, 3);
INSERT INTO `9h_sys_city` VALUES (440400, '珠海市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440402, '香洲区', 440400, 3);
INSERT INTO `9h_sys_city` VALUES (440403, '斗门区', 440400, 3);
INSERT INTO `9h_sys_city` VALUES (440404, '金湾区', 440400, 3);
INSERT INTO `9h_sys_city` VALUES (440500, '汕头市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440507, '龙湖区', 440500, 3);
INSERT INTO `9h_sys_city` VALUES (440511, '金平区', 440500, 3);
INSERT INTO `9h_sys_city` VALUES (440512, '濠江区', 440500, 3);
INSERT INTO `9h_sys_city` VALUES (440513, '潮阳区', 440500, 3);
INSERT INTO `9h_sys_city` VALUES (440514, '潮南区', 440500, 3);
INSERT INTO `9h_sys_city` VALUES (440515, '澄海区', 440500, 3);
INSERT INTO `9h_sys_city` VALUES (440523, '南澳县', 440500, 3);
INSERT INTO `9h_sys_city` VALUES (440600, '佛山市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440604, '禅城区', 440600, 3);
INSERT INTO `9h_sys_city` VALUES (440605, '南海区', 440600, 3);
INSERT INTO `9h_sys_city` VALUES (440606, '顺德区', 440600, 3);
INSERT INTO `9h_sys_city` VALUES (440607, '三水区', 440600, 3);
INSERT INTO `9h_sys_city` VALUES (440608, '高明区', 440600, 3);
INSERT INTO `9h_sys_city` VALUES (440700, '江门市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440703, '蓬江区', 440700, 3);
INSERT INTO `9h_sys_city` VALUES (440704, '江海区', 440700, 3);
INSERT INTO `9h_sys_city` VALUES (440705, '新会区', 440700, 3);
INSERT INTO `9h_sys_city` VALUES (440781, '台山市', 440700, 3);
INSERT INTO `9h_sys_city` VALUES (440783, '开平市', 440700, 3);
INSERT INTO `9h_sys_city` VALUES (440784, '鹤山市', 440700, 3);
INSERT INTO `9h_sys_city` VALUES (440785, '恩平市', 440700, 3);
INSERT INTO `9h_sys_city` VALUES (440800, '湛江市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440802, '赤坎区', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440803, '霞山区', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440804, '坡头区', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440811, '麻章区', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440823, '遂溪县', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440825, '徐闻县', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440881, '廉江市', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440882, '雷州市', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440883, '吴川市', 440800, 3);
INSERT INTO `9h_sys_city` VALUES (440900, '茂名市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (440902, '茂南区', 440900, 3);
INSERT INTO `9h_sys_city` VALUES (440904, '电白区', 440900, 3);
INSERT INTO `9h_sys_city` VALUES (440981, '高州市', 440900, 3);
INSERT INTO `9h_sys_city` VALUES (440982, '化州市', 440900, 3);
INSERT INTO `9h_sys_city` VALUES (440983, '信宜市', 440900, 3);
INSERT INTO `9h_sys_city` VALUES (441200, '肇庆市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (441202, '端州区', 441200, 3);
INSERT INTO `9h_sys_city` VALUES (441203, '鼎湖区', 441200, 3);
INSERT INTO `9h_sys_city` VALUES (441223, '广宁县', 441200, 3);
INSERT INTO `9h_sys_city` VALUES (441224, '怀集县', 441200, 3);
INSERT INTO `9h_sys_city` VALUES (441225, '封开县', 441200, 3);
INSERT INTO `9h_sys_city` VALUES (441226, '德庆县', 441200, 3);
INSERT INTO `9h_sys_city` VALUES (441283, '高要市', 441200, 3);
INSERT INTO `9h_sys_city` VALUES (441284, '四会市', 441200, 3);
INSERT INTO `9h_sys_city` VALUES (441300, '惠州市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (441302, '惠城区', 441300, 3);
INSERT INTO `9h_sys_city` VALUES (441303, '惠阳区', 441300, 3);
INSERT INTO `9h_sys_city` VALUES (441322, '博罗县', 441300, 3);
INSERT INTO `9h_sys_city` VALUES (441323, '惠东县', 441300, 3);
INSERT INTO `9h_sys_city` VALUES (441324, '龙门县', 441300, 3);
INSERT INTO `9h_sys_city` VALUES (441400, '梅州市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (441402, '梅江区', 441400, 3);
INSERT INTO `9h_sys_city` VALUES (441403, '梅县区', 441400, 3);
INSERT INTO `9h_sys_city` VALUES (441422, '大埔县', 441400, 3);
INSERT INTO `9h_sys_city` VALUES (441423, '丰顺县', 441400, 3);
INSERT INTO `9h_sys_city` VALUES (441424, '五华县', 441400, 3);
INSERT INTO `9h_sys_city` VALUES (441426, '平远县', 441400, 3);
INSERT INTO `9h_sys_city` VALUES (441427, '蕉岭县', 441400, 3);
INSERT INTO `9h_sys_city` VALUES (441481, '兴宁市', 441400, 3);
INSERT INTO `9h_sys_city` VALUES (441500, '汕尾市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (441502, '城区', 441500, 3);
INSERT INTO `9h_sys_city` VALUES (441521, '海丰县', 441500, 3);
INSERT INTO `9h_sys_city` VALUES (441523, '陆河县', 441500, 3);
INSERT INTO `9h_sys_city` VALUES (441581, '陆丰市', 441500, 3);
INSERT INTO `9h_sys_city` VALUES (441600, '河源市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (441602, '源城区', 441600, 3);
INSERT INTO `9h_sys_city` VALUES (441621, '紫金县', 441600, 3);
INSERT INTO `9h_sys_city` VALUES (441622, '龙川县', 441600, 3);
INSERT INTO `9h_sys_city` VALUES (441623, '连平县', 441600, 3);
INSERT INTO `9h_sys_city` VALUES (441624, '和平县', 441600, 3);
INSERT INTO `9h_sys_city` VALUES (441625, '东源县', 441600, 3);
INSERT INTO `9h_sys_city` VALUES (441700, '阳江市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (441702, '江城区', 441700, 3);
INSERT INTO `9h_sys_city` VALUES (441721, '阳西县', 441700, 3);
INSERT INTO `9h_sys_city` VALUES (441723, '阳东县', 441700, 3);
INSERT INTO `9h_sys_city` VALUES (441781, '阳春市', 441700, 3);
INSERT INTO `9h_sys_city` VALUES (441800, '清远市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (441802, '清城区', 441800, 3);
INSERT INTO `9h_sys_city` VALUES (441803, '清新区', 441800, 3);
INSERT INTO `9h_sys_city` VALUES (441821, '佛冈县', 441800, 3);
INSERT INTO `9h_sys_city` VALUES (441823, '阳山县', 441800, 3);
INSERT INTO `9h_sys_city` VALUES (441825, '连山壮族瑶族自治县', 441800, 3);
INSERT INTO `9h_sys_city` VALUES (441826, '连南瑶族自治县', 441800, 3);
INSERT INTO `9h_sys_city` VALUES (441881, '英德市', 441800, 3);
INSERT INTO `9h_sys_city` VALUES (441882, '连州市', 441800, 3);
INSERT INTO `9h_sys_city` VALUES (441900, '东莞市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (442000, '中山市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (445100, '潮州市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (445102, '湘桥区', 445100, 3);
INSERT INTO `9h_sys_city` VALUES (445103, '潮安区', 445100, 3);
INSERT INTO `9h_sys_city` VALUES (445122, '饶平县', 445100, 3);
INSERT INTO `9h_sys_city` VALUES (445200, '揭阳市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (445202, '榕城区', 445200, 3);
INSERT INTO `9h_sys_city` VALUES (445203, '揭东区', 445200, 3);
INSERT INTO `9h_sys_city` VALUES (445222, '揭西县', 445200, 3);
INSERT INTO `9h_sys_city` VALUES (445224, '惠来县', 445200, 3);
INSERT INTO `9h_sys_city` VALUES (445281, '普宁市', 445200, 3);
INSERT INTO `9h_sys_city` VALUES (445300, '云浮市', 440000, 2);
INSERT INTO `9h_sys_city` VALUES (445302, '云城区', 445300, 3);
INSERT INTO `9h_sys_city` VALUES (445303, '云安区', 445300, 3);
INSERT INTO `9h_sys_city` VALUES (445321, '新兴县', 445300, 3);
INSERT INTO `9h_sys_city` VALUES (445322, '郁南县', 445300, 3);
INSERT INTO `9h_sys_city` VALUES (445381, '罗定市', 445300, 3);
INSERT INTO `9h_sys_city` VALUES (450000, '广西壮族自治区', 0, 1);
INSERT INTO `9h_sys_city` VALUES (450100, '南宁市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450102, '兴宁区', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450103, '青秀区', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450105, '江南区', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450107, '西乡塘区', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450108, '良庆区', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450109, '邕宁区', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450122, '武鸣县', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450123, '隆安县', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450124, '马山县', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450125, '上林县', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450126, '宾阳县', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450127, '横县', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450128, '五象新区', 450100, 3);
INSERT INTO `9h_sys_city` VALUES (450200, '柳州市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450202, '城中区', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450203, '鱼峰区', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450204, '柳南区', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450205, '柳北区', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450221, '柳江县', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450222, '柳城县', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450223, '鹿寨县', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450224, '融安县', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450225, '融水苗族自治县', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450226, '三江侗族自治县', 450200, 3);
INSERT INTO `9h_sys_city` VALUES (450300, '桂林市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450302, '秀峰区', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450303, '叠彩区', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450304, '象山区', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450305, '七星区', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450311, '雁山区', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450312, '临桂区', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450321, '阳朔县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450323, '灵川县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450324, '全州县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450325, '兴安县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450326, '永福县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450327, '灌阳县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450328, '龙胜各族自治县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450329, '资源县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450330, '平乐县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450331, '荔浦县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450332, '恭城瑶族自治县', 450300, 3);
INSERT INTO `9h_sys_city` VALUES (450400, '梧州市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450403, '万秀区', 450400, 3);
INSERT INTO `9h_sys_city` VALUES (450405, '长洲区', 450400, 3);
INSERT INTO `9h_sys_city` VALUES (450406, '龙圩区', 450400, 3);
INSERT INTO `9h_sys_city` VALUES (450421, '苍梧县', 450400, 3);
INSERT INTO `9h_sys_city` VALUES (450422, '藤县', 450400, 3);
INSERT INTO `9h_sys_city` VALUES (450423, '蒙山县', 450400, 3);
INSERT INTO `9h_sys_city` VALUES (450481, '岑溪市', 450400, 3);
INSERT INTO `9h_sys_city` VALUES (450500, '北海市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450502, '海城区', 450500, 3);
INSERT INTO `9h_sys_city` VALUES (450503, '银海区', 450500, 3);
INSERT INTO `9h_sys_city` VALUES (450512, '铁山港区', 450500, 3);
INSERT INTO `9h_sys_city` VALUES (450521, '合浦县', 450500, 3);
INSERT INTO `9h_sys_city` VALUES (450600, '防城港市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450602, '港口区', 450600, 3);
INSERT INTO `9h_sys_city` VALUES (450603, '防城区', 450600, 3);
INSERT INTO `9h_sys_city` VALUES (450621, '上思县', 450600, 3);
INSERT INTO `9h_sys_city` VALUES (450681, '东兴市', 450600, 3);
INSERT INTO `9h_sys_city` VALUES (450700, '钦州市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450702, '钦南区', 450700, 3);
INSERT INTO `9h_sys_city` VALUES (450703, '钦北区', 450700, 3);
INSERT INTO `9h_sys_city` VALUES (450721, '灵山县', 450700, 3);
INSERT INTO `9h_sys_city` VALUES (450722, '浦北县', 450700, 3);
INSERT INTO `9h_sys_city` VALUES (450800, '贵港市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450802, '港北区', 450800, 3);
INSERT INTO `9h_sys_city` VALUES (450803, '港南区', 450800, 3);
INSERT INTO `9h_sys_city` VALUES (450804, '覃塘区', 450800, 3);
INSERT INTO `9h_sys_city` VALUES (450821, '平南县', 450800, 3);
INSERT INTO `9h_sys_city` VALUES (450881, '桂平市', 450800, 3);
INSERT INTO `9h_sys_city` VALUES (450900, '玉林市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (450902, '玉州区', 450900, 3);
INSERT INTO `9h_sys_city` VALUES (450903, '福绵区', 450900, 3);
INSERT INTO `9h_sys_city` VALUES (450921, '容县', 450900, 3);
INSERT INTO `9h_sys_city` VALUES (450922, '陆川县', 450900, 3);
INSERT INTO `9h_sys_city` VALUES (450923, '博白县', 450900, 3);
INSERT INTO `9h_sys_city` VALUES (450924, '兴业县', 450900, 3);
INSERT INTO `9h_sys_city` VALUES (450981, '北流市', 450900, 3);
INSERT INTO `9h_sys_city` VALUES (451000, '百色市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (451002, '右江区', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451021, '田阳县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451022, '田东县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451023, '平果县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451024, '德保县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451025, '靖西县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451026, '那坡县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451027, '凌云县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451028, '乐业县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451029, '田林县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451030, '西林县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451031, '隆林各族自治县', 451000, 3);
INSERT INTO `9h_sys_city` VALUES (451100, '贺州市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (451102, '八步区', 451100, 3);
INSERT INTO `9h_sys_city` VALUES (451121, '昭平县', 451100, 3);
INSERT INTO `9h_sys_city` VALUES (451122, '钟山县', 451100, 3);
INSERT INTO `9h_sys_city` VALUES (451123, '富川瑶族自治县', 451100, 3);
INSERT INTO `9h_sys_city` VALUES (451200, '河池市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (451202, '金城江区', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451221, '南丹县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451222, '天峨县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451223, '凤山县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451224, '东兰县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451225, '罗城仫佬族自治县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451226, '环江毛南族自治县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451227, '巴马瑶族自治县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451228, '都安瑶族自治县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451229, '大化瑶族自治县', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451281, '宜州市', 451200, 3);
INSERT INTO `9h_sys_city` VALUES (451300, '来宾市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (451302, '兴宾区', 451300, 3);
INSERT INTO `9h_sys_city` VALUES (451321, '忻城县', 451300, 3);
INSERT INTO `9h_sys_city` VALUES (451322, '象州县', 451300, 3);
INSERT INTO `9h_sys_city` VALUES (451323, '武宣县', 451300, 3);
INSERT INTO `9h_sys_city` VALUES (451324, '金秀瑶族自治县', 451300, 3);
INSERT INTO `9h_sys_city` VALUES (451381, '合山市', 451300, 3);
INSERT INTO `9h_sys_city` VALUES (451400, '崇左市', 450000, 2);
INSERT INTO `9h_sys_city` VALUES (451402, '江州区', 451400, 3);
INSERT INTO `9h_sys_city` VALUES (451421, '扶绥县', 451400, 3);
INSERT INTO `9h_sys_city` VALUES (451422, '宁明县', 451400, 3);
INSERT INTO `9h_sys_city` VALUES (451423, '龙州县', 451400, 3);
INSERT INTO `9h_sys_city` VALUES (451424, '大新县', 451400, 3);
INSERT INTO `9h_sys_city` VALUES (451425, '天等县', 451400, 3);
INSERT INTO `9h_sys_city` VALUES (451481, '凭祥市', 451400, 3);
INSERT INTO `9h_sys_city` VALUES (460000, '海南省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (460100, '海口市', 460000, 2);
INSERT INTO `9h_sys_city` VALUES (460105, '秀英区', 460100, 3);
INSERT INTO `9h_sys_city` VALUES (460106, '龙华区', 460100, 3);
INSERT INTO `9h_sys_city` VALUES (460107, '琼山区', 460100, 3);
INSERT INTO `9h_sys_city` VALUES (460108, '美兰区', 460100, 3);
INSERT INTO `9h_sys_city` VALUES (460200, '三亚市', 460000, 2);
INSERT INTO `9h_sys_city` VALUES (460202, '海棠区', 460200, 3);
INSERT INTO `9h_sys_city` VALUES (460203, '吉阳区', 460200, 3);
INSERT INTO `9h_sys_city` VALUES (460204, '天涯区', 460200, 3);
INSERT INTO `9h_sys_city` VALUES (460205, '崖州区', 460200, 3);
INSERT INTO `9h_sys_city` VALUES (460300, '三沙市', 460000, 2);
INSERT INTO `9h_sys_city` VALUES (469000, '省直辖县级行政区划', 460000, 2);
INSERT INTO `9h_sys_city` VALUES (469001, '五指山市', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469002, '琼海市', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469003, '儋州市', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469005, '文昌市', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469006, '万宁市', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469007, '东方市', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469021, '定安县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469022, '屯昌县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469023, '澄迈县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469024, '临高县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469025, '白沙黎族自治县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469026, '昌江黎族自治县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469027, '乐东黎族自治县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469028, '陵水黎族自治县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469029, '保亭黎族苗族自治县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (469030, '琼中黎族苗族自治县', 469000, 3);
INSERT INTO `9h_sys_city` VALUES (500000, '重庆市', 0, 1);
INSERT INTO `9h_sys_city` VALUES (500100, '重庆市(市区)', 500000, 2);
INSERT INTO `9h_sys_city` VALUES (500101, '万州区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500102, '涪陵区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500103, '渝中区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500104, '大渡口区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500105, '江北区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500106, '沙坪坝区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500107, '九龙坡区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500108, '南岸区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500109, '北碚区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500110, '綦江区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500111, '大足区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500112, '渝北区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500113, '巴南区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500114, '黔江区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500115, '长寿区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500116, '江津区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500117, '合川区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500118, '永川区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500119, '南川区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500120, '璧山区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500151, '铜梁区', 500100, 3);
INSERT INTO `9h_sys_city` VALUES (500200, '重庆市 (县)', 500000, 2);
INSERT INTO `9h_sys_city` VALUES (500223, '潼南县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500226, '荣昌县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500228, '梁平县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500229, '城口县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500230, '丰都县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500231, '垫江县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500232, '武隆县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500233, '忠县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500234, '开县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500235, '云阳县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500236, '奉节县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500237, '巫山县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500238, '巫溪县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500240, '石柱土家族自治县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500241, '秀山土家族苗族自治县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500242, '酉阳土家族苗族自治县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (500243, '彭水苗族土家族自治县', 500200, 3);
INSERT INTO `9h_sys_city` VALUES (510000, '四川省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (510100, '成都市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (510104, '锦江区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510105, '青羊区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510106, '金牛区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510107, '武侯区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510108, '成华区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510112, '龙泉驿区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510113, '青白江区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510114, '新都区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510115, '温江区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510121, '金堂县', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510122, '双流县', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510124, '郫县', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510129, '大邑县', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510131, '蒲江县', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510132, '新津县', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510181, '都江堰市', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510182, '彭州市', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510183, '邛崃市', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510184, '崇州市', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510185, '天府新区', 510100, 3);
INSERT INTO `9h_sys_city` VALUES (510300, '自贡市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (510302, '自流井区', 510300, 3);
INSERT INTO `9h_sys_city` VALUES (510303, '贡井区', 510300, 3);
INSERT INTO `9h_sys_city` VALUES (510304, '大安区', 510300, 3);
INSERT INTO `9h_sys_city` VALUES (510311, '沿滩区', 510300, 3);
INSERT INTO `9h_sys_city` VALUES (510321, '荣县', 510300, 3);
INSERT INTO `9h_sys_city` VALUES (510322, '富顺县', 510300, 3);
INSERT INTO `9h_sys_city` VALUES (510400, '攀枝花市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (510402, '东区', 510400, 3);
INSERT INTO `9h_sys_city` VALUES (510403, '西区', 510400, 3);
INSERT INTO `9h_sys_city` VALUES (510411, '仁和区', 510400, 3);
INSERT INTO `9h_sys_city` VALUES (510421, '米易县', 510400, 3);
INSERT INTO `9h_sys_city` VALUES (510422, '盐边县', 510400, 3);
INSERT INTO `9h_sys_city` VALUES (510500, '泸州市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (510502, '江阳区', 510500, 3);
INSERT INTO `9h_sys_city` VALUES (510503, '纳溪区', 510500, 3);
INSERT INTO `9h_sys_city` VALUES (510504, '龙马潭区', 510500, 3);
INSERT INTO `9h_sys_city` VALUES (510521, '泸县', 510500, 3);
INSERT INTO `9h_sys_city` VALUES (510522, '合江县', 510500, 3);
INSERT INTO `9h_sys_city` VALUES (510524, '叙永县', 510500, 3);
INSERT INTO `9h_sys_city` VALUES (510525, '古蔺县', 510500, 3);
INSERT INTO `9h_sys_city` VALUES (510600, '德阳市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (510603, '旌阳区', 510600, 3);
INSERT INTO `9h_sys_city` VALUES (510623, '中江县', 510600, 3);
INSERT INTO `9h_sys_city` VALUES (510626, '罗江县', 510600, 3);
INSERT INTO `9h_sys_city` VALUES (510681, '广汉市', 510600, 3);
INSERT INTO `9h_sys_city` VALUES (510682, '什邡市', 510600, 3);
INSERT INTO `9h_sys_city` VALUES (510683, '绵竹市', 510600, 3);
INSERT INTO `9h_sys_city` VALUES (510700, '绵阳市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (510703, '涪城区', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510704, '游仙区', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510722, '三台县', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510723, '盐亭县', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510724, '安县', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510725, '梓潼县', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510726, '北川羌族自治县', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510727, '平武县', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510781, '江油市', 510700, 3);
INSERT INTO `9h_sys_city` VALUES (510800, '广元市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (510802, '利州区', 510800, 3);
INSERT INTO `9h_sys_city` VALUES (510811, '昭化区', 510800, 3);
INSERT INTO `9h_sys_city` VALUES (510812, '朝天区', 510800, 3);
INSERT INTO `9h_sys_city` VALUES (510821, '旺苍县', 510800, 3);
INSERT INTO `9h_sys_city` VALUES (510822, '青川县', 510800, 3);
INSERT INTO `9h_sys_city` VALUES (510823, '剑阁县', 510800, 3);
INSERT INTO `9h_sys_city` VALUES (510824, '苍溪县', 510800, 3);
INSERT INTO `9h_sys_city` VALUES (510900, '遂宁市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (510903, '船山区', 510900, 3);
INSERT INTO `9h_sys_city` VALUES (510904, '安居区', 510900, 3);
INSERT INTO `9h_sys_city` VALUES (510921, '蓬溪县', 510900, 3);
INSERT INTO `9h_sys_city` VALUES (510922, '射洪县', 510900, 3);
INSERT INTO `9h_sys_city` VALUES (510923, '大英县', 510900, 3);
INSERT INTO `9h_sys_city` VALUES (511000, '内江市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511002, '市中区', 511000, 3);
INSERT INTO `9h_sys_city` VALUES (511011, '东兴区', 511000, 3);
INSERT INTO `9h_sys_city` VALUES (511024, '威远县', 511000, 3);
INSERT INTO `9h_sys_city` VALUES (511025, '资中县', 511000, 3);
INSERT INTO `9h_sys_city` VALUES (511028, '隆昌县', 511000, 3);
INSERT INTO `9h_sys_city` VALUES (511100, '乐山市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511102, '市中区', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511111, '沙湾区', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511112, '五通桥区', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511113, '金口河区', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511123, '犍为县', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511124, '井研县', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511126, '夹江县', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511129, '沐川县', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511132, '峨边彝族自治县', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511133, '马边彝族自治县', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511181, '峨眉山市', 511100, 3);
INSERT INTO `9h_sys_city` VALUES (511300, '南充市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511302, '顺庆区', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511303, '高坪区', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511304, '嘉陵区', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511321, '南部县', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511322, '营山县', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511323, '蓬安县', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511324, '仪陇县', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511325, '西充县', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511381, '阆中市', 511300, 3);
INSERT INTO `9h_sys_city` VALUES (511400, '眉山市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511402, '东坡区', 511400, 3);
INSERT INTO `9h_sys_city` VALUES (511421, '仁寿县', 511400, 3);
INSERT INTO `9h_sys_city` VALUES (511422, '彭山县', 511400, 3);
INSERT INTO `9h_sys_city` VALUES (511423, '洪雅县', 511400, 3);
INSERT INTO `9h_sys_city` VALUES (511424, '丹棱县', 511400, 3);
INSERT INTO `9h_sys_city` VALUES (511425, '青神县', 511400, 3);
INSERT INTO `9h_sys_city` VALUES (511500, '宜宾市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511502, '翠屏区', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511503, '南溪区', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511521, '宜宾县', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511523, '江安县', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511524, '长宁县', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511525, '高县', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511526, '珙县', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511527, '筠连县', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511528, '兴文县', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511529, '屏山县', 511500, 3);
INSERT INTO `9h_sys_city` VALUES (511600, '广安市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511602, '广安区', 511600, 3);
INSERT INTO `9h_sys_city` VALUES (511603, '前锋区', 511600, 3);
INSERT INTO `9h_sys_city` VALUES (511621, '岳池县', 511600, 3);
INSERT INTO `9h_sys_city` VALUES (511622, '武胜县', 511600, 3);
INSERT INTO `9h_sys_city` VALUES (511623, '邻水县', 511600, 3);
INSERT INTO `9h_sys_city` VALUES (511681, '华蓥市', 511600, 3);
INSERT INTO `9h_sys_city` VALUES (511700, '达州市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511702, '通川区', 511700, 3);
INSERT INTO `9h_sys_city` VALUES (511703, '达川区', 511700, 3);
INSERT INTO `9h_sys_city` VALUES (511722, '宣汉县', 511700, 3);
INSERT INTO `9h_sys_city` VALUES (511723, '开江县', 511700, 3);
INSERT INTO `9h_sys_city` VALUES (511724, '大竹县', 511700, 3);
INSERT INTO `9h_sys_city` VALUES (511725, '渠县', 511700, 3);
INSERT INTO `9h_sys_city` VALUES (511781, '万源市', 511700, 3);
INSERT INTO `9h_sys_city` VALUES (511800, '雅安市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511802, '雨城区', 511800, 3);
INSERT INTO `9h_sys_city` VALUES (511803, '名山区', 511800, 3);
INSERT INTO `9h_sys_city` VALUES (511822, '荥经县', 511800, 3);
INSERT INTO `9h_sys_city` VALUES (511823, '汉源县', 511800, 3);
INSERT INTO `9h_sys_city` VALUES (511824, '石棉县', 511800, 3);
INSERT INTO `9h_sys_city` VALUES (511825, '天全县', 511800, 3);
INSERT INTO `9h_sys_city` VALUES (511826, '芦山县', 511800, 3);
INSERT INTO `9h_sys_city` VALUES (511827, '宝兴县', 511800, 3);
INSERT INTO `9h_sys_city` VALUES (511900, '巴中市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (511902, '巴州区', 511900, 3);
INSERT INTO `9h_sys_city` VALUES (511903, '恩阳区', 511900, 3);
INSERT INTO `9h_sys_city` VALUES (511921, '通江县', 511900, 3);
INSERT INTO `9h_sys_city` VALUES (511922, '南江县', 511900, 3);
INSERT INTO `9h_sys_city` VALUES (511923, '平昌县', 511900, 3);
INSERT INTO `9h_sys_city` VALUES (512000, '资阳市', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (512002, '雁江区', 512000, 3);
INSERT INTO `9h_sys_city` VALUES (512021, '安岳县', 512000, 3);
INSERT INTO `9h_sys_city` VALUES (512022, '乐至县', 512000, 3);
INSERT INTO `9h_sys_city` VALUES (512081, '简阳市', 512000, 3);
INSERT INTO `9h_sys_city` VALUES (513200, '阿坝藏族羌族自治州', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (513221, '汶川县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513222, '理县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513223, '茂县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513224, '松潘县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513225, '九寨沟县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513226, '金川县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513227, '小金县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513228, '黑水县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513229, '马尔康县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513230, '壤塘县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513231, '阿坝县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513232, '若尔盖县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513233, '红原县', 513200, 3);
INSERT INTO `9h_sys_city` VALUES (513300, '甘孜藏族自治州', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (513321, '康定县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513322, '泸定县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513323, '丹巴县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513324, '九龙县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513325, '雅江县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513326, '道孚县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513327, '炉霍县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513328, '甘孜县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513329, '新龙县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513330, '德格县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513331, '白玉县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513332, '石渠县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513333, '色达县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513334, '理塘县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513335, '巴塘县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513336, '乡城县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513337, '稻城县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513338, '得荣县', 513300, 3);
INSERT INTO `9h_sys_city` VALUES (513400, '凉山彝族自治州', 510000, 2);
INSERT INTO `9h_sys_city` VALUES (513401, '西昌市', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513422, '木里藏族自治县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513423, '盐源县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513424, '德昌县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513425, '会理县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513426, '会东县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513427, '宁南县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513428, '普格县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513429, '布拖县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513430, '金阳县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513431, '昭觉县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513432, '喜德县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513433, '冕宁县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513434, '越西县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513435, '甘洛县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513436, '美姑县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (513437, '雷波县', 513400, 3);
INSERT INTO `9h_sys_city` VALUES (520000, '贵州省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (520100, '贵阳市', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (520102, '南明区', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520103, '云岩区', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520111, '花溪区', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520112, '乌当区', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520113, '白云区', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520115, '观山湖区', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520121, '开阳县', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520122, '息烽县', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520123, '修文县', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520181, '清镇市', 520100, 3);
INSERT INTO `9h_sys_city` VALUES (520200, '六盘水市', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (520201, '钟山区', 520200, 3);
INSERT INTO `9h_sys_city` VALUES (520203, '六枝特区', 520200, 3);
INSERT INTO `9h_sys_city` VALUES (520221, '水城县', 520200, 3);
INSERT INTO `9h_sys_city` VALUES (520222, '盘县', 520200, 3);
INSERT INTO `9h_sys_city` VALUES (520300, '遵义市', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (520302, '红花岗区', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520303, '汇川区', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520321, '遵义县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520322, '桐梓县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520323, '绥阳县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520324, '正安县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520325, '道真仡佬族苗族自治县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520326, '务川仡佬族苗族自治县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520327, '凤冈县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520328, '湄潭县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520329, '余庆县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520330, '习水县', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520381, '赤水市', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520382, '仁怀市', 520300, 3);
INSERT INTO `9h_sys_city` VALUES (520400, '安顺市', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (520402, '西秀区', 520400, 3);
INSERT INTO `9h_sys_city` VALUES (520421, '平坝县', 520400, 3);
INSERT INTO `9h_sys_city` VALUES (520422, '普定县', 520400, 3);
INSERT INTO `9h_sys_city` VALUES (520423, '镇宁布依族苗族自治县', 520400, 3);
INSERT INTO `9h_sys_city` VALUES (520424, '关岭布依族苗族自治县', 520400, 3);
INSERT INTO `9h_sys_city` VALUES (520425, '紫云苗族布依族自治县', 520400, 3);
INSERT INTO `9h_sys_city` VALUES (520500, '毕节市', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (520502, '七星关区', 520500, 3);
INSERT INTO `9h_sys_city` VALUES (520521, '大方县', 520500, 3);
INSERT INTO `9h_sys_city` VALUES (520522, '黔西县', 520500, 3);
INSERT INTO `9h_sys_city` VALUES (520523, '金沙县', 520500, 3);
INSERT INTO `9h_sys_city` VALUES (520524, '织金县', 520500, 3);
INSERT INTO `9h_sys_city` VALUES (520525, '纳雍县', 520500, 3);
INSERT INTO `9h_sys_city` VALUES (520526, '威宁彝族回族苗族自治县', 520500, 3);
INSERT INTO `9h_sys_city` VALUES (520527, '赫章县', 520500, 3);
INSERT INTO `9h_sys_city` VALUES (520600, '铜仁市', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (520602, '碧江区', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520603, '万山区', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520621, '江口县', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520622, '玉屏侗族自治县', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520623, '石阡县', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520624, '思南县', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520625, '印江土家族苗族自治县', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520626, '德江县', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520627, '沿河土家族自治县', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (520628, '松桃苗族自治县', 520600, 3);
INSERT INTO `9h_sys_city` VALUES (522300, '黔西南布依族苗族自治州', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (522301, '兴义市', 522300, 3);
INSERT INTO `9h_sys_city` VALUES (522322, '兴仁县', 522300, 3);
INSERT INTO `9h_sys_city` VALUES (522323, '普安县', 522300, 3);
INSERT INTO `9h_sys_city` VALUES (522324, '晴隆县', 522300, 3);
INSERT INTO `9h_sys_city` VALUES (522325, '贞丰县', 522300, 3);
INSERT INTO `9h_sys_city` VALUES (522326, '望谟县', 522300, 3);
INSERT INTO `9h_sys_city` VALUES (522327, '册亨县', 522300, 3);
INSERT INTO `9h_sys_city` VALUES (522328, '安龙县', 522300, 3);
INSERT INTO `9h_sys_city` VALUES (522600, '黔东南苗族侗族自治州', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (522601, '凯里市', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522622, '黄平县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522623, '施秉县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522624, '三穗县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522625, '镇远县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522626, '岑巩县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522627, '天柱县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522628, '锦屏县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522629, '剑河县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522630, '台江县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522631, '黎平县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522632, '榕江县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522633, '从江县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522634, '雷山县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522635, '麻江县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522636, '丹寨县', 522600, 3);
INSERT INTO `9h_sys_city` VALUES (522700, '黔南布依族苗族自治州', 520000, 2);
INSERT INTO `9h_sys_city` VALUES (522701, '都匀市', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522702, '福泉市', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522722, '荔波县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522723, '贵定县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522725, '瓮安县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522726, '独山县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522727, '平塘县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522728, '罗甸县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522729, '长顺县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522730, '龙里县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522731, '惠水县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (522732, '三都水族自治县', 522700, 3);
INSERT INTO `9h_sys_city` VALUES (530000, '云南省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (530100, '昆明市', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (530102, '五华区', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530103, '盘龙区', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530111, '官渡区', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530112, '西山区', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530113, '东川区', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530114, '呈贡区', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530122, '晋宁县', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530124, '富民县', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530125, '宜良县', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530126, '石林彝族自治县', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530127, '嵩明县', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530128, '禄劝彝族苗族自治县', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530129, '寻甸回族彝族自治县', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530181, '安宁市', 530100, 3);
INSERT INTO `9h_sys_city` VALUES (530300, '曲靖市', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (530302, '麒麟区', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530321, '马龙县', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530322, '陆良县', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530323, '师宗县', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530324, '罗平县', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530325, '富源县', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530326, '会泽县', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530328, '沾益县', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530381, '宣威市', 530300, 3);
INSERT INTO `9h_sys_city` VALUES (530400, '玉溪市', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (530402, '红塔区', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530421, '江川县', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530422, '澄江县', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530423, '通海县', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530424, '华宁县', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530425, '易门县', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530426, '峨山彝族自治县', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530427, '新平彝族傣族自治县', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530428, '元江哈尼族彝族傣族自治县', 530400, 3);
INSERT INTO `9h_sys_city` VALUES (530500, '保山市', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (530502, '隆阳区', 530500, 3);
INSERT INTO `9h_sys_city` VALUES (530521, '施甸县', 530500, 3);
INSERT INTO `9h_sys_city` VALUES (530522, '腾冲县', 530500, 3);
INSERT INTO `9h_sys_city` VALUES (530523, '龙陵县', 530500, 3);
INSERT INTO `9h_sys_city` VALUES (530524, '昌宁县', 530500, 3);
INSERT INTO `9h_sys_city` VALUES (530600, '昭通市', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (530602, '昭阳区', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530621, '鲁甸县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530622, '巧家县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530623, '盐津县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530624, '大关县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530625, '永善县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530626, '绥江县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530627, '镇雄县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530628, '彝良县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530629, '威信县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530630, '水富县', 530600, 3);
INSERT INTO `9h_sys_city` VALUES (530700, '丽江市', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (530702, '古城区', 530700, 3);
INSERT INTO `9h_sys_city` VALUES (530721, '玉龙纳西族自治县', 530700, 3);
INSERT INTO `9h_sys_city` VALUES (530722, '永胜县', 530700, 3);
INSERT INTO `9h_sys_city` VALUES (530723, '华坪县', 530700, 3);
INSERT INTO `9h_sys_city` VALUES (530724, '宁蒗彝族自治县', 530700, 3);
INSERT INTO `9h_sys_city` VALUES (530800, '普洱市', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (530802, '思茅区', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530821, '宁洱哈尼族彝族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530822, '墨江哈尼族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530823, '景东彝族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530824, '景谷傣族彝族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530825, '镇沅彝族哈尼族拉祜族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530826, '江城哈尼族彝族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530827, '孟连傣族拉祜族佤族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530828, '澜沧拉祜族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530829, '西盟佤族自治县', 530800, 3);
INSERT INTO `9h_sys_city` VALUES (530900, '临沧市', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (530902, '临翔区', 530900, 3);
INSERT INTO `9h_sys_city` VALUES (530921, '凤庆县', 530900, 3);
INSERT INTO `9h_sys_city` VALUES (530922, '云县', 530900, 3);
INSERT INTO `9h_sys_city` VALUES (530923, '永德县', 530900, 3);
INSERT INTO `9h_sys_city` VALUES (530924, '镇康县', 530900, 3);
INSERT INTO `9h_sys_city` VALUES (530925, '双江拉祜族佤族布朗族傣族自治县', 530900, 3);
INSERT INTO `9h_sys_city` VALUES (530926, '耿马傣族佤族自治县', 530900, 3);
INSERT INTO `9h_sys_city` VALUES (530927, '沧源佤族自治县', 530900, 3);
INSERT INTO `9h_sys_city` VALUES (532300, '楚雄彝族自治州', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (532301, '楚雄市', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532322, '双柏县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532323, '牟定县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532324, '南华县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532325, '姚安县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532326, '大姚县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532327, '永仁县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532328, '元谋县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532329, '武定县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532331, '禄丰县', 532300, 3);
INSERT INTO `9h_sys_city` VALUES (532500, '红河哈尼族彝族自治州', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (532501, '个旧市', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532502, '开远市', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532503, '蒙自市', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532504, '弥勒市', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532523, '屏边苗族自治县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532524, '建水县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532525, '石屏县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532527, '泸西县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532528, '元阳县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532529, '红河县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532530, '金平苗族瑶族傣族自治县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532531, '绿春县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532532, '河口瑶族自治县', 532500, 3);
INSERT INTO `9h_sys_city` VALUES (532600, '文山壮族苗族自治州', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (532601, '文山市', 532600, 3);
INSERT INTO `9h_sys_city` VALUES (532622, '砚山县', 532600, 3);
INSERT INTO `9h_sys_city` VALUES (532623, '西畴县', 532600, 3);
INSERT INTO `9h_sys_city` VALUES (532624, '麻栗坡县', 532600, 3);
INSERT INTO `9h_sys_city` VALUES (532625, '马关县', 532600, 3);
INSERT INTO `9h_sys_city` VALUES (532626, '丘北县', 532600, 3);
INSERT INTO `9h_sys_city` VALUES (532627, '广南县', 532600, 3);
INSERT INTO `9h_sys_city` VALUES (532628, '富宁县', 532600, 3);
INSERT INTO `9h_sys_city` VALUES (532800, '西双版纳傣族自治州', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (532801, '景洪市', 532800, 3);
INSERT INTO `9h_sys_city` VALUES (532822, '勐海县', 532800, 3);
INSERT INTO `9h_sys_city` VALUES (532823, '勐腊县', 532800, 3);
INSERT INTO `9h_sys_city` VALUES (532900, '大理白族自治州', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (532901, '大理市', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532922, '漾濞彝族自治县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532923, '祥云县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532924, '宾川县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532925, '弥渡县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532926, '南涧彝族自治县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532927, '巍山彝族回族自治县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532928, '永平县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532929, '云龙县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532930, '洱源县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532931, '剑川县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (532932, '鹤庆县', 532900, 3);
INSERT INTO `9h_sys_city` VALUES (533100, '德宏傣族景颇族自治州', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (533102, '瑞丽市', 533100, 3);
INSERT INTO `9h_sys_city` VALUES (533103, '芒市', 533100, 3);
INSERT INTO `9h_sys_city` VALUES (533122, '梁河县', 533100, 3);
INSERT INTO `9h_sys_city` VALUES (533123, '盈江县', 533100, 3);
INSERT INTO `9h_sys_city` VALUES (533124, '陇川县', 533100, 3);
INSERT INTO `9h_sys_city` VALUES (533300, '怒江傈僳族自治州', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (533321, '泸水县', 533300, 3);
INSERT INTO `9h_sys_city` VALUES (533323, '福贡县', 533300, 3);
INSERT INTO `9h_sys_city` VALUES (533324, '贡山独龙族怒族自治县', 533300, 3);
INSERT INTO `9h_sys_city` VALUES (533325, '兰坪白族普米族自治县', 533300, 3);
INSERT INTO `9h_sys_city` VALUES (533400, '迪庆藏族自治州', 530000, 2);
INSERT INTO `9h_sys_city` VALUES (533421, '香格里拉县', 533400, 3);
INSERT INTO `9h_sys_city` VALUES (533422, '德钦县', 533400, 3);
INSERT INTO `9h_sys_city` VALUES (533423, '维西傈僳族自治县', 533400, 3);
INSERT INTO `9h_sys_city` VALUES (540000, '西藏自治区', 0, 1);
INSERT INTO `9h_sys_city` VALUES (540100, '拉萨市', 540000, 2);
INSERT INTO `9h_sys_city` VALUES (540102, '城关区', 540100, 3);
INSERT INTO `9h_sys_city` VALUES (540121, '林周县', 540100, 3);
INSERT INTO `9h_sys_city` VALUES (540122, '当雄县', 540100, 3);
INSERT INTO `9h_sys_city` VALUES (540123, '尼木县', 540100, 3);
INSERT INTO `9h_sys_city` VALUES (540124, '曲水县', 540100, 3);
INSERT INTO `9h_sys_city` VALUES (540125, '堆龙德庆县', 540100, 3);
INSERT INTO `9h_sys_city` VALUES (540126, '达孜县', 540100, 3);
INSERT INTO `9h_sys_city` VALUES (540127, '墨竹工卡县', 540100, 3);
INSERT INTO `9h_sys_city` VALUES (540200, '日喀则市', 540000, 2);
INSERT INTO `9h_sys_city` VALUES (540202, '桑珠孜区', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540221, '南木林县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540222, '江孜县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540223, '定日县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540224, '萨迦县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540225, '拉孜县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540226, '昂仁县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540227, '谢通门县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540228, '白朗县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540229, '仁布县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540230, '康马县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540231, '定结县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540232, '仲巴县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540233, '亚东县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540234, '吉隆县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540235, '聂拉木县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540236, '萨嘎县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (540237, '岗巴县', 540200, 3);
INSERT INTO `9h_sys_city` VALUES (542100, '昌都地区', 540000, 2);
INSERT INTO `9h_sys_city` VALUES (542121, '昌都县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542122, '江达县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542123, '贡觉县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542124, '类乌齐县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542125, '丁青县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542126, '察雅县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542127, '八宿县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542128, '左贡县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542129, '芒康县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542132, '洛隆县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542133, '边坝县', 542100, 3);
INSERT INTO `9h_sys_city` VALUES (542200, '山南地区', 540000, 2);
INSERT INTO `9h_sys_city` VALUES (542221, '乃东县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542222, '扎囊县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542223, '贡嘎县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542224, '桑日县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542225, '琼结县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542226, '曲松县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542227, '措美县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542228, '洛扎县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542229, '加查县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542231, '隆子县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542232, '错那县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542233, '浪卡子县', 542200, 3);
INSERT INTO `9h_sys_city` VALUES (542400, '那曲地区', 540000, 2);
INSERT INTO `9h_sys_city` VALUES (542421, '那曲县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542422, '嘉黎县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542423, '比如县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542424, '聂荣县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542425, '安多县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542426, '申扎县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542427, '索县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542428, '班戈县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542429, '巴青县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542430, '尼玛县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542431, '双湖县', 542400, 3);
INSERT INTO `9h_sys_city` VALUES (542500, '阿里地区', 540000, 2);
INSERT INTO `9h_sys_city` VALUES (542521, '普兰县', 542500, 3);
INSERT INTO `9h_sys_city` VALUES (542522, '札达县', 542500, 3);
INSERT INTO `9h_sys_city` VALUES (542523, '噶尔县', 542500, 3);
INSERT INTO `9h_sys_city` VALUES (542524, '日土县', 542500, 3);
INSERT INTO `9h_sys_city` VALUES (542525, '革吉县', 542500, 3);
INSERT INTO `9h_sys_city` VALUES (542526, '改则县', 542500, 3);
INSERT INTO `9h_sys_city` VALUES (542527, '措勤县', 542500, 3);
INSERT INTO `9h_sys_city` VALUES (542600, '林芝地区', 540000, 2);
INSERT INTO `9h_sys_city` VALUES (542621, '林芝县', 542600, 3);
INSERT INTO `9h_sys_city` VALUES (542622, '工布江达县', 542600, 3);
INSERT INTO `9h_sys_city` VALUES (542623, '米林县', 542600, 3);
INSERT INTO `9h_sys_city` VALUES (542624, '墨脱县', 542600, 3);
INSERT INTO `9h_sys_city` VALUES (542625, '波密县', 542600, 3);
INSERT INTO `9h_sys_city` VALUES (542626, '察隅县', 542600, 3);
INSERT INTO `9h_sys_city` VALUES (542627, '朗县', 542600, 3);
INSERT INTO `9h_sys_city` VALUES (610000, '陕西省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (610100, '西安市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610102, '新城区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610103, '碑林区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610104, '莲湖区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610111, '灞桥区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610112, '未央区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610113, '雁塔区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610114, '阎良区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610115, '临潼区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610116, '长安区', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610122, '蓝田县', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610124, '周至县', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610125, '户县', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610126, '高陵县', 610100, 3);
INSERT INTO `9h_sys_city` VALUES (610200, '铜川市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610202, '王益区', 610200, 3);
INSERT INTO `9h_sys_city` VALUES (610203, '印台区', 610200, 3);
INSERT INTO `9h_sys_city` VALUES (610204, '耀州区', 610200, 3);
INSERT INTO `9h_sys_city` VALUES (610222, '宜君县', 610200, 3);
INSERT INTO `9h_sys_city` VALUES (610300, '宝鸡市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610302, '渭滨区', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610303, '金台区', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610304, '陈仓区', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610322, '凤翔县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610323, '岐山县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610324, '扶风县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610326, '眉县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610327, '陇县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610328, '千阳县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610329, '麟游县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610330, '凤县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610331, '太白县', 610300, 3);
INSERT INTO `9h_sys_city` VALUES (610400, '咸阳市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610402, '秦都区', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610403, '杨陵区', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610404, '渭城区', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610422, '三原县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610423, '泾阳县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610424, '乾县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610425, '礼泉县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610426, '永寿县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610427, '彬县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610428, '长武县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610429, '旬邑县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610430, '淳化县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610431, '武功县', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610481, '兴平市', 610400, 3);
INSERT INTO `9h_sys_city` VALUES (610500, '渭南市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610502, '临渭区', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610521, '华县', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610522, '潼关县', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610523, '大荔县', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610524, '合阳县', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610525, '澄城县', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610526, '蒲城县', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610527, '白水县', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610528, '富平县', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610581, '韩城市', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610582, '华阴市', 610500, 3);
INSERT INTO `9h_sys_city` VALUES (610600, '延安市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610602, '宝塔区', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610621, '延长县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610622, '延川县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610623, '子长县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610624, '安塞县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610625, '志丹县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610626, '吴起县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610627, '甘泉县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610628, '富县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610629, '洛川县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610630, '宜川县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610631, '黄龙县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610632, '黄陵县', 610600, 3);
INSERT INTO `9h_sys_city` VALUES (610700, '汉中市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610702, '汉台区', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610721, '南郑县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610722, '城固县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610723, '洋县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610724, '西乡县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610725, '勉县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610726, '宁强县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610727, '略阳县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610728, '镇巴县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610729, '留坝县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610730, '佛坪县', 610700, 3);
INSERT INTO `9h_sys_city` VALUES (610800, '榆林市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610802, '榆阳区', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610821, '神木县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610822, '府谷县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610823, '横山县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610824, '靖边县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610825, '定边县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610826, '绥德县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610827, '米脂县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610828, '佳县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610829, '吴堡县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610830, '清涧县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610831, '子洲县', 610800, 3);
INSERT INTO `9h_sys_city` VALUES (610900, '安康市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (610902, '汉滨区', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610921, '汉阴县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610922, '石泉县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610923, '宁陕县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610924, '紫阳县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610925, '岚皋县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610926, '平利县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610927, '镇坪县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610928, '旬阳县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (610929, '白河县', 610900, 3);
INSERT INTO `9h_sys_city` VALUES (611000, '商洛市', 610000, 2);
INSERT INTO `9h_sys_city` VALUES (611002, '商州区', 611000, 3);
INSERT INTO `9h_sys_city` VALUES (611021, '洛南县', 611000, 3);
INSERT INTO `9h_sys_city` VALUES (611022, '丹凤县', 611000, 3);
INSERT INTO `9h_sys_city` VALUES (611023, '商南县', 611000, 3);
INSERT INTO `9h_sys_city` VALUES (611024, '山阳县', 611000, 3);
INSERT INTO `9h_sys_city` VALUES (611025, '镇安县', 611000, 3);
INSERT INTO `9h_sys_city` VALUES (611026, '柞水县', 611000, 3);
INSERT INTO `9h_sys_city` VALUES (620000, '甘肃省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (620100, '兰州市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620102, '城关区', 620100, 3);
INSERT INTO `9h_sys_city` VALUES (620103, '七里河区', 620100, 3);
INSERT INTO `9h_sys_city` VALUES (620104, '西固区', 620100, 3);
INSERT INTO `9h_sys_city` VALUES (620105, '安宁区', 620100, 3);
INSERT INTO `9h_sys_city` VALUES (620111, '红古区', 620100, 3);
INSERT INTO `9h_sys_city` VALUES (620121, '永登县', 620100, 3);
INSERT INTO `9h_sys_city` VALUES (620122, '皋兰县', 620100, 3);
INSERT INTO `9h_sys_city` VALUES (620123, '榆中县', 620100, 3);
INSERT INTO `9h_sys_city` VALUES (620200, '嘉峪关市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620300, '金昌市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620302, '金川区', 620300, 3);
INSERT INTO `9h_sys_city` VALUES (620321, '永昌县', 620300, 3);
INSERT INTO `9h_sys_city` VALUES (620400, '白银市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620402, '白银区', 620400, 3);
INSERT INTO `9h_sys_city` VALUES (620403, '平川区', 620400, 3);
INSERT INTO `9h_sys_city` VALUES (620421, '靖远县', 620400, 3);
INSERT INTO `9h_sys_city` VALUES (620422, '会宁县', 620400, 3);
INSERT INTO `9h_sys_city` VALUES (620423, '景泰县', 620400, 3);
INSERT INTO `9h_sys_city` VALUES (620500, '天水市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620502, '秦州区', 620500, 3);
INSERT INTO `9h_sys_city` VALUES (620503, '麦积区', 620500, 3);
INSERT INTO `9h_sys_city` VALUES (620521, '清水县', 620500, 3);
INSERT INTO `9h_sys_city` VALUES (620522, '秦安县', 620500, 3);
INSERT INTO `9h_sys_city` VALUES (620523, '甘谷县', 620500, 3);
INSERT INTO `9h_sys_city` VALUES (620524, '武山县', 620500, 3);
INSERT INTO `9h_sys_city` VALUES (620525, '张家川回族自治县', 620500, 3);
INSERT INTO `9h_sys_city` VALUES (620600, '武威市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620602, '凉州区', 620600, 3);
INSERT INTO `9h_sys_city` VALUES (620621, '民勤县', 620600, 3);
INSERT INTO `9h_sys_city` VALUES (620622, '古浪县', 620600, 3);
INSERT INTO `9h_sys_city` VALUES (620623, '天祝藏族自治县', 620600, 3);
INSERT INTO `9h_sys_city` VALUES (620700, '张掖市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620702, '甘州区', 620700, 3);
INSERT INTO `9h_sys_city` VALUES (620721, '肃南裕固族自治县', 620700, 3);
INSERT INTO `9h_sys_city` VALUES (620722, '民乐县', 620700, 3);
INSERT INTO `9h_sys_city` VALUES (620723, '临泽县', 620700, 3);
INSERT INTO `9h_sys_city` VALUES (620724, '高台县', 620700, 3);
INSERT INTO `9h_sys_city` VALUES (620725, '山丹县', 620700, 3);
INSERT INTO `9h_sys_city` VALUES (620800, '平凉市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620802, '崆峒区', 620800, 3);
INSERT INTO `9h_sys_city` VALUES (620821, '泾川县', 620800, 3);
INSERT INTO `9h_sys_city` VALUES (620822, '灵台县', 620800, 3);
INSERT INTO `9h_sys_city` VALUES (620823, '崇信县', 620800, 3);
INSERT INTO `9h_sys_city` VALUES (620824, '华亭县', 620800, 3);
INSERT INTO `9h_sys_city` VALUES (620825, '庄浪县', 620800, 3);
INSERT INTO `9h_sys_city` VALUES (620826, '静宁县', 620800, 3);
INSERT INTO `9h_sys_city` VALUES (620900, '酒泉市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (620902, '肃州区', 620900, 3);
INSERT INTO `9h_sys_city` VALUES (620921, '金塔县', 620900, 3);
INSERT INTO `9h_sys_city` VALUES (620922, '瓜州县', 620900, 3);
INSERT INTO `9h_sys_city` VALUES (620923, '肃北蒙古族自治县', 620900, 3);
INSERT INTO `9h_sys_city` VALUES (620924, '阿克塞哈萨克族自治县', 620900, 3);
INSERT INTO `9h_sys_city` VALUES (620981, '玉门市', 620900, 3);
INSERT INTO `9h_sys_city` VALUES (620982, '敦煌市', 620900, 3);
INSERT INTO `9h_sys_city` VALUES (621000, '庆阳市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (621002, '西峰区', 621000, 3);
INSERT INTO `9h_sys_city` VALUES (621021, '庆城县', 621000, 3);
INSERT INTO `9h_sys_city` VALUES (621022, '环县', 621000, 3);
INSERT INTO `9h_sys_city` VALUES (621023, '华池县', 621000, 3);
INSERT INTO `9h_sys_city` VALUES (621024, '合水县', 621000, 3);
INSERT INTO `9h_sys_city` VALUES (621025, '正宁县', 621000, 3);
INSERT INTO `9h_sys_city` VALUES (621026, '宁县', 621000, 3);
INSERT INTO `9h_sys_city` VALUES (621027, '镇原县', 621000, 3);
INSERT INTO `9h_sys_city` VALUES (621100, '定西市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (621102, '安定区', 621100, 3);
INSERT INTO `9h_sys_city` VALUES (621121, '通渭县', 621100, 3);
INSERT INTO `9h_sys_city` VALUES (621122, '陇西县', 621100, 3);
INSERT INTO `9h_sys_city` VALUES (621123, '渭源县', 621100, 3);
INSERT INTO `9h_sys_city` VALUES (621124, '临洮县', 621100, 3);
INSERT INTO `9h_sys_city` VALUES (621125, '漳县', 621100, 3);
INSERT INTO `9h_sys_city` VALUES (621126, '岷县', 621100, 3);
INSERT INTO `9h_sys_city` VALUES (621200, '陇南市', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (621202, '武都区', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (621221, '成县', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (621222, '文县', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (621223, '宕昌县', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (621224, '康县', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (621225, '西和县', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (621226, '礼县', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (621227, '徽县', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (621228, '两当县', 621200, 3);
INSERT INTO `9h_sys_city` VALUES (622900, '临夏回族自治州', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (622901, '临夏市', 622900, 3);
INSERT INTO `9h_sys_city` VALUES (622921, '临夏县', 622900, 3);
INSERT INTO `9h_sys_city` VALUES (622922, '康乐县', 622900, 3);
INSERT INTO `9h_sys_city` VALUES (622923, '永靖县', 622900, 3);
INSERT INTO `9h_sys_city` VALUES (622924, '广河县', 622900, 3);
INSERT INTO `9h_sys_city` VALUES (622925, '和政县', 622900, 3);
INSERT INTO `9h_sys_city` VALUES (622926, '东乡族自治县', 622900, 3);
INSERT INTO `9h_sys_city` VALUES (622927, '积石山保安族东乡族撒拉族自治县', 622900, 3);
INSERT INTO `9h_sys_city` VALUES (623000, '甘南藏族自治州', 620000, 2);
INSERT INTO `9h_sys_city` VALUES (623001, '合作市', 623000, 3);
INSERT INTO `9h_sys_city` VALUES (623021, '临潭县', 623000, 3);
INSERT INTO `9h_sys_city` VALUES (623022, '卓尼县', 623000, 3);
INSERT INTO `9h_sys_city` VALUES (623023, '舟曲县', 623000, 3);
INSERT INTO `9h_sys_city` VALUES (623024, '迭部县', 623000, 3);
INSERT INTO `9h_sys_city` VALUES (623025, '玛曲县', 623000, 3);
INSERT INTO `9h_sys_city` VALUES (623026, '碌曲县', 623000, 3);
INSERT INTO `9h_sys_city` VALUES (623027, '夏河县', 623000, 3);
INSERT INTO `9h_sys_city` VALUES (630000, '青海省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (630100, '西宁市', 630000, 2);
INSERT INTO `9h_sys_city` VALUES (630102, '城东区', 630100, 3);
INSERT INTO `9h_sys_city` VALUES (630103, '城中区', 630100, 3);
INSERT INTO `9h_sys_city` VALUES (630104, '城西区', 630100, 3);
INSERT INTO `9h_sys_city` VALUES (630105, '城北区', 630100, 3);
INSERT INTO `9h_sys_city` VALUES (630121, '大通回族土族自治县', 630100, 3);
INSERT INTO `9h_sys_city` VALUES (630122, '湟中县', 630100, 3);
INSERT INTO `9h_sys_city` VALUES (630123, '湟源县', 630100, 3);
INSERT INTO `9h_sys_city` VALUES (630200, '海东市', 630000, 2);
INSERT INTO `9h_sys_city` VALUES (630202, '乐都区', 630200, 3);
INSERT INTO `9h_sys_city` VALUES (630221, '平安县', 630200, 3);
INSERT INTO `9h_sys_city` VALUES (630222, '民和回族土族自治县', 630200, 3);
INSERT INTO `9h_sys_city` VALUES (630223, '互助土族自治县', 630200, 3);
INSERT INTO `9h_sys_city` VALUES (630224, '化隆回族自治县', 630200, 3);
INSERT INTO `9h_sys_city` VALUES (630225, '循化撒拉族自治县', 630200, 3);
INSERT INTO `9h_sys_city` VALUES (632200, '海北藏族自治州', 630000, 2);
INSERT INTO `9h_sys_city` VALUES (632221, '门源回族自治县', 632200, 3);
INSERT INTO `9h_sys_city` VALUES (632222, '祁连县', 632200, 3);
INSERT INTO `9h_sys_city` VALUES (632223, '海晏县', 632200, 3);
INSERT INTO `9h_sys_city` VALUES (632224, '刚察县', 632200, 3);
INSERT INTO `9h_sys_city` VALUES (632300, '黄南藏族自治州', 630000, 2);
INSERT INTO `9h_sys_city` VALUES (632321, '同仁县', 632300, 3);
INSERT INTO `9h_sys_city` VALUES (632322, '尖扎县', 632300, 3);
INSERT INTO `9h_sys_city` VALUES (632323, '泽库县', 632300, 3);
INSERT INTO `9h_sys_city` VALUES (632324, '河南蒙古族自治县', 632300, 3);
INSERT INTO `9h_sys_city` VALUES (632500, '海南藏族自治州', 630000, 2);
INSERT INTO `9h_sys_city` VALUES (632521, '共和县', 632500, 3);
INSERT INTO `9h_sys_city` VALUES (632522, '同德县', 632500, 3);
INSERT INTO `9h_sys_city` VALUES (632523, '贵德县', 632500, 3);
INSERT INTO `9h_sys_city` VALUES (632524, '兴海县', 632500, 3);
INSERT INTO `9h_sys_city` VALUES (632525, '贵南县', 632500, 3);
INSERT INTO `9h_sys_city` VALUES (632600, '果洛藏族自治州', 630000, 2);
INSERT INTO `9h_sys_city` VALUES (632621, '玛沁县', 632600, 3);
INSERT INTO `9h_sys_city` VALUES (632622, '班玛县', 632600, 3);
INSERT INTO `9h_sys_city` VALUES (632623, '甘德县', 632600, 3);
INSERT INTO `9h_sys_city` VALUES (632624, '达日县', 632600, 3);
INSERT INTO `9h_sys_city` VALUES (632625, '久治县', 632600, 3);
INSERT INTO `9h_sys_city` VALUES (632626, '玛多县', 632600, 3);
INSERT INTO `9h_sys_city` VALUES (632700, '玉树藏族自治州', 630000, 2);
INSERT INTO `9h_sys_city` VALUES (632701, '玉树市', 632700, 3);
INSERT INTO `9h_sys_city` VALUES (632722, '杂多县', 632700, 3);
INSERT INTO `9h_sys_city` VALUES (632723, '称多县', 632700, 3);
INSERT INTO `9h_sys_city` VALUES (632724, '治多县', 632700, 3);
INSERT INTO `9h_sys_city` VALUES (632725, '囊谦县', 632700, 3);
INSERT INTO `9h_sys_city` VALUES (632726, '曲麻莱县', 632700, 3);
INSERT INTO `9h_sys_city` VALUES (632800, '海西蒙古族藏族自治州', 630000, 2);
INSERT INTO `9h_sys_city` VALUES (632801, '格尔木市', 632800, 3);
INSERT INTO `9h_sys_city` VALUES (632802, '德令哈市', 632800, 3);
INSERT INTO `9h_sys_city` VALUES (632821, '乌兰县', 632800, 3);
INSERT INTO `9h_sys_city` VALUES (632822, '都兰县', 632800, 3);
INSERT INTO `9h_sys_city` VALUES (632823, '天峻县', 632800, 3);
INSERT INTO `9h_sys_city` VALUES (640000, '宁夏回族自治区', 0, 1);
INSERT INTO `9h_sys_city` VALUES (640100, '银川市', 640000, 2);
INSERT INTO `9h_sys_city` VALUES (640104, '兴庆区', 640100, 3);
INSERT INTO `9h_sys_city` VALUES (640105, '西夏区', 640100, 3);
INSERT INTO `9h_sys_city` VALUES (640106, '金凤区', 640100, 3);
INSERT INTO `9h_sys_city` VALUES (640121, '永宁县', 640100, 3);
INSERT INTO `9h_sys_city` VALUES (640122, '贺兰县', 640100, 3);
INSERT INTO `9h_sys_city` VALUES (640181, '灵武市', 640100, 3);
INSERT INTO `9h_sys_city` VALUES (640200, '石嘴山市', 640000, 2);
INSERT INTO `9h_sys_city` VALUES (640202, '大武口区', 640200, 3);
INSERT INTO `9h_sys_city` VALUES (640205, '惠农区', 640200, 3);
INSERT INTO `9h_sys_city` VALUES (640221, '平罗县', 640200, 3);
INSERT INTO `9h_sys_city` VALUES (640300, '吴忠市', 640000, 2);
INSERT INTO `9h_sys_city` VALUES (640302, '利通区', 640300, 3);
INSERT INTO `9h_sys_city` VALUES (640303, '红寺堡区', 640300, 3);
INSERT INTO `9h_sys_city` VALUES (640323, '盐池县', 640300, 3);
INSERT INTO `9h_sys_city` VALUES (640324, '同心县', 640300, 3);
INSERT INTO `9h_sys_city` VALUES (640381, '青铜峡市', 640300, 3);
INSERT INTO `9h_sys_city` VALUES (640400, '固原市', 640000, 2);
INSERT INTO `9h_sys_city` VALUES (640402, '原州区', 640400, 3);
INSERT INTO `9h_sys_city` VALUES (640422, '西吉县', 640400, 3);
INSERT INTO `9h_sys_city` VALUES (640423, '隆德县', 640400, 3);
INSERT INTO `9h_sys_city` VALUES (640424, '泾源县', 640400, 3);
INSERT INTO `9h_sys_city` VALUES (640425, '彭阳县', 640400, 3);
INSERT INTO `9h_sys_city` VALUES (640500, '中卫市', 640000, 2);
INSERT INTO `9h_sys_city` VALUES (640502, '沙坡头区', 640500, 3);
INSERT INTO `9h_sys_city` VALUES (640521, '中宁县', 640500, 3);
INSERT INTO `9h_sys_city` VALUES (640522, '海原县', 640500, 3);
INSERT INTO `9h_sys_city` VALUES (650000, '新疆维吾尔自治区', 0, 1);
INSERT INTO `9h_sys_city` VALUES (650100, '乌鲁木齐市', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (650102, '天山区', 650100, 3);
INSERT INTO `9h_sys_city` VALUES (650103, '沙依巴克区', 650100, 3);
INSERT INTO `9h_sys_city` VALUES (650104, '新市区', 650100, 3);
INSERT INTO `9h_sys_city` VALUES (650105, '水磨沟区', 650100, 3);
INSERT INTO `9h_sys_city` VALUES (650106, '头屯河区', 650100, 3);
INSERT INTO `9h_sys_city` VALUES (650107, '达坂城区', 650100, 3);
INSERT INTO `9h_sys_city` VALUES (650109, '米东区', 650100, 3);
INSERT INTO `9h_sys_city` VALUES (650121, '乌鲁木齐县', 650100, 3);
INSERT INTO `9h_sys_city` VALUES (650200, '克拉玛依市', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (650202, '独山子区', 650200, 3);
INSERT INTO `9h_sys_city` VALUES (650203, '克拉玛依区', 650200, 3);
INSERT INTO `9h_sys_city` VALUES (650204, '白碱滩区', 650200, 3);
INSERT INTO `9h_sys_city` VALUES (650205, '乌尔禾区', 650200, 3);
INSERT INTO `9h_sys_city` VALUES (652100, '吐鲁番地区', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (652101, '吐鲁番市', 652100, 3);
INSERT INTO `9h_sys_city` VALUES (652122, '鄯善县', 652100, 3);
INSERT INTO `9h_sys_city` VALUES (652123, '托克逊县', 652100, 3);
INSERT INTO `9h_sys_city` VALUES (652200, '哈密地区', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (652201, '哈密市', 652200, 3);
INSERT INTO `9h_sys_city` VALUES (652222, '巴里坤哈萨克自治县', 652200, 3);
INSERT INTO `9h_sys_city` VALUES (652223, '伊吾县', 652200, 3);
INSERT INTO `9h_sys_city` VALUES (652300, '昌吉回族自治州', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (652301, '昌吉市', 652300, 3);
INSERT INTO `9h_sys_city` VALUES (652302, '阜康市', 652300, 3);
INSERT INTO `9h_sys_city` VALUES (652323, '呼图壁县', 652300, 3);
INSERT INTO `9h_sys_city` VALUES (652324, '玛纳斯县', 652300, 3);
INSERT INTO `9h_sys_city` VALUES (652325, '奇台县', 652300, 3);
INSERT INTO `9h_sys_city` VALUES (652327, '吉木萨尔县', 652300, 3);
INSERT INTO `9h_sys_city` VALUES (652328, '木垒哈萨克自治县', 652300, 3);
INSERT INTO `9h_sys_city` VALUES (652700, '博尔塔拉蒙古自治州', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (652701, '博乐市', 652700, 3);
INSERT INTO `9h_sys_city` VALUES (652702, '阿拉山口市', 652700, 3);
INSERT INTO `9h_sys_city` VALUES (652722, '精河县', 652700, 3);
INSERT INTO `9h_sys_city` VALUES (652723, '温泉县', 652700, 3);
INSERT INTO `9h_sys_city` VALUES (652800, '巴音郭楞蒙古自治州', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (652801, '库尔勒市', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652822, '轮台县', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652823, '尉犁县', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652824, '若羌县', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652825, '且末县', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652826, '焉耆回族自治县', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652827, '和静县', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652828, '和硕县', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652829, '博湖县', 652800, 3);
INSERT INTO `9h_sys_city` VALUES (652900, '阿克苏地区', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (652901, '阿克苏市', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (652922, '温宿县', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (652923, '库车县', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (652924, '沙雅县', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (652925, '新和县', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (652926, '拜城县', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (652927, '乌什县', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (652928, '阿瓦提县', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (652929, '柯坪县', 652900, 3);
INSERT INTO `9h_sys_city` VALUES (653000, '克孜勒苏柯尔克孜自治州', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (653001, '阿图什市', 653000, 3);
INSERT INTO `9h_sys_city` VALUES (653022, '阿克陶县', 653000, 3);
INSERT INTO `9h_sys_city` VALUES (653023, '阿合奇县', 653000, 3);
INSERT INTO `9h_sys_city` VALUES (653024, '乌恰县', 653000, 3);
INSERT INTO `9h_sys_city` VALUES (653100, '喀什地区', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (653101, '喀什市', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653121, '疏附县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653122, '疏勒县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653123, '英吉沙县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653124, '泽普县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653125, '莎车县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653126, '叶城县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653127, '麦盖提县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653128, '岳普湖县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653129, '伽师县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653130, '巴楚县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653131, '塔什库尔干塔吉克自治县', 653100, 3);
INSERT INTO `9h_sys_city` VALUES (653200, '和田地区', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (653201, '和田市', 653200, 3);
INSERT INTO `9h_sys_city` VALUES (653221, '和田县', 653200, 3);
INSERT INTO `9h_sys_city` VALUES (653222, '墨玉县', 653200, 3);
INSERT INTO `9h_sys_city` VALUES (653223, '皮山县', 653200, 3);
INSERT INTO `9h_sys_city` VALUES (653224, '洛浦县', 653200, 3);
INSERT INTO `9h_sys_city` VALUES (653225, '策勒县', 653200, 3);
INSERT INTO `9h_sys_city` VALUES (653226, '于田县', 653200, 3);
INSERT INTO `9h_sys_city` VALUES (653227, '民丰县', 653200, 3);
INSERT INTO `9h_sys_city` VALUES (654000, '伊犁哈萨克自治州', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (654002, '伊宁市', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654003, '奎屯市', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654021, '伊宁县', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654022, '察布查尔锡伯自治县', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654023, '霍城县', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654024, '巩留县', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654025, '新源县', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654026, '昭苏县', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654027, '特克斯县', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654028, '尼勒克县', 654000, 3);
INSERT INTO `9h_sys_city` VALUES (654200, '塔城地区', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (654201, '塔城市', 654200, 3);
INSERT INTO `9h_sys_city` VALUES (654202, '乌苏市', 654200, 3);
INSERT INTO `9h_sys_city` VALUES (654221, '额敏县', 654200, 3);
INSERT INTO `9h_sys_city` VALUES (654223, '沙湾县', 654200, 3);
INSERT INTO `9h_sys_city` VALUES (654224, '托里县', 654200, 3);
INSERT INTO `9h_sys_city` VALUES (654225, '裕民县', 654200, 3);
INSERT INTO `9h_sys_city` VALUES (654226, '和布克赛尔蒙古自治县', 654200, 3);
INSERT INTO `9h_sys_city` VALUES (654300, '阿勒泰地区', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (654301, '阿勒泰市', 654300, 3);
INSERT INTO `9h_sys_city` VALUES (654321, '布尔津县', 654300, 3);
INSERT INTO `9h_sys_city` VALUES (654322, '富蕴县', 654300, 3);
INSERT INTO `9h_sys_city` VALUES (654323, '福海县', 654300, 3);
INSERT INTO `9h_sys_city` VALUES (654324, '哈巴河县', 654300, 3);
INSERT INTO `9h_sys_city` VALUES (654325, '青河县', 654300, 3);
INSERT INTO `9h_sys_city` VALUES (654326, '吉木乃县', 654300, 3);
INSERT INTO `9h_sys_city` VALUES (659000, '自治区直辖县级行政区划', 650000, 2);
INSERT INTO `9h_sys_city` VALUES (659001, '石河子市', 659000, 3);
INSERT INTO `9h_sys_city` VALUES (659002, '阿拉尔市', 659000, 3);
INSERT INTO `9h_sys_city` VALUES (659003, '图木舒克市', 659000, 3);
INSERT INTO `9h_sys_city` VALUES (659004, '五家渠市', 659000, 3);
INSERT INTO `9h_sys_city` VALUES (710000, '台湾省', 0, 1);
INSERT INTO `9h_sys_city` VALUES (810000, '香港特别行政区', 0, 1);
INSERT INTO `9h_sys_city` VALUES (820000, '澳门特别行政区', 0, 1);

-- ----------------------------
-- Table structure for 9h_sysset
-- ----------------------------
DROP TABLE IF EXISTS `9h_sysset`;
CREATE TABLE `9h_sysset`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '唯一key的值',
  `val` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '值',
  `describe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用处描述',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `key`(`key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统的一些配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of 9h_sysset
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
