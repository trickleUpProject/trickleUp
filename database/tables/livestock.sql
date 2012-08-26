drop table if exists trickleup.livestock;

create table trickleup.livestock(
  business_number int(11) NOT NULL,
  participant_name varchar(100) not null,
  quarter      INT NULL,
  month        INT NULL,
  year         INT NOT NULL,
  livestock_number smallint NOT NULL,
  livestock_type enum('goat/sheep','pig') NOT NULL,
  gender         enum ('M','F') not null,
  purchase_price float null,
  acquisition_date date not null,
  age_at_purchase varchar(25) null,
  pox_vaccine_yr1 date  null,
  pox_vaccine_yr2 date null,
  ppr_vaccine_yr1 date null,
  ppr_vaccine_yr2 date null,
  other_vaccine_yr1 date null,
  other_vaccine_yr2 date  null,
  swine_flu_vaccine_yr1 date null,
  swine_flu_vaccine_yr2 date null,
  castration enum('Y','N','N/A') null,
  unique key (business_number, year, quarter, month, livestock_number)

)ENGINE=InnoDB DEFAULT CHARSET=utf8;
