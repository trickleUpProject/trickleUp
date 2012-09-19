SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `trickleup` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `trickleup` ;

-- -----------------------------------------------------
-- Table `trickleup`.`partner`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `trickleup`.`partner` ;

CREATE  TABLE IF NOT EXISTS `trickleup`.`partner` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(127) NULL ,
  `region` VARCHAR(127) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trickleup`.`coordinator`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `trickleup`.`coordinator` ;

CREATE  TABLE IF NOT EXISTS `trickleup`.`coordinator` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `partner_id` INT NULL ,
  `first_name` VARCHAR(127) NULL ,
  `last_name` VARCHAR(127) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `coordinator_partner_id_idx` (`partner_id` ASC) ,
  CONSTRAINT `fk_coordinator_partner_id`
    FOREIGN KEY (`partner_id` )
    REFERENCES `trickleup`.`partner` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trickleup`.`field_worker`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `trickleup`.`field_worker` ;

CREATE  TABLE IF NOT EXISTS `trickleup`.`field_worker` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `coordinator_id` INT NULL ,
  `first_name` VARCHAR(127) NOT NULL ,
  `last_name` VARCHAR(127) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `field_worker_coordinator_id_idx` (`coordinator_id` ASC) ,
  CONSTRAINT `fk_field_worker_coordinator_id`
    FOREIGN KEY (`coordinator_id` )
    REFERENCES `trickleup`.`coordinator` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trickleup`.`health_worker`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `trickleup`.`health_worker` ;

CREATE  TABLE IF NOT EXISTS `trickleup`.`health_worker` (
  `id` INT NOT NULL ,
  `coordinator_id` INT NULL ,
  `first_name` VARCHAR(127) NOT NULL ,
  `last_name` VARCHAR(127) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `health_worker_coordinator_id_idx` USING BTREE (`coordinator_id` ASC) ,
  CONSTRAINT `fk_health_worker_coordinator_id`
    FOREIGN KEY (`coordinator_id` )
    REFERENCES `trickleup`.`coordinator` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trickleup`.`participant`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `trickleup`.`participant` ;

CREATE  TABLE IF NOT EXISTS `trickleup`.`participant` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `field_worker_id` INT NULL ,
  `health_worker_id` INT NULL ,
  `first_name` VARCHAR(127) NOT NULL ,
  `last_name` VARCHAR(127) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `participant_field_worker_id_idx` (`field_worker_id` ASC) ,
  INDEX `participant_health_worker_id_idx` (`health_worker_id` ASC) ,
  CONSTRAINT `fk_participant_field_worker_id`
    FOREIGN KEY (`field_worker_id` )
    REFERENCES `trickleup`.`field_worker` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_participant_health_worker_id`
    FOREIGN KEY (`health_worker_id` )
    REFERENCES `trickleup`.`health_worker` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trickleup`.`business`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `trickleup`.`business` ;

CREATE  TABLE IF NOT EXISTS `trickleup`.`business` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `participant_id` INT NOT NULL ,
  `business_number` INT NOT NULL COMMENT 'NOT FK and so might be confused with id in this table' ,
  `name` VARCHAR(255) NOT NULL ,
  `start_date` DATE NULL ,
  `end_date` DATE NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `business_participant_id_idx` (`participant_id` ASC) ,
  CONSTRAINT `fk_business_participant_id`
    FOREIGN KEY (`participant_id` )
    REFERENCES `trickleup`.`participant` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `trickleup`.`participant_livestock_report`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `trickleup`.`participant_livestock_report` ;

CREATE  TABLE IF NOT EXISTS `trickleup`.`participant_livestock_report` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `participant_id` INT NOT NULL ,
  `business_id` INT NOT NULL ,
  `report_date` DATE NOT NULL COMMENT 'derived from \\\"period\\\" cell' ,
  `report_year` INT NOT NULL COMMENT 'derived from \\\"period\\\" cell' ,
  `report_quarter` INT NOT NULL COMMENT 'derived from \\\"period\\\" cell' ,
  `shed_condition` INT(8) NULL ,
  `maintenance_cleanliness` ENUM('Y', 'N') NULL ,
  `KMn04_application` ENUM('Y', 'N') NULL ,
  `separation_if_pregnant` ENUM('Y', 'N') NULL ,
  `import_date` DATE NOT NULL ,
  `imported_by_user_id` INT NULL COMMENT 'informally FK but to one of several possible tables and may be null which means imported by admin' ,
  `imported_by_user_type` ENUM('PARTNER','COORDINATOR','FIELD_WORKER','HEALTH_WORKER') NULL ,
  `validated` TINYINT(1) NULL DEFAULT FALSE ,
  `validated_date` DATE NULL ,
  `validated_by_user_id` INT NULL ,
  `validated_by_user_type` ENUM('PARTNER','COORDINATOR','FIELD_WORKER','HEALTH_WORKER') NULL ,
  `format_id` VARCHAR(255) NOT NULL COMMENT 'all or part of the title of the excel doc' ,
  `source_file_name` VARCHAR(255) NULL ,
  `unresolved_parse_errors_json` VARCHAR(255) NULL COMMENT 'array of assoc array with each assoc having keys name value parse-error and type with type being compound or complex or other with other being a datatype' ,
  PRIMARY KEY (`id`) ,
  INDEX `plr_participant_id_idx` (`participant_id` ASC) ,
  INDEX `plr_business_id_idx` (`business_id` ASC) ,
  UNIQUE INDEX `plr_pp_biz_date_idx` (`participant_id` ASC, `business_id` ASC, `report_year` ASC, `report_quarter` ASC) ,
  CONSTRAINT `fk_plr_participant_id`
    FOREIGN KEY (`participant_id` )
    REFERENCES `trickleup`.`participant` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_plr_business_id`
    FOREIGN KEY (`business_id` )
    REFERENCES `trickleup`.`business` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'report-tables are white unlike entity tables';


-- -----------------------------------------------------
-- Table `trickleup`.`livestock_status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `trickleup`.`livestock_status` ;

CREATE  TABLE IF NOT EXISTS `trickleup`.`livestock_status` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `participant_livestock_report_id` INT NULL COMMENT 'if referenced report not validated then no field linked to that report is validated yet' ,
  `livestock_number` INT NULL COMMENT 'NOT an FK but rather from report column heading' ,
  `age_months` INT NULL ,
  `weight_kg` INT NULL ,
  `deworm` DATE NULL ,
  `problem_conceiving` ENUM('Y', 'N') NULL ,
  `concentrate_during_pregnancy` ENUM('Y', 'N') NULL ,
  `miscarriage_date` DATE NULL ,
  `miscarriage_reason` VARCHAR(255) NULL ,
  `delivery_date` DATE NULL ,
  `num_kids_born_m` INT(8) NULL ,
  `num_kids_born_f` INT(8) NULL ,
  `death_date` DATE NULL ,
  `death_reason` VARCHAR(255) NULL ,
  `sale_date` DATE NULL ,
  `sale_price` DECIMAL(8,2) NULL ,
  `livestock_type` ENUM('pig','goat','sheep','goat-sheep') NULL ,
  `unresolved_parse_errors_json` VARCHAR(255) NULL COMMENT 'array of assoc array with each assoc having keys name value parse-error and type with type being compound or complex or other with other being a datatype' ,
  PRIMARY KEY (`id`) ,
  INDEX `ls_participant_livestock_report_id_idx` (`participant_livestock_report_id` ASC) ,
  CONSTRAINT `fk_ls_participant_livestock_report_id`
    FOREIGN KEY (`participant_livestock_report_id` )
    REFERENCES `trickleup`.`participant_livestock_report` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
