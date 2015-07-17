create table if not exists oc_message(
	id integer not null auto_increment primary key,
	customerid integer not null,
	ToUserName varchar(255) not null,
	FromUserName varchar(255) not null,
	CreateTime integer not null,
	MsgType varchar(32) not null,
	Content varchar(255) default null,
	MsgId bigint not null unique,
	PicUrl varchar(1024) default null,
	MediaId varchar(255) default null,
	Format varchar(64) default null,
	ThumbMediaId varchar(64) default null,
	Location_X double default 0.0,
	Location_Y double default 0.0,
	Scale int default 0,
	Label varchar(512) default null,
	Title varchar(255) default null,
	Description varchar(512) default null,
	Url varchar(1024) default null,
	Others text default null
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

create table if not exists oc_message_reply(
	id integer not null auto_increment primary key,
	customerid integer not null,
	userid integer not null,
	content text default null
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

create table if not exists oc_auto_message(
	id integer not null auto_increment primary key,
	pattern varchar(255) not null,
	MsgType varchar(32) not null,
	ItemCount integer default 0,
	Items text default null
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

create table if not exists oc_event(
	id integer not null auto_increment primary key,
	customerid integer not null,
	ToUserName varchar(255) not null,
	FromUserName varchar(255) not null,
	CreateTime integer not null,
	MsgType varchar(32) not null,
	Event varchar(32) not null,
	EventKey varchar(1024) default null,
	Ticket varchar(255) default null,
	Latitude double default 0.0,
	Longitude double default 0.0,
	`Precision` double default 0.0,
	Others text default null
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*配送区域*/
CREATE TABLE if not exists `oc_district` (
  `id` int(11) NOT NULL auto_increment,
  `city` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `address` varchar(512) NOT NULL,
  `map` varchar(1024) NOT NULL,
  `desp` text default null,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists pos_exchange_data (
	`id` int(11) NOT NULL auto_increment,
	`datatype` int(11) not null,
	`dataval` text,
	`uploadtime` timestamp default now(),
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists pos_exchange_store (
	`id` int(11) NOT NULL auto_increment,
	`storeid` int(11) not null,
	`dataid` int(11) not null,
	`gettime` timestamp default now(),
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists qy_menu_group (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(256) not null,
	`disable` int(11) default 0,
	`sort` int(11) default 0,
	`image` varchar(1024),
	`hasname` int(11) default 1,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists qy_menu (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(256) not null,
	`desp` text,
	`disable` int(11) default 0,
	`sort` int(11) default 0,
	`image1` varchar(1024),
	`image2` varchar(1024),
	`image3` varchar(1024),
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists qy_rel_menu_group (
	`menu_id` int(11) NOT NULL,
	`menu_group_id` int(11) not null,
	PRIMARY KEY  (`menu_id`, `menu_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists qy_food (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(256) not null,
	`desp` text,
	`disable` int(11) default 0,
	`sort` int(11) default 0,
	`image1` varchar(1024),
	`image2` varchar(1024),
	`image3` varchar(1024),
	`make_video` varchar(1024),
	`make_url` varchar(1024),
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table qy_food add `make_url` varchar(1024);

CREATE TABLE if not exists qy_rel_food_menu (
	`food_id` int(11) NOT NULL,
	`menu_id` int(11) not null,
	PRIMARY KEY  (`food_id`, `menu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists qy_food_source (
	`food_id` int(11) NOT NULL,
	`product_id` int(11) not null,
	`source_type` int(11) default 0, /*0主料，1辅料*/
	`groupid` int(11) default 0,
	`sort` int(11) default 0,
	PRIMARY KEY  (`food_id`, `product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists qy_food_make (
	`id` int(11) NOT NULL auto_increment,
	`food_id` int(11) NOT NULL,
	`step` int(11),
	`desp` text,
	`image` varchar(1024),
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists qy_food_attr (
	`id` int(11) NOT NULL auto_increment,
	`stype` varchar(32) NOT NULL,
	`name` varchar(256) not null,
	`disable` int(11) default 0,
	`sort` int(11) default 0,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE if not exists qy_rel_food_attr (
	`food_id` int(11) NOT NULL,
	`attr_id` int(11) not null,
	PRIMARY KEY  (`food_id`, `attr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

create table if not exists qy_balance(
	id integer not null auto_increment primary key,
	shop_id integer not null,
	last_balance_date timestamp default now(),
	order_id bigint not null
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

insert into qy_food_attr set stype='制作难度', name='快速制作';
insert into qy_food_attr set stype='制作难度', name='中等复杂';
insert into qy_food_attr set stype='制作难度', name='较难制作';
insert into qy_food_attr set stype='适宜人群', name='老人';
insert into qy_food_attr set stype='适宜人群', name='幼儿';
insert into qy_food_attr set stype='适宜人群', name='青少年';
insert into qy_food_attr set stype='适宜人群', name='孕产妇';

/*优惠劵添加*/
create table if not exists `oc_coupon_customer` (
  `coupon_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `counts` int(11) default 0,   /*劵的数量*/
  `amount` decimal(15,4) default 0.0,  /*劵的金额*/
  PRIMARY KEY  (`coupon_id`, `customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

alter table oc_order add `coupon_total` decimal(15,4) default 0.0;

/*合作伙伴结算*/
CREATE TABLE if not exists `oc_partner` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `address` varchar(512) NOT NULL,
  `phone` varchar(64) NOT NULL,
  `desp` text default null,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table oc_product add `partner_id` int default 0;

CREATE TABLE if not exists `oc_invitecode` (
  `code` varchar(6) NOT NULL,
  `usertype` int(11) NOT NULL,
  `hasused` int(11) NOT NULL default 0,
  `userid` int(11) NOT NULL default 0,
  `userpid` int(11) NOT NULL default 0,
  `gettime` timestamp default now(),
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
mysql> desc oc_coupon;
+---------------+---------------+------+-----+---------------------+----------------+
| Field         | Type          | Null | Key | Default             | Extra          |
+---------------+---------------+------+-----+---------------------+----------------+
| coupon_id     | int(11)       | NO   | PRI | NULL                | auto_increment |
| name          | varchar(128)  | NO   |     | NULL                |  名称              |
| code          | varchar(10)   | NO   |     | NULL                |                |
| type          | char(1)       | NO   |     | NULL                |  类型，百分比P（比值，数量），金额F （面额，数量）            |
| discount      | decimal(15,4) | NO   |     | NULL                |  比值，面额              |
| logged        | tinyint(1)    | NO   |     | NULL                |  是否要登录              |
| shipping      | tinyint(1)    | NO   |     | NULL                |  是否配送              |
| total         | decimal(15,4) | NO   |     | NULL                |  最大生效金额              |
| date_start    | date          | NO   |     | 0000-00-00          |  有效期开始              |
| date_end      | date          | NO   |     | 0000-00-00          |  有效期结束              |
| uses_total    | int(11)       | NO   |     | NULL                |  优惠劵总数量，0不限制              |
| uses_customer | varchar(11)   | NO   |     | NULL                |  每个客户可以领取多少张该优惠劵，0或空白不限制              |
| status        | tinyint(1)    | NO   |     | NULL                |  是否启用              |
| date_added    | datetime      | NO   |     | 0000-00-00 00:00:00 |  添加日期              |
+---------------+---------------+------+-----+---------------------+----------------+
*/

//地址信息微信使用说明
/*oc_address
CREATE TABLE `oc_address` (
  `address_id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL,
  `firstname` varchar(32) NOT NULL, 姓名
  `lastname` varchar(32) NOT NULL,  电话
  `company` varchar(32) NOT NULL,   
  `company_id` varchar(32) NOT NULL,
  `tax_id` varchar(32) NOT NULL,
  `address_1` varchar(128) NOT NULL, 省市区（从微信合并）
  `address_2` varchar(128) NOT NULL, 详细地址
  `city` varchar(128) NOT NULL,     
  `postcode` varchar(10) NOT NULL,  邮编
  `country_id` int(11) NOT NULL default '0', 国家码看情况转换成本系统ID
  `zone_id` int(11) NOT NULL default '0', 
  `district_id` int(11) not null default '0',
  PRIMARY KEY  (`address_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8
*/
alter table oc_customer add `subscribe` integer default 0;
alter table oc_customer add `openid` varchar(255) default null;
alter table oc_customer add `nickname` varchar(255) default null;
alter table oc_customer add `sex` integer default 0;
alter table oc_customer add `city` varchar(64) default null;
alter table oc_customer add `country` varchar(255) default null;
alter table oc_customer add `province` varchar(64) default null;
alter table oc_customer add `language` varchar(64) default null;
alter table oc_customer add `headimgurl` varchar(1024) default null;
alter table oc_customer add `subscribe_time` integer default 0;
alter table oc_customer add customer_type int(11) default 0; //0 opencart, 1 微信, 2 淘宝
alter table oc_customer add `access_token` varchar(1024) default null;
alter table oc_customer add `lastlogin` timestamp;
alter table oc_customer add `unsubscribe` integer default 0;

alter table oc_order modify order_id bigint NOT NULL auto_increment;
alter table oc_order add shipping_time datetime default null;
alter table oc_order add shipping_district_id integer default 0;
alter table oc_order add `shipping_pay` double default 0.0;
alter table oc_order add other_order_id bigint default 0;
alter table oc_order add shipping_telephone varchar(32);
alter table oc_order add transaction_id varchar(256) default "";
alter table oc_order add weixin_pay_result text default NULL;
alter table oc_order add order_type integer default 0;
alter table oc_order add balance integer default 0;
alter table oc_order add refund_amount double default 0.0;
alter table oc_order add refund_id int default 0;
alter table oc_order add iscash int default 0;
alter table oc_order add comment2 text default null;
alter table oc_order add costpay double default 0.0; /*订单零星采购成本*/
alter table oc_order add cashpay double default 0.0; /*到付实收金额*/
alter table oc_order add ismodify int default 0;

alter table oc_order_product add `weight` double default 0.0;
alter table oc_order_product add `realweight` double default 0.0;
alter table oc_order_product add `realtotal` double default 0.0;
alter table oc_order_product add `other_product_id` bigint default 0;
alter table oc_order_product add `perprice` double default 0.0;
alter table oc_order_product add `perweight` double default 0.0;

alter table oc_order_download modify order_id bigint;
alter table oc_order_field modify order_id bigint;
alter table oc_order_fraud modify order_id bigint;
alter table oc_order_history modify order_id bigint;
alter table oc_order_option modify order_id bigint;
alter table oc_order_product modify order_id bigint;
alter table oc_order_total modify order_id bigint;
alter table oc_order_voucher modify order_id bigint;

alter table oc_product add `other_product_id` bigint default 0;
//0:重量可以固定的商品, 1:重量不能固定的商品
alter table oc_product add `product_type` int default 0;
alter table oc_product add `hasedit` int default 0;

alter table oc_address add `district_id` int(11) not null default '0';
alter table oc_address add telephone varchar(32);

alter table oc_user add district_id int(11) default 0;
alter table oc_user add usertype int(11) default 0;

insert into oc_setting (`key`,`value`) values('first_shipping_time', '9');
insert into oc_setting (`key`,`value`) values('last_shipping_time', '19');
insert into oc_setting (`key`,`value`) values('minum_order', '20.00');

alter table oc_order_status add wxtitle varchar(128);
alter table oc_order_status add wxmsg varchar(2048);

//init
insert into oc_setting (`group`, `key`,`value`) values('weixin', 'weixin_token', 'wxc0a931d70de89f4c');
insert into oc_setting (`group`, `key`,`value`) values('weixin', 'weixin_appid', 'wxc0a931d70de89f4c');
insert into oc_setting (`group`, `key`,`value`) values('weixin', 'weixin_appsecret', 'c7153a6b0dba17395e66c7f4d25e35a1');

insert into oc_auto_message set pattern='subscribe', MsgType='news', ItemCount=1, Items='[{"title":"菜鸽子欢迎您","description":"我们的承诺：传承老一代，用心卖好菜。\\n\\n选择菜单中的功能将全面使用本服务号。\\n如有任何疑问，请直接在此发送消息，在线客服将随时为您服务！","url":"AUTO_LOGIN:mobile_store/about","picurl":"HOST:/image/data/weixin/caigezi2-green-360x200.jpg"}]';
insert into oc_auto_message set pattern='order|订|买', MsgType='news', ItemCount=1, Items='[{"title":"点击开始买菜","description":"传承老一代，用心卖好菜。飞鸽传蔬期待为您服务。","url":"AUTO_LOGIN:mobile_store/home","picurl":"HOST:/image/data/weixin/caigezi2-green-360x200.jpg"}]';

update oc_order_status set wxtitle='下单通知', wxmsg = '亲，我们已收到您的订单。请在此发送消息联系在线客服。\n\n订单编号：%s\n订单金额：%s\n下单时间：%s\n消费明细：%s\n' where order_status_id = 1;
update oc_order_status set wxtitle='付款通知', wxmsg = '亲，您的订单已称重。\n点击此消息即可付款，或选择“货到付款”。\n\n订单编号：%s\n订单金额：%s\n下单时间：%s\n消费明细：%s\n\n点击查看订单详情' where order_status_id = 2;
update oc_order_status set wxtitle='配送通知', wxmsg = '亲，您的订单已付款，或改为货到付款，我们正在安排配送！\n\n飞鸽传蔬将携程为您服务\n\n订单编号：%s\n订单金额：%s\n下单时间：%s\n消费明细：%s\n\n点击查看订单详情' where order_status_id = 3;
update oc_order_status set wxtitle='订单完成', wxmsg = '亲，您的订单已经配送完成！\n欢迎下次再来哦！\n\n订单编号：%s\n订单金额：%s\n下单时间：%s\n消费明细：%s\n\n点击查看详情' where order_status_id = 4;
update oc_order_status set wxtitle='订单退款', wxmsg = '亲，您的订单已退款！\n\n订单编号：%s\n订单金额：%s\n下单时间：%s\n消费明细：%s\n\n点击查看详情' where order_status_id = 5;
update oc_order_status set wxtitle='订单取消', wxmsg = '亲，您的订单已取消！\n\n订单编号：%s\n订单金额：%s\n下单时间：%s\n消费明细：%s\n\n点击查看详情' where order_status_id = 6;

/* 维护SQL order */
delete from oc_order where order_id=0;
delete from oc_order_download where order_id=0;
delete from oc_order_field where order_id=0;
delete from oc_order_fraud where order_id=0;
delete from oc_order_history where order_id=0;
delete from oc_order_option where order_id=0;
delete from oc_order_product where order_id=0;
delete from oc_order_total where order_id=0;
delete from oc_order_voucher where order_id=0;

delete from oc_order;
delete from oc_order_download;
delete from oc_order_field;
delete from oc_order_fraud;
delete from oc_order_history;
delete from oc_order_option;
delete from oc_order_product;
delete from oc_order_total;
delete from oc_order_voucher;

