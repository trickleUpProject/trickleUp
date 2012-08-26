our %participant_name = (
 200 => 'droid droid',
 205 => 'mac mac',
 208 => 'participant one',
 308 => 'participant two',
 210 => 'linux linux');
our @business_numbers = keys %participant_name;
our @recent_dates = ('2012-08-20', '2012-08-21', '2012-08-22',
 '2012-01-20', '2012-02-21', '2012-03-22',
 '2012-04-20', '2012-06-21', '2012-04-22',
 '2012-01-17', '2012-02-04', '2012-03-05',
 '2012-03-20', '2012-05-21', '2012-07-22');
our %generate_table_sub;
our @crops = ('spinach', 'corn flower', 'pisciculture', 'paddy');
our @livestock_type = ('goat/sheep','pig');
our @gender = ('M','F');
our @staff = ('Staff one', 'Staff two', 'Staff three', 'Staff four');
our @yes_no_na = ('Y','N','N/A');
our @yes_no = ('Y','N');
our @business = ('Bus1', 'Bus2', 'Bus3');
our %line_number;

sub random_integer {
 my $min = shift;
 my $max = shift;
 my $range = $max - $min;
 return int(rand($range)) + $min;
}

sub random_from_array {
  my $nullable = shift;
  my @array = @_;
  if ($nullable) {
    my $check = random_integer(1,100);
    if ($nullable < $check) {return 'NULL'}
  }
  $array[int rand(@array)];
}

sub random_string_from_array {
  my $nullable = shift;
  my @array = @_;
  my $value = $array[int rand(@array)];
  if ($nullable) {
    if (int(rand(2))) { return 'NULL'};
  }
  return '"'.$value.'"';
}

sub dummy_agri_payment {
  my %values;
  #my $business_number = random_from_array(keys %participant_names);
  my $business_number = random_from_array(0,@business_numbers);
  my $participant = $participant_name{$business_number};
  my $staff = random_string_from_array(0,@staff);
  my $recent_date = random_from_array(0,@recent_dates);
  my $year = substr($recent_date,0,4);
  my $month = substr($recent_date,5,2);
  if ($month < 4) {$quarter = 1;}
  elsif ($month < 7) {$quarter = 2;}
  elsif ($month < 10) {$quarter = 3;}
  elsif ($month < 10) {$quarter = 4;}
  $values{business_number} = $business_number;
  $values{participant_name} = '"'.$participant.'"';
  $values{quarter} = $quarter;
  $values{month} = $month;
  $values{year} = $year;
  $values{line_number} = ++$line_number{agri_payment}{$business_number}{$participant}{$quarter}{$year};
  $values{date} = random_string_from_array(0,@recent_dates);
  $values{particular} = random_integer(0,10);
  $values{quantity} = random_integer(1,20);
  $values{amount} = random_integer(5,10);
  return \%values;
}
$generate_table_sub{agri_payment} = \&dummy_agri_payment;

sub dummy_agri_receipt {
  my %values;
  #my $business_number = random_from_array(keys %participant_names);
  my $business_number = random_from_array(0,@business_numbers);
  my $participant = $participant_name{$business_number};
  my $staff = random_string_from_array(0,@staff);
  my $recent_date = random_from_array(0,@recent_dates);
  my $year = substr($recent_date,0,4);
  my $month = substr($recent_date,5,2);
  if ($month < 4) {$quarter = 1;}
  elsif ($month < 7) {$quarter = 2;}
  elsif ($month < 10) {$quarter = 3;}
  elsif ($month < 10) {$quarter = 4;}
  $values{business_number} = $business_number;
  $values{participant_name} = '"'.$participant.'"';
  $values{quarter} = $quarter;
  $values{month} = $month;
  $values{year} = $year;
  $values{line_number} = ++$line_number{agri_receipt}{$business_number}{$participant}{$quarter}{$year};
  $values{date} = random_string_from_array(0,@recent_dates);
  $values{crop_fish} = '"'.random_from_array(0,@crops).'"';
  $values{consumption_prod} = random_integer(1,20);
  $values{sale_weight} = random_integer(5,10);
  $values{sale_amount} = random_integer(5,10);
  return \%values;
}
$generate_table_sub{agri_receipt} = \&dummy_agri_receipt;

