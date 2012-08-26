drop table if exists trickleup.agriculture_report;

create table trickleup.agriculture_report (
 agriculture_report_id int not null auto_increment,
 business_number       int(11) not null,        
 month                 int, 
 year                  int,        
 participant_name      varchar(100) not null,        
 quarter               int,        
 shg_name              varchar(100),
 staff                 varchar(50),
 crop                  varchar(50), 
 agr_inf_type          varchar(50),
 agr_inf_cost          int,
 std_prod_land         int,
 age                   varchar(50),
 input_cost            int,
 total_prod            int,
 quantity_sold         int,
 amount_from_sale      int,
 value_produced        int,
 primary key (agriculture_report_id)
);
