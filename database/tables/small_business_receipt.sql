drop table if exists trickleup.small_business_receipt;

create table trickleup.small_business_receipt (
    business_number int(11) NOT NULL,
    participant_name      varchar(100) not null,
    quarter               INT NULL,
    month                 INT NULL,
    year                  INT NOT NULL,
    line_number           smallint not null,
    business_name         varchar(200),
    start_date            date null ,
    sale_date             date null,
    sale_amount           float null,
    consumption_amount    float null,
    total_sale            float null,
    staff_signature       varchar(100),
    primary key (business_number, year, quarter, month, line_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;