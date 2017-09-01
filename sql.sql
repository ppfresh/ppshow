-- 创建数据库
create database jql_shop charset=utf8;

-- 使用数据库
use jql_shop;

-- 品牌表
drop table if exists jql_brand;
create table jql_brand(
id int unsigned  auto_increment primary key comment '主键id',
title varchar(32) not null default '' comment '名称',
logo varchar(255) not null default '' comment 'LOGO',
site varchar(255) not null default '' comment '官网',
  -- 通常都会增加这三个字段
sort int not null default 0 comment '排序',
create_time int not null default 0 comment '创建时间',
update_time int not null default 0 comment '更新时间',
key (title),
key (sort),
key (update_time)
)engine innodb charset=utf8 comment '品牌';


-- 商品分类表
drop table if exists jql_category;
create table jql_category (
  id int unsigned auto_increment primary key comment 'ID',
  title varchar(32) not null default '' comment '分类',
  parent_id int unsigned not null default 0 comment '上级分类',
  sort int not null default 0 COMMENT '排序',
  is_used boolean not null default 0 comment '启用', -- tinyint(1)

  -- SEO优化
  meta_title varchar(255) not null default '' comment 'SEO标题',
  meta_keywords varchar(255) not null default '' comment 'SEO关键字',
  meta_description varchar(1024) not null default '' comment 'SEO描述',
  
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  index (title),
  index (parent_id),
  index (sort)
) engine innodb charset utf8 comment '分类';
-- 初始化了一条数据
insert into jql_category values (1, '未分类', 0, -1, 0, '', '', '', unix_timestamp(), unix_timestamp());







