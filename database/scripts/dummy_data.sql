use trickleup;

insert into agriculture_report (
 business_number       ,
 quarter               ,
 month                 ,
 year                  ,
 participant_name                    ,
 shg_name                   ,
 staff                 ,
 crop                  ,
 agr_inf_type          ,
 agr_inf_cost          ,
 std_prod_land         ,
 age                   ,
 input_cost            ,
 total_prod            ,
 quantity_sold         ,
 amount_from_sale      ,
 value_produced
) values (
 340       ,
 3               ,
 NULL                 ,
 2012                  ,
 "arthur arthur"                    ,
 "ay shg"                   ,
 "staff member one"                 ,
 "Spinach"                  ,
 0          ,
 0          ,
 30         ,
 "Complete"                  ,
 NULL            ,
 NULL            ,
 NULL         ,
 NULL      ,
 NULL
);

insert into agriculture_report (
 business_number       ,
 quarter               ,
 month                 ,
 year                  ,
 participant_name                    ,
 shg_name                   ,
 staff                 ,
 crop                  ,
 agr_inf_type          ,
 agr_inf_cost          ,
 std_prod_land         ,
 age                   ,
 input_cost            ,
 total_prod            ,
 quantity_sold         ,
 amount_from_sale      ,
 value_produced
) values (
 340       ,
 3               ,
 NULL                 ,
 2012                  ,
 "arthur arthur"                    ,
 "ay shg"                   ,
 "staff member one"                 ,
 "Paddy"                  ,
 0          ,
 0          ,
 0         ,
 "45"                   ,
 NULL            ,
 NULL            ,
 NULL         ,
 NULL      ,
 NULL
);

insert into payments (
 business_number      ,
 quarter              ,        
 month                ,       
 year                 ,      
 participant_name                   ,
 date                 ,
 particular           ,
 quantity             ,
 amount               
) values
(
 340 ,# business_number      int not null,
 3, #quarter              int,        
 NULL, #month                int,       
 2012, #                 int,      
 "arthur arthur", #participant_name                   varchar(50) not null,
 2012-08-23, #                 date,
 "spinach", #particular           varchar(50),
 50, #             int,
 40 #amount               int
);

insert into receipts (
 business_number      ,#int not null,
 quarter              ,#int,        
 month                ,#int,       
 year                 ,#int,      
 participant_name                   ,#varchar(50) not null,
 date                 ,#date,
 crop_fish            ,#varchar(50),
 consumption_prod     ,#int,
 sale_weight          ,#int,
 sale_amount          #int,
) values (
 340 ,#business_number      int not null,
 3 ,#quarter              int,        
 NULL, #month                int,       
 2012, #year                 int,      
 "arthur arthur", #participant_name                   varchar(50) not null,
 2012-08-24, #date                 date,
 "Spinach", #crop_fish            varchar(50),
 20, #consumption_prod     int,
 30, #sale_weight          int,
 40 #sale_amount          int
);
