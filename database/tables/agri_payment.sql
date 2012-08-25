drop table if exists trickleup.agri_payment;

create table trickleup.agri_payment (
 business_number      int(11) not null,
 participant_name     varchar(100) not null,
 line_number          smallint not null ,
 quarter              tinyint,        
 month                tinyint,       
 year                 int,      
 date                 date,
 particular           varchar(100),
 quantity             int,
 amount               float,
 primary key (business_number,year,quarter, month ,line_number)
);
