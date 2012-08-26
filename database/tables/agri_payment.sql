drop table if exists trickleup.agri_payment;

create table trickleup.agri_payment (
 business_number      int(11) not null,
 participant_name     varchar(100) not null,
 staff                varchar(100) null,
 line_number          smallint not null ,
 month                tinyint not null,       
 year                 int not null,      
 quarter              tinyint null,        
 date                 date null,
 particular           varchar(100) null,
 quantity             int null,
 amount               float null,
 primary key (business_number,year,month ,line_number)
);
