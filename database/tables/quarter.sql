drop table if exists trickleup.quarter;

create table trickleup.quarter(
  quarter tinyint NOT NULL,
  month int NOT NULL
);

use trickleup;

insert into quarter
(quarter, month)
values
(1,1);

insert into quarter
(quarter, month)
values
(1,2);

insert into quarter
(quarter, month)
values
(1,3);

insert into quarter
(quarter, month)
values
(2,4);

insert into quarter
(quarter, month)
values
(2,5);

insert into quarter
(quarter, month)
values
(2,6);

insert into quarter
(quarter, month)
values
(3,7);

insert into quarter
(quarter, month)
values
(3,8);

insert into quarter
(quarter, month)
values
(3,9);

insert into quarter
(quarter, month)
values
(4,10);

insert into quarter
(quarter, month)
values
(4,11);

insert into quarter
(quarter, month)
values
(4,12);
