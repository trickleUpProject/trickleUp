<?php

/**
 * This is the model class for table "agri_receipt".
 *
 * The followings are the available columns in table 'agri_receipt':
 * @property integer $business_number
 * @property string $participant_name
 * @property integer $line_number
 * @property integer $quarter
 * @property integer $month
 * @property integer $year
 * @property string $date
 * @property string $crop_fish
 * @property integer $consumption_prod
 * @property integer $sale_weight
 * @property integer $sale_amount
 */
class AgriReceipt extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AgriReceipt the static model class
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
		return 'agri_receipt';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('business_number, participant_name, line_number, year', 'required'),
			array('business_number, line_number, quarter, month, year, consumption_prod, sale_weight, sale_amount', 'numerical', 'integerOnly'=>true),
			array('participant_name', 'length', 'max'=>100),
			array('crop_fish', 'length', 'max'=>50),
			array('date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('business_number, participant_name, line_number, quarter, month, year, date, crop_fish, consumption_prod, sale_weight, sale_amount', 'safe', 'on'=>'search'),
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
			'crop_fish' => 'Crop Fish',
			'consumption_prod' => 'Consumption Prod',
			'sale_weight' => 'Sale Weight',
			'sale_amount' => 'Sale Amount',
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
		$criteria->compare('crop_fish',$this->crop_fish,true);
		$criteria->compare('consumption_prod',$this->consumption_prod);
		$criteria->compare('sale_weight',$this->sale_weight);
		$criteria->compare('sale_amount',$this->sale_amount);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}