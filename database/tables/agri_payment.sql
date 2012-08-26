drop table if exists trickleup.agri_payment;

create table trickleup.agri_payment (
 business_number      int(11) not null,
 participant_name     varchar(100) not null,
 line_number          smallint not null ,
 quarter              tinyint null,        
 month                tinyint null,       
 year                 int not null,      
 date                 date null,
 particular           varchar(100) null,
 quantity             int null,
 amount               float null,
 unique key (business_number,year,quarter, month ,line_number)
);