sub dummy_livestock {
  my %values;
  #my $business_number = random_from_array(keys %participant_names);
  my $business_number = random_from_array(0,@business_numbers);
  my $participant = $participant_name{$business_number};
  my $staff = random_string_from_array(0,@staff);
  my $recent_date = random_from_array(0,@recent_dates);
  my $year = substr($recent_date,0,4);
  my $month = substr($recent_date,5,2);
  if ($month < 4) {$quarter = 1;}
  elsif ($month < 7) {$quarter = 2;}
  elsif ($month < 10) {$quarter = 3;}
  elsif ($month < 10) {$quarter = 4;}
  $values{business_number} = $business_number;
  $values{participant_name} = '"'.$participant.'"';
  $values{quarter} = $quarter;
  $values{month} = $month;
  $values{year} = $year;
  my $livestock_type = random_from_array(0,@livestock_type);
  $values{livestock_type} = '"'.$livestock_type.'"';
  $values{livestock_number} = ++$line_number{livestock}{$business_number}{$participant}{$quarter}{$year}{$livestock_type};
  $values{gender} = '"'.random_from_array(0,@gender).'"';
  $values{acquisition_date} = random_string_from_array(0,@recent_dates);
  $values{age_at_purchase} = random_integer(1,20);
  $values{pox_vaccine_yr1} = 'NULL',
  $values{pox_vaccine_yr2} = 'NULL',
  $values{ppr_vaccine_yr1} = 'NULL',
  $values{ppr_vaccine_yr2} = 'NULL',
  $values{other_vaccine_yr1} = 'NULL',
  $values{other_vaccine_yr2} = 'NULL',
  $values{swine_flu_vaccine_yr1} = 'NULL',
  $values{swine_flu_vaccine_yr2} = 'NULL',
  $values{castration} = '"'.random_from_array(0,@yes_no_na).'"';
  return \%values;
}
$generate_table_sub{livestock} = \&dummy_livestock;

sub dummy_livestock_tracking {
  my %values;
  #my $business_number = random_from_array(keys %participant_names);
  my $business_number = random_from_array(0,@business_numbers);
  my $participant = $participant_name{$business_number};
  my $staff = random_string_from_array(0,@staff);
  my $recent_date = random_from_array(0,@recent_dates);
  my $year = substr($recent_date,0,4);
  my $month = substr($recent_date,5,2);
  if ($month < 4) {$quarter = 1;}
  elsif ($month < 7) {$quarter = 2;}
  elsif ($month < 10) {$quarter = 3;}
  elsif ($month < 10) {$quarter = 4;}
  $values{business_number} = $business_number;
  $values{participant_name} = '"'.$participant.'"';
  $values{quarter} = $quarter;
  $values{month} = $month;
  $values{year} = $year;
  my $livestock_type = random_from_array(0,@livestock_type);
  $values{livestock_type} = '"'.$livestock_type.'"';
  $values{livestock_number} = ++$line_number{livestock_tracking}{$business_number}{$participant}{$quarter}{$year}{$livestock_type};
  $values{age_in_months} = random_integer(3,10);
  $values{weight_kg} = random_integer(1,30);
  $values{deworming_done} = '"'.random_from_array(0,@yes_no).'"';
  $values{problem_conceiving} = '"'.random_from_array(0,@yes_no).'"';
  $values{concentrate_during_pregnancy} = 'NULL';
  $values{separate_during_pregnancy} = '"'.random_from_array(0,@yes_no_na).'"';
  $values{miscarriage} = $miscarriage = '"'.random_from_array(0,@yes_no).'"';
  if ($miscarriage eq 'Y') {$values{miscarriage} = 'bad'};
  $values{delivery_date} = random_string_from_array(0,@recent_dates);
  $values{num_kids_m} = random_integer(0,4);
  $values{num_kids_f} = random_integer(1,4);
  $values{death} = random_string_from_array(40,@recent_dates);
  if ($values{death} ne 'NULL') {
    $values{reason_for_death} = random_string_from_array(0,"old","sick","accident");
  }
  $values{sold} = random_string_from_array(30,@recent_dates);
  if ($values{sold} ne 'NULL') {
    $values{sale_price} = random_integer(1,100);
  }
  $values{shed_condition} = random_integer(0,10);
  $values{maintenance_cleanliness} = random_string_from_array(50,@yes_no);
  return \%values;
}
$generate_table_sub{livestock_tracking} = \&dummy_livestock_tracking;

