drop table trickleup.payments;

create table trickleup.payments (
 payments_id          int not null auto_increment,
 business_number      int(11) not null,
 quarter              int,        
 month                int,       
 year                 int,      
 participant_name     varchar(100) not null,
 date                 date,
 particular           varchar(50),
 quantity             int,
 amount               int,
 primary key (payments_id)
);
