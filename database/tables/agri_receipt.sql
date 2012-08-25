drop table if exists trickleup.agri_receipt;

create table trickleup.agri_receipt (
 business_number      int(11) not null,
 participant_name     varchar(100) not null,
 line_number          smallint not null,
 quarter              tinyint,        
 month                tinyint,       
 year                 int not null,      
 date                 date,
 crop_fish            varchar(50),
 consumption_prod     int,
 sale_weight          int,
 sale_amount          int,
 primary key (business_number, year,quarter, month, line_number)
);

