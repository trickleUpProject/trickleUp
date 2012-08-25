drop database trickleup;
create database trickleup DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;


create user 'trickleup_admin' identified by 'emp0w3r@LL';
grant usage on *.* to trickleup_admin identified by 'emp0w3r@LL';
grant all privileges on trickleup.* to trickleup_admin ;