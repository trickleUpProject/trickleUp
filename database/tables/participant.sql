drop table if exists trickleup.participant;

CREATE TABLE trickleup.participant (
  business_number int(11) NOT NULL,
  participant_name varchar(100) NOT NULL,
  PRIMARY KEY (business_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='stores basic information for participants';

