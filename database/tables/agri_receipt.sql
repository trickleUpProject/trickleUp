drop table if exists trickleup.agri_receipt;

create table trickleup.agri_receipt (
 business_number      int(11) not null,
 participant_name     varchar(100) not null,
 line_number          smallint not null,
 quarter              tinyint null,        
 month                tinyint null,       
 year                 int not null,      
 date                 date null,
 crop_fish            varchar(50) null,
 consumption_prod     int null,
 sale_weight          int null,
 sale_amount          int null,
 unique key (business_number, year,quarter, month, line_number)
);

