drop table if exists trickleup.livestock_tracking;

CREATE TABLE trickleup.livestock_tracking (
  business_number int(11) NOT NULL,
  participant_name varchar(100) not null,
  staff                varchar(100) null,
  month        INT NOT NULL,
  year         INT NOT NULL,
  quarter      INT NULL,
  livestock_number smallint NOT NULL,
  livestock_type enum('goat/sheep','pig') NOT NULL,
  age_in_months int(11) NULL,
  weight_kg float NULL,
  deworming_done varchar(100) NULL,
  problem_conceiving varchar(4000) NULL,
  concentrate_during_pregnancy varchar(4000) NULL,
  separate_during_pregnancy enum('Y','N','N/A') NULL,
  miscarriage enum('Y','N')  NULL,
  miscarriage_reason varchar(4000)  NULL,
  delivery_date date  NULL,
  num_kids_m int(11)  NULL,
  num_kids_f int(11) NULL,
  death date  NULL,
  reason_for_death varchar(4000) NULL,
  sold date  NULL,
  sale_price float NULL,
  shed_condition int NULL,
  maintenance_cleanliness enum ('Y','N') NULL,
  KMnO4_application enum ('Y','N') NULL,
  primary key (business_number, year, month, livestock_number, livestock_type)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
