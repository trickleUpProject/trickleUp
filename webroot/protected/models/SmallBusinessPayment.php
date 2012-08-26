<?php

/**
 * This is the model class for table "small_business_payment".
 *
 * The followings are the available columns in table 'small_business_payment':
 * @property integer $business_number
 * @property string $participant_name
 * @property integer $quarter
 * @property integer $month
 * @property integer $year
 * @property integer $line_number
 * @property string $business_name
 * @property string $start_date
 * @property string $particular
 * @property double $amount
 * @property string $staff_signature
 */
class SmallBusinessPayment extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SmallBusinessPayment the static model class
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
		return 'small_business_payment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('business_number, participant_name, year, line_number', 'required'),
			array('business_number, quarter, month, year, line_number', 'numerical', 'integerOnly'=>true),
			array('amount', 'numerical'),
			array('participant_name, staff_signature', 'length', 'max'=>100),
			array('business_name, particular', 'length', 'max'=>200),
			array('start_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('business_number, participant_name, quarter, month, year, line_number, business_name, start_date, particular, amount, staff_signature', 'safe', 'on'=>'search'),
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
			'line_number' => 'Line Number',
			'business_name' => 'Business Name',
			'start_date' => 'Start Date',
			'particular' => 'Particular',
			'amount' => 'Amount',
			'staff_signature' => 'Staff Signature',
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
		$criteria->compare('line_number',$this->line_number);
		$criteria->compare('business_name',$this->business_name,true);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('particular',$this->particular,true);
		$criteria->compare('amount',$this->amount);
		$criteria->compare('staff_signature',$this->staff_signature,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}