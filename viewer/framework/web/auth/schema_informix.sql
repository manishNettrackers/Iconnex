/**
 * Database schema required by CDbAuthManager.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 1.0
 */

drop table AuthAssignment;
drop table AuthItemChild;
drop table AuthItem;

create table AuthItem
(
   name                 varchar(64) not null,
   type                 integer not null,
   description          text,
   bizrule              text,
   data                 text,
   primary key (name)
);

create table AuthItemChild
(
   parent               varchar(64) not null,
   child                varchar(64) not null,
   primary key (parent,child)
);

ALTER TABLE AuthItemChild ADD CONSTRAINT
   (FOREIGN KEY (parent) REFERENCES AuthItem
   ON DELETE CASCADE CONSTRAINT aildelpar ); -- on update cascade CONSTRAINT ailupdpar);

ALTER TABLE AuthItemChild ADD CONSTRAINT
   (FOREIGN KEY (child) REFERENCES AuthItem
   ON DELETE CASCADE CONSTRAINT aildelchd ); --on update cascade);

create table AuthAssignment
(
   itemname             varchar(64) not null,
   userid               varchar(64) not null,
   bizrule              text,
   data                 text,
   primary key (itemname,userid)
);


ALTER TABLE AuthAssignment ADD CONSTRAINT
   (FOREIGN KEY (itemname) REFERENCES AuthItem (name)
   ON DELETE CASCADE CONSTRAINT autassdel ); --on update cascade);

