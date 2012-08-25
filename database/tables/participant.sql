drop table if exists trickleup.participant;

CREATE TABLE trickleup.participant (
  business_number int(11) NOT NULL,
  participant_name varchar(100) NOT NULL,
  staff_name varchar(100) NOT NULL,
  shg_name varchar(100) NOT NULL,
  PRIMARY KEY (business_number),
  UNIQUE KEY business_number (business_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='stores basic information for participants';

ALTER TABLE trickleup.participant ADD INDEX ( business_number ) 