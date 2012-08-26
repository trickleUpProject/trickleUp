<?php

/**
 * This is the model class for table "small_business_receipt".
 *
 * The followings are the available columns in table 'small_business_receipt':
 * @property integer $business_number
 * @property string $participant_name
 * @property integer $quarter
 * @property integer $month
 * @property integer $year
 * @property integer $line_number
 * @property string $business_name
 * @property string $start_date
 * @property string $sale_date
 * @property double $sale_amount
 * @property double $consumption_amount
 * @property double $total_sale
 * @property string $staff_signature
 */
class SmallBusinessReceipt extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SmallBusinessReceipt the static model class
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
		return 'small_business_receipt';
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
			array('sale_amount, consumption_amount, total_sale', 'numerical'),
			array('participant_name, staff_signature', 'length', 'max'=>100),
			array('business_name', 'length', 'max'=>200),
			array('start_date, sale_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('business_number, participant_name, quarter, month, year, line_number, business_name, start_date, sale_date, sale_amount, consumption_amount, total_sale, staff_signature', 'safe', 'on'=>'search'),
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
			'sale_date' => 'Sale Date',
			'sale_amount' => 'Sale Amount',
			'consumption_amount' => 'Consumption Amount',
			'total_sale' => 'Total Sale',
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
		$criteria->compare('sale_date',$this->sale_date,true);
		$criteria->compare('sale_amount',$this->sale_amount);
		$criteria->compare('consumption_amount',$this->consumption_amount);
		$criteria->compare('total_sale',$this->total_sale);
		$criteria->compare('staff_signature',$this->staff_signature,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}