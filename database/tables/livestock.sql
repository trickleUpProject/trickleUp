drop table trickleup.livestock;

create table trickleup.livestock(
  participant_id int(11) not null,
  survey_period varchar(25) not null,
  livestock_num int not null,
  purchase_price float null,
  acquisition_date date not null,
  age_at_acquisition int not null,
  pox_vaccine_yr1 date  null,
  pox_vaccine_yr2 date null,
  ppr_vaccine_yr1 date null,
  ppr_vaccine_yr2 date null,
  other_vaccine_yr1 date null,
  other_vaccine_yr2 date  null,
  swine_flu_vaccine_yr1 date null,
  swine_flu_vaccine_yr2 date null,
  castration enum('Y','N','N/A') null

)ENGINE=InnoDB DEFAULT CHARSET=utf8;