drop table if exists trickleup.crop_list;

create table trickleup.crop_list(
  crop varchar(50) NOT NULL
);

use trickleup;

insert into crop_list
(crop)
values
('spinach');

insert into crop_list
(crop)
values
('pisciculture');

insert into crop_list
(crop)
values
('corn flower');

insert into crop_list
(crop)
values
('paddy');