sub dummy_small_business_payment {
  my %values;
  #my $business_number = random_from_array(keys %participant_names);
  my $business_number = random_from_array(0,@business_numbers);
  my $participant = $participant_name{$business_number};
  my $staff = random_string_from_array(0,@staff);
  my $recent_date = random_from_array(0,@recent_dates);
  my $year = substr($recent_date,0,4);
  my $month = substr($recent_date,5,2);
  if ($month < 4) {$quarter = 1;}
  elsif ($month < 7) {$quarter = 2;}
  elsif ($month < 10) {$quarter = 3;}
  elsif ($month < 10) {$quarter = 4;}
  $values{business_number} = $business_number;
  $values{participant_name} = '"'.$participant.'"';
  $values{quarter} = $quarter;
  $values{month} = $month;
  $values{year} = $year;
  $values{line_number} = ++$line_number{small_business_payment}{$business_number}{$participant}{$quarter}{$year};
  $values{business_name} = '"'.random_from_array(0,@business).'"';
  $values{start_date} = $recent_date;
  $values{amount} = random_integer(1,20);
  return \%values;
}
$generate_table_sub{small_business_payment} = \&dummy_small_business_payment;

sub dummy_small_business_receipt {
  my %values;
  #my $business_number = random_from_array(keys %participant_names);
  my $business_number = random_from_array(0,@business_numbers);
  my $participant = $participant_name{$business_number};
  my $staff = random_string_from_array(0,@staff);
  my $recent_date = random_from_array(0,@recent_dates);
  my $year = substr($recent_date,0,4);
  my $month = substr($recent_date,5,2);
  if ($month < 4) {$quarter = 1;}
  elsif ($month < 7) {$quarter = 2;}
  elsif ($month < 10) {$quarter = 3;}
  elsif ($month < 10) {$quarter = 4;}
  $values{business_number} = $business_number;
  $values{participant_name} = '"'.$participant.'"';
  $values{quarter} = $quarter;
  $values{month} = $month;
  $values{year} = $year;
  $values{line_number} = ++$line_number{small_business_receipt}{$business_number}{$participant}{$quarter}{$year};
  $values{business_name} = '"'.random_from_array(0,@business).'"';
  $values{start_date} = $recent_date;
  $values{sale_date} = $recent_date;
  $values{sale_amount} = random_integer(1,20);
  $values{consumption_amount} = random_integer(1,20);
  $values{total_sale} = random_integer(1,20);
  return \%values;
}
$generate_table_sub{small_business_receipt} = \&dummy_small_business_receipt;

sub random_row {
 my $table = shift;
 my $string;
 #my $to_insert = hash_dummy_payments;
 my $sub_to_run = $generate_table_sub{$table};
 my $to_insert = &$sub_to_run;
 my @columns = keys %$to_insert;
 my @values;
 foreach my $column (@columns) {
   push @values, $$to_insert{$column}; 
 };
 $string = "insert into $table (".join (",\n", @columns).") values \n (";
 $string .= join (",\n", @values).");\n\n";
 return $string;
}

sub random_table_rows {
  my $table = shift;
  my $number = shift;
  for ($i = 1; $i <= $number; $i++) {
    print random_row($table);
  }
}

print "use trickleup;\n\n";

random_table_rows('agri_payment',1000);
random_table_rows('agri_receipt',1000);
random_table_rows('livestock',2000);
random_table_rows('livestock_tracking',2000);
random_table_rows('small_business_payment',1000);
random_table_rows('small_business_receipt',1000);
