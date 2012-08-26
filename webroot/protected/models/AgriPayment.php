<?php

/**
 * This is the model class for table "agri_payment".
 *
 * The followings are the available columns in table 'agri_payment':
 * @property integer $business_number
 * @property string $participant_name
 * @property integer $line_number
 * @property integer $quarter
 * @property integer $month
 * @property integer $year
 * @property string $date
 * @property string $particular
 * @property integer $quantity
 * @property double $amount
 */
class AgriPayment extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AgriPayment the static model class
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
		return 'agri_payment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('business_number, participant_name, line_number', 'required'),
			array('business_number, line_number, quarter, month, year, quantity', 'numerical', 'integerOnly'=>true),
			array('amount', 'numerical'),
			array('participant_name, particular', 'length', 'max'=>100),
			array('date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('business_number, participant_name, line_number, quarter, month, year, date, particular, quantity, amount', 'safe', 'on'=>'search'),
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
			'line_number' => 'Line Number',
			'quarter' => 'Quarter',
			'month' => 'Month',
			'year' => 'Year',
			'date' => 'Date',
			'particular' => 'Particular',
			'quantity' => 'Quantity',
			'amount' => 'Amount',
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
		$criteria->compare('line_number',$this->line_number);
		$criteria->compare('quarter',$this->quarter);
		$criteria->compare('month',$this->month);
		$criteria->compare('year',$this->year);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('particular',$this->particular,true);
		$criteria->compare('quantity',$this->quantity);
		$criteria->compare('amount',$this->amount);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}