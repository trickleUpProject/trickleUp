<?php

/**
 * This is the model class for table "livestock_tracking".
 *
 * The followings are the available columns in table 'livestock_tracking':
 * @property integer $business_number
 * @property string $participant_name
 * @property integer $quarter
 * @property integer $month
 * @property integer $year
 * @property integer $livestock_number
 * @property string $livestock_type
 * @property integer $age_in_months
 * @property double $weight_kg
 * @property string $deworming_done
 * @property string $problem_conceiving
 * @property string $concentrate_during_pregnancy
 * @property string $separate_during_pregnancy
 * @property string $miscarriage
 * @property string $miscarriage_reason
 * @property string $delivery_date
 * @property integer $num_kids_m
 * @property integer $num_kids_f
 * @property string $death
 * @property string $reason_for_death
 * @property string $sold
 * @property double $sale_price
 * @property integer $shed_condition
 * @property string $maintenance_cleanliness
 * @property string $KMnO4_application
 */
class LivestockTracking extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return LivestockTracking the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'livestock_tracking';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('business_number, participant_name, year, livestock_number, livestock_type', 'required'),
			array('business_number, quarter, month, year, livestock_number, age_in_months, num_kids_m, num_kids_f, shed_condition', 'numerical', 'integerOnly'=>true),
			array('weight_kg, sale_price', 'numerical'),
			array('participant_name, deworming_done', 'length', 'max'=>100),
			array('livestock_type', 'length', 'max'=>10),
			array('problem_conceiving, concentrate_during_pregnancy, miscarriage_reason, reason_for_death', 'length', 'max'=>4000),
			array('separate_during_pregnancy', 'length', 'max'=>3),
			array('miscarriage, maintenance_cleanliness, KMnO4_application', 'length', 'max'=>1),
			array('delivery_date, death, sold', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('business_number, participant_name, quarter, month, year, livestock_number, livestock_type, age_in_months, weight_kg, deworming_done, problem_conceiving, concentrate_during_pregnancy, separate_during_pregnancy, miscarriage, miscarriage_reason, delivery_date, num_kids_m, num_kids_f, death, reason_for_death, sold, sale_price, shed_condition, maintenance_cleanliness, KMnO4_application', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'business_number' => 'Business Number',
			'participant_name' => 'Participant Name',
			'quarter' => 'Quarter',
			'month' => 'Month',
			'year' => 'Year',
			'livestock_number' => 'Livestock Number',
			'livestock_type' => 'Livestock Type',
			'age_in_months' => 'Age In Months',
			'weight_kg' => 'Weight Kg',
			'deworming_done' => 'Deworming Done',
			'problem_conceiving' => 'Problem Conceiving',
			'concentrate_during_pregnancy' => 'Concentrate During Pregnancy',
			'separate_during_pregnancy' => 'Separate During Pregnancy',
			'miscarriage' => 'Miscarriage',
			'miscarriage_reason' => 'Miscarriage Reason',
			'delivery_date' => 'Delivery Date',
			'num_kids_m' => 'Num Kids M',
			'num_kids_f' => 'Num Kids F',
			'death' => 'Death',
			'reason_for_death' => 'Reason For Death',
			'sold' => 'Sold',
			'sale_price' => 'Sale Price',
			'shed_condition' => 'Shed Condition',
			'maintenance_cleanliness' => 'Maintenance Cleanliness',
			'KMnO4_application' => 'Kmn O4 Application',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('business_number',$this->business_number);
		$criteria->compare('participant_name',$this->participant_name,true);
		$criteria->compare('quarter',$this->quarter);
		$criteria->compare('month',$this->month);
		$criteria->compare('year',$this->year);
		$criteria->compare('livestock_number',$this->livestock_number);
		$criteria->compare('livestock_type',$this->livestock_type,true);
		$criteria->compare('age_in_months',$this->age_in_months);
		$criteria->compare('weight_kg',$this->weight_kg);
		$criteria->compare('deworming_done',$this->deworming_done,true);
		$criteria->compare('problem_conceiving',$this->problem_conceiving,true);
		$criteria->compare('concentrate_during_pregnancy',$this->concentrate_during_pregnancy,true);
		$criteria->compare('separate_during_pregnancy',$this->separate_during_pregnancy,true);
		$criteria->compare('miscarriage',$this->miscarriage,true);
		$criteria->compare('miscarriage_reason',$this->miscarriage_reason,true);
		$criteria->compare('delivery_date',$this->delivery_date,true);
		$criteria->compare('num_kids_m',$this->num_kids_m);
		$criteria->compare('num_kids_f',$this->num_kids_f);
		$criteria->compare('death',$this->death,true);
		$criteria->compare('reason_for_death',$this->reason_for_death,true);
		$criteria->compare('sold',$this->sold,true);
		$criteria->compare('sale_price',$this->sale_price);
		$criteria->compare('shed_condition',$this->shed_condition);
		$criteria->compare('maintenance_cleanliness',$this->maintenance_cleanliness,true);
		$criteria->compare('KMnO4_application',$this->KMnO4_application,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function primaryKey()
	{
	    return array('business_number', 'year', 'quarter', 'month', 'livestock_number');
	}
	
}