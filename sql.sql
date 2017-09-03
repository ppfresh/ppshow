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

-- 管理员表
drop  table if exists jql_admin;
create table jql_admin(
  id int not null primary key auto_increment comment'ID',

  user varchar(32) not null default '' comment '管理员',
  password varchar(64) not null default '' comment '密码',
  salt varchar(12) not null default '' comment '盐',

  sort int not null default 0 comment '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  unique key(user),
  key(password(10))
)engine innodb charset utf8 comment '管理员';
-- 插入一条数据
insert into jql_admin VALUES (NULL,'admin',md5(concat('admin',salt)),'123456789',0,'','');



-- 动作表(对应权限表)
drop table if exists jql_action;
create table jql_action(
  id int unsigned  not null primary key auto_increment comment 'ID',
  route varchar(255) not null default '' comment '路由',
  title varchar(32) not null default '' comment '动作名称',
  description varchar(255) not null default '' comment '描述',

  sort int not null default 0 comment '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '更新时间',
  key(route),
  key(title),
  key(sort)
)engine innodb charset utf8 comment '动作表';

-- 角色表(权限分组表)
drop table if exists jql_role;
create table jql_role(
  id int unsigned not null primary key auto_increment comment 'ID',
  title varchar(32) not null default '' comment '角色',
  descripttion varchar(255) not null default '' comment '角色描述',

  sort int not null default 0 comment '排序',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '更新时间',
  key(title),
  key(sort)
)engine innodb charset utf8 comment '角色表';

-- 管理员-角色关联表
drop table if exists jql_role_admin;
create table jql_role_admin(
  id int unsigned not null primary key auto_increment comment 'ID',
  admin_id int unsigned not null default 0 comment '管理员ID',
  role_id int unsigned not null default 0 comment '角色ID',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',


  unique index(role_id,admin_id),
  key(admin_id)
)engine innodb charset utf8 comment '管理员--角色表';

-- 动作(权限)-角色关联表
drop table if exists jql_role_action;
create table jql_role_action(
  id int unsigned not null primary key auto_increment comment 'ID',
  action_id int unsigned not  null  default  0 comment  '动作ID',
  role_id int UNSIGNED not  null default 0 comment '角色ID',
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  unique index(role_id,action_id),
  key(action_id)
)engine innodb charset utf8 comment '动作--角色表';








