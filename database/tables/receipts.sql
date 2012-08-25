drop table trickleup.receipts;

create table trickleup.receipts (
 receipts_id          int not null auto_increment,
 business_number      int(11) not null,
 quarter              int,        
 month                int,       
 year                 int,      
 participant_name     varchar(100) not null,
 date                 date,
 crop_fish            varchar(50),
 consumption_prod     int,
 sale_weight          int,
 sale_amount          int,
 primary key (receipts_id)
);

