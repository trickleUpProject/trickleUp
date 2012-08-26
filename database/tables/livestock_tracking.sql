drop table if exists trickleup.livestock_tracking;

CREATE TABLE trickleup.livestock_tracking (
  business_number int(11) NOT NULL,
  participant_name varchar(100) not null,
  quarter      INT NULL,
  month        INT NULL,
  year         INT NOT NULL,
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
  unique key (business_number, year, quarter, month, livestock_number)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
