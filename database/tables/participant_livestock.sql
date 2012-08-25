drop table trickleup.participant_livestock;

CREATE TABLE trickleup.participant_livestock (
  `participant_id` int(11) NOT NULL,
  `livestock_id` int(11) NOT NULL,
  `age_in_months` int(11) NOT NULL,
  `weight_kg` float NOT NULL,
  `deworming_done` varchar(100) NOT NULL,
  `problem_conceiving` varchar(4000) NOT NULL,
  `concentrate_during_pregnancy` varchar(4000) NOT NULL,
  `miscarriage` enum('Y','N') NOT NULL,
  `miscarriage_Reason` varchar(4000) NOT NULL,
  `delivery_date` date NOT NULL,
  `num_kids_m` int(11) NOT NULL,
  `num_kids_f` int(11) NOT NULL,
  `death` date NOT NULL,
  `reason_for_death` varchar(4000) NOT NULL,
  `sold` date NOT NULL,
  `sale_price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;